<?php
include('../db.php');
verificarSesion();  

// Eliminar nota
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM notas WHERE id_nota = :id");
    $stmt->execute(['id' => $id]);
    header("Location: nota.php");
    exit;
}

// Agregar o editar nota
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valor = $_POST['valor'];
    $id_estudiante = $_POST['id_estudiante'];
    $id_actividad = $_POST['id_actividad'];
    $id_nota = $_POST['id_nota'] ?? null;

    if (!empty($valor) && !empty($id_estudiante) && !empty($id_actividad)) {
        if ($id_nota) {
            $stmt = $pdo->prepare("UPDATE notas SET valor = :valor, id_estudiante = :id_estudiante, id_actividad = :id_actividad WHERE id_nota = :id");
            $stmt->execute([
                'valor' => $valor, 
                'id_estudiante' => $id_estudiante, 
                'id_actividad' => $id_actividad, 
                'id' => $id_nota
            ]);
            $success_message = "Nota editada correctamente.";
        } else {

            $stmt = $pdo->prepare("SELECT id_nota FROM notas WHERE id_estudiante = :id_estudiante AND id_actividad = :id_actividad");
            $stmt->execute([
                'id_estudiante' => $id_estudiante,
                'id_actividad' => $id_actividad
            ]);
            $existente = $stmt->fetch();
            
            if ($existente) {
                $error_message = "Ya existe una nota para este estudiante en esta actividad.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO notas (valor, id_estudiante, id_actividad) VALUES (:valor, :id_estudiante, :id_actividad)");
                $stmt->execute([
                    'valor' => $valor,
                    'id_estudiante' => $id_estudiante,
                    'id_actividad' => $id_actividad
                ]);
                $success_message = "Nota agregada correctamente.";
            }
        }
    } else {
        $error_message = "Todos los campos son obligatorios.";
    }
}

// Obtener grados para el filtro
$grados = $pdo->query("SELECT * FROM grados ORDER BY id_grado ASC")->fetchAll(PDO::FETCH_ASSOC);

// Filtros
$filtro_grado = $_GET['grado'] ?? null;
$filtro_salon = $_GET['salon'] ?? null;

// Obtener salones basados en el grado seleccionado
$salones = [];
if ($filtro_grado) {
    $stmt = $pdo->prepare("SELECT * FROM salones WHERE id_grado = :id_grado ORDER BY nombre ASC");
    $stmt->execute(['id_grado' => $filtro_grado]);
    $salones = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Consultar estudiantes según los filtros
$condiciones = [];
$parametros = [];
$sql_estudiantes = "SELECT * FROM estudiantes";

if ($filtro_salon) {
    $condiciones[] = "id_salon = :id_salon";
    $parametros['id_salon'] = $filtro_salon;
}

if (!empty($condiciones)) {
    $sql_estudiantes .= " WHERE " . implode(' AND ', $condiciones);
}

$sql_estudiantes .= " ORDER BY nombre ASC";

$stmt = $pdo->prepare($sql_estudiantes);
$stmt->execute($parametros);
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener actividades con información relacionada
$actividades = $pdo->query("SELECT a.*, asig.nombre as nombre_asignatura 
                           FROM actividades a 
                           INNER JOIN asignaturas asig ON a.id_asignatura = asig.id_asignatura
                           ORDER BY a.nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Obtener todas las notas con relación a estudiantes y actividades
$stmt = $pdo->query("SELECT n.*, e.nombre as nombre_estudiante, a.nombre as nombre_actividad 
                    FROM notas n
                    INNER JOIN estudiantes e ON n.id_estudiante = e.id_estudiante
                    INNER JOIN actividades a ON n.id_actividad = a.id_actividad");
$todasLasNotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar las notas en un array para fácil acceso
$notasOrganizadas = [];
foreach ($todasLasNotas as $nota) {
    $notasOrganizadas[$nota['id_estudiante']][$nota['id_actividad']] = [
        'id_nota' => $nota['id_nota'],
        'valor' => $nota['valor']
    ];
}

// Para edición
$nota_para_editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM notas WHERE id_nota = :id");
    $stmt->execute(['id' => $id]);
    $nota_para_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Contar notas filtradas
$totalNotasFiltradas = 0;
if (!empty($estudiantes)) {
    $ids_estudiantes = array_column($estudiantes, 'id_estudiante');
    
    foreach ($todasLasNotas as $nota) {
        if (in_array($nota['id_estudiante'], $ids_estudiantes)) {
            $totalNotasFiltradas++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../Styles/styles_general.css">
    <link rel="stylesheet" href="../Styles/nota.css">
</head>
<body>
<?php include('../navbar.php'); ?>

    <div class="container">
        <h1>Notas Registradas</h1>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?= $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?= $error_message; ?></div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filtros-container">
            <form action="nota.php" method="GET" class="form-filtros">
                <div class="filtro-grupo">
                    <label for="grado">Grado:</label>
                    <select id="grado" name="grado" onchange="this.form.submit()">
                        <option value="">Todos los grados</option>
                        <?php foreach ($grados as $grado): ?>
                            <option value="<?= $grado['id_grado']; ?>" <?= ($filtro_grado == $grado['id_grado']) ? 'selected' : ''; ?>>
                                <?= $grado['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($filtro_grado): ?>
                <div class="filtro-grupo">
                    <label for="salon">Salón:</label>
                    <select id="salon" name="salon" onchange="this.form.submit()">
                        <option value="">Todos los salones</option>
                        <?php foreach ($salones as $salon): ?>
                            <option value="<?= $salon['id_salon']; ?>" <?= ($filtro_salon == $salon['id_salon']) ? 'selected' : ''; ?>>
                                <?= $salon['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabla de notas -->
        <div class="table-header">
            <div class="table-title">
                <h2>Calificaciones</h2>
                <span class="record-count"><?= $totalNotasFiltradas ?> notas</span>
            </div>
        </div>

        <div class="tabla-responsive">
            <table>
                <thead>
                    <tr>
                        <th class="nombre-header">Estudiante</th>
                        <?php foreach ($actividades as $actividad): ?>
                            <th class="actividad-header">
                                <div class="actividad-titulo"><?= $actividad['nombre']; ?></div>
                                <div class="actividad-subtitulo"><?= $actividad['nombre_asignatura']; ?></div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($estudiantes)): ?>
                        <tr>
                            <td colspan="<?= count($actividades) + 1; ?>" class="no-results">No se encontraron estudiantes con los filtros seleccionados</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($estudiantes as $estudiante): ?>
                            <tr>
                                <td class="estudiante-nombre"><?= $estudiante['nombre']; ?></td>
                                <?php foreach ($actividades as $actividad): ?>
                                    <?php 
                                        $tiene_nota = isset($notasOrganizadas[$estudiante['id_estudiante']][$actividad['id_actividad']]);
                                        $clase_celda = $tiene_nota ? 'celda-con-nota' : 'celda-sin-nota';
                                        $id_nota = $tiene_nota ? $notasOrganizadas[$estudiante['id_estudiante']][$actividad['id_actividad']]['id_nota'] : '';
                                        $nota_valor = $tiene_nota ? $notasOrganizadas[$estudiante['id_estudiante']][$actividad['id_actividad']]['valor'] : '';
                                    ?>
                                    <td class="<?= $clase_celda ?>" 
                                        data-estudiante="<?= $estudiante['id_estudiante'] ?>" 
                                        data-actividad="<?= $actividad['id_actividad'] ?>" 
                                        data-id-nota="<?= $id_nota ?>" 
                                        data-nombre-estudiante="<?= $estudiante['nombre'] ?>"
                                        data-nombre-actividad="<?= $actividad['nombre'] ?>"
                                        data-asignatura="<?= $actividad['nombre_asignatura'] ?>">
                                        <?= $tiene_nota ? $nota_valor : '-' ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para agregar/editar nota -->
    <div id="modalNota" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2 id="modalTitle">Agregar Nota</h2>
            <form id="notaForm" action="nota.php" method="POST">
                <div class="input-group">
                    <label for="id_estudiante">Estudiante</label>
                    <select id="id_estudiante" name="id_estudiante" required>
                        <option value="">Seleccione un estudiante</option>
                        <?php foreach ($estudiantes as $estudiante): ?>
                            <option value="<?= $estudiante['id_estudiante']; ?>" <?= ($nota_para_editar && $nota_para_editar['id_estudiante'] == $estudiante['id_estudiante']) ? 'selected' : ''; ?>>
                                <?= $estudiante['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="input-group">
                    <label for="id_actividad">Actividad</label>
                    <select id="id_actividad" name="id_actividad" required>
                        <option value="">Seleccione una actividad</option>
                        <?php foreach ($actividades as $actividad): ?>
                            <option value="<?= $actividad['id_actividad']; ?>" <?= ($nota_para_editar && $nota_para_editar['id_actividad'] == $actividad['id_actividad']) ? 'selected' : ''; ?>>
                                <?= $actividad['nombre']; ?> (<?= $actividad['nombre_asignatura']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="input-group">
                    <label for="valor">Valor de la Nota</label>
                    <input type="text" id="valor" name="valor" value="<?= $nota_para_editar['valor'] ?? '' ?>" required>
                </div>
                
                <input type="hidden" id="id_nota" name="id_nota" value="">
                <input type="hidden" name="grado" value="<?= $filtro_grado ?>">
                <input type="hidden" name="salon" value="<?= $filtro_salon ?>">
                
                <button type="submit" id="btn-submit">Agregar Nota</button>
            </form>
        </div>
    </div>

    <!-- Modal para confirmación de eliminación -->
    <div id="modalEliminar" class="modal">
        <div class="modal-content modal-sm">
            <h2>Eliminar Nota</h2>
            <p>¿Está seguro que desea eliminar esta nota?</p>
            <div class="button-group">
                <button type="button" class="btn-cancelar" id="cancelarEliminar">Cancelar</button>
                <a href="#" id="confirmarEliminar" class="btn-eliminar">Eliminar</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById("modalNota");
            const modalEliminar = document.getElementById("modalEliminar");
            const btnCerrar = document.getElementById("closeModal");
            const btnCancelarEliminar = document.getElementById("cancelarEliminar");
            const btnConfirmarEliminar = document.getElementById("confirmarEliminar");
            const modalTitle = document.getElementById("modalTitle");
            const btnSubmit = document.getElementById("btn-submit");
            const formNota = document.getElementById("notaForm");

            // Cerrar modal
            btnCerrar.onclick = cerrarModal;
            window.onclick = e => { 
                if (e.target == modal) cerrarModal();
                if (e.target == modalEliminar) modalEliminar.style.display = "none";
            };

            // Función para cerrar el modal
            function cerrarModal() {
                modal.style.display = "none";
                if (window.location.href.includes('editar=')) {
                    // Mantener los filtros al redirigir
                    let urlParams = new URLSearchParams(window.location.search);
                    urlParams.delete('editar');
                    let redirectUrl = 'nota.php';
                    if (urlParams.toString()) {
                        redirectUrl += '?' + urlParams.toString();
                    }
                    window.location.href = redirectUrl;
                }
            }

            // Evento para abrir modal al hacer clic en una celda
            const celdas = document.querySelectorAll('.celda-con-nota, .celda-sin-nota');
            celdas.forEach(celda => {
                celda.addEventListener('click', function() {
                    const idEstudiante = this.getAttribute('data-estudiante');
                    const idActividad = this.getAttribute('data-actividad');
                    const idNota = this.getAttribute('data-id-nota');
                    const nombreEstudiante = this.getAttribute('data-nombre-estudiante');
                    const nombreActividad = this.getAttribute('data-nombre-actividad');
                    const asignatura = this.getAttribute('data-asignatura');

                    resetModal();
                    
                    // Seleccionar el estudiante y la actividad en el formulario
                    document.getElementById('id_estudiante').value = idEstudiante;
                    document.getElementById('id_actividad').value = idActividad;
                    
                    if (idNota) {
                        // Es una edición
                        modalTitle.textContent = `Editar Nota de ${nombreEstudiante} - ${nombreActividad} (${asignatura})`;
                        document.getElementById('valor').value = this.textContent.trim();
                        document.getElementById('id_nota').value = idNota;
                        btnSubmit.textContent = "Guardar Cambios";
                        
                        // Agregar botón de eliminar
                        const eliminarBtn = document.createElement('button');
                        eliminarBtn.type = 'button';
                        eliminarBtn.className = 'btn-eliminar';
                        eliminarBtn.textContent = 'Eliminar';
                        eliminarBtn.onclick = function() {
                            modal.style.display = "none";
                            modalEliminar.style.display = "block";
                            // Conservar los parámetros de filtro en la URL de eliminación
                            let urlParams = new URLSearchParams(window.location.search);
                            urlParams.set('eliminar', idNota);
                            btnConfirmarEliminar.href = `nota.php?${urlParams.toString()}`;
                        };
                        document.querySelector('.modal-content form').appendChild(eliminarBtn);
                    } else {
                        // Es una nueva nota
                        modalTitle.textContent = `Agregar Nota para ${nombreEstudiante} - ${nombreActividad} (${asignatura})`;
                        document.getElementById('valor').value = '';
                        btnSubmit.textContent = "Agregar Nota";
                    }
                    
                    modal.style.display = "block";
                });
            });

            // Función para resetear el modal
            function resetModal() {
                formNota.reset();
                const btnEliminar = document.querySelector('.btn-eliminar');
                if (btnEliminar) {
                    btnEliminar.remove();
                }
            }

            // Evento para cerrar modal de eliminación
            btnCancelarEliminar.onclick = () => {
                modalEliminar.style.display = "none";
                modal.style.display = "block";
            };

            // Mostrar modal de edición si hay una nota para editar en la URL
            <?php if ($nota_para_editar): ?>
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('id_estudiante').value = '<?= $nota_para_editar['id_estudiante'] ?>';
                    document.getElementById('id_actividad').value = '<?= $nota_para_editar['id_actividad'] ?>';
                    document.getElementById('valor').value = '<?= $nota_para_editar['valor'] ?>';
                    document.getElementById('id_nota').value = '<?= $nota_para_editar['id_nota'] ?>';
                    modalTitle.textContent = "Editar Nota";
                    btnSubmit.textContent = "Guardar Cambios";
                    modal.style.display = "block";
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>