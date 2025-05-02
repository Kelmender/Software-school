<?php
include('../db.php');
verificarSesion();  

// Eliminar actividad
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM actividades WHERE id_actividad = :id");
    $stmt->execute(['id' => $id]);
    header("Location: actividad.php");
    exit;
}

// Agregar o editar actividad
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $id_asignatura = $_POST['id_asignatura'];
    $id_salon = $_POST['id_salon'];
    $fecha_creacion = $_POST['fecha_creacion'] ?? date('Y-m-d');
    $id_actividad = $_POST['id_actividad'] ?? null;

    if (!empty($nombre) && !empty($id_asignatura) && !empty($id_salon)) {
        if ($id_actividad) {
            $stmt = $pdo->prepare("UPDATE actividades SET nombre = :nombre, descripcion = :descripcion, id_asignatura = :id_asignatura, id_salon = :id_salon, fecha_creacion = :fecha_creacion WHERE id_actividad = :id");
            $stmt->execute([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'id_asignatura' => $id_asignatura,
                'id_salon' => $id_salon,
                'fecha_creacion' => $fecha_creacion,
                'id' => $id_actividad
            ]);
            $success_message = "Actividad editada correctamente.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO actividades (nombre, descripcion, id_asignatura, id_salon, fecha_creacion) VALUES (:nombre, :descripcion, :id_asignatura, :id_salon, :fecha_creacion)");
            $stmt->execute([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'id_asignatura' => $id_asignatura,
                'id_salon' => $id_salon,
                'fecha_creacion' => $fecha_creacion
            ]);
            $success_message = "Actividad agregada correctamente.";
        }
    } else {
        $error_message = "Los campos nombre, asignatura y salón son obligatorios.";
    }
}

// Obtener asignaturas para el select
$asignaturas = $pdo->query("SELECT * FROM asignaturas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Obtener salones para el select
$salones = $pdo->query("SELECT s.*, g.nombre as nombre_grado FROM salones s 
                       INNER JOIN grados g ON s.id_grado = g.id_grado 
                       ORDER BY g.nombre")->fetchAll(PDO::FETCH_ASSOC);

// Obtener actividades con información relacionada
$actividades = $pdo->query("SELECT a.*, asig.nombre as nombre_asignatura, s.nombre as nombre_salon, g.nombre as nombre_grado 
                           FROM actividades a 
                           INNER JOIN asignaturas asig ON a.id_asignatura = asig.id_asignatura
                           INNER JOIN salones s ON a.id_salon = s.id_salon
                           INNER JOIN grados g ON s.id_grado = g.id_grado
                           ORDER BY g.nombre, asig.nombre, a.nombre")->fetchAll(PDO::FETCH_ASSOC);

// Contar actividades
$total_actividades = count($actividades);

// Si se desea editar
$actividad_para_editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM actividades WHERE id_actividad = :id");
    $stmt->execute(['id' => $id]);
    $actividad_para_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actividades</title>
    <link rel="stylesheet" href="../Styles/styles_general.css">
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="container">
    
    <h1>Actividades Registradas</h1>
    <p><strong>Total de actividades:</strong> <?= $total_actividades ?></p>

    <?php if (isset($success_message)): ?>
        <div class="success-message"><?= $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="error-message"><?= $error_message; ?></div>
    <?php endif; ?>

    <button class="btn-agregar" id="btn-agregar">Agregar Nueva Actividad</button>

    <table>
        <thead>
        <tr>
            <th>Grado</th>
            <th>Asignatura</th>
            <th>Nombre de la Actividad</th>
            <th>Descripción</th>
            <th>Fecha de Creación</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($actividades as $actividad): ?>
            <tr>
                <td><?= $actividad['nombre_grado']; ?></td>
                <td><?= $actividad['nombre_asignatura']; ?></td>
                <td><?= $actividad['nombre']; ?></td>
                <td class="descripcion-cell"><?= !empty($actividad['descripcion']) ? $actividad['descripcion'] : '<em>Sin descripción</em>'; ?></td>
                <td><?= date('d/m/Y', strtotime($actividad['fecha_creacion'])); ?></td>
                <td>
                    <a href="actividad.php?editar=<?= $actividad['id_actividad']; ?>" class="btn-editar">Editar</a>
                    <a href="actividad.php?eliminar=<?= $actividad['id_actividad']; ?>" onclick="return confirm('¿Seguro que deseas eliminar esta actividad?');" class="eliminar">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="modalActividad" class="modal" style="display: <?= $actividad_para_editar ? 'block' : 'none'; ?>">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2><?= $actividad_para_editar ? 'Editar Actividad' : 'Agregar Nueva Actividad'; ?></h2>
        <form action="actividad.php" method="POST">
            <div class="input-group">
                <label for="id_asignatura">Asignatura</label>
                <select id="id_asignatura" name="id_asignatura" required>
                    <option value="">Seleccione una asignatura</option>
                    <?php foreach ($asignaturas as $asignatura): ?>
                        <option value="<?= $asignatura['id_asignatura']; ?>" <?= ($actividad_para_editar && $actividad_para_editar['id_asignatura'] == $asignatura['id_asignatura']) ? 'selected' : ''; ?>>
                            <?= $asignatura['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="input-group">
                <label for="id_salon">Salón</label>
                <select id="id_salon" name="id_salon" required>
                    <option value="">Seleccione un salón</option>
                    <?php foreach ($salones as $salon): ?>
                        <option value="<?= $salon['id_salon']; ?>" <?= ($actividad_para_editar && $actividad_para_editar['id_salon'] == $salon['id_salon']) ? 'selected' : ''; ?>>
                            <?= $salon['nombre'] . ' - ' . $salon['nombre_grado']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="input-group">
                <label for="nombre">Nombre de la Actividad</label>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre de la actividad" value="<?= $actividad_para_editar ? $actividad_para_editar['nombre'] : ''; ?>" required>
            </div>
            
            <div class="input-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" placeholder="Descripción de la actividad" rows="4"><?= $actividad_para_editar ? $actividad_para_editar['descripcion'] : ''; ?></textarea>
            </div>
            
            <div class="input-group">
                <label for="fecha_creacion">Fecha de Creación</label>
                <input type="date" id="fecha_creacion" name="fecha_creacion" value="<?= $actividad_para_editar ? $actividad_para_editar['fecha_creacion'] : date('Y-m-d'); ?>" required>
            </div>
            
            <?php if ($actividad_para_editar): ?>
                <input type="hidden" name="id_actividad" value="<?= $actividad_para_editar['id_actividad']; ?>">
            <?php endif; ?>
            
            <div class="botones-accion">
                <button type="button" class="btn-cancelar" id="btn-cancelar"><?= $actividad_para_editar ? 'Cancelar Edición' : 'Cancelar'; ?></button>
                <button type="submit" class="btn-guardar"><?= $actividad_para_editar ? 'Guardar Cambios' : 'Agregar Actividad'; ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById("modalActividad");
    const btn = document.getElementById("btn-agregar");
    const span = document.getElementById("closeModal");
    const btnCancelar = document.getElementById("btn-cancelar");

    btn.onclick = function() {
        // Limpiamos el formulario al abrir para agregar
        if (document.querySelector('form')) {
            document.querySelector('form').reset();
            // Establecer la fecha de hoy
            document.getElementById('fecha_creacion').value = new Date().toISOString().split('T')[0];
        }
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    btnCancelar.onclick = function() {
        modal.style.display = "none";
        // Si estamos editando, redirigir a la página principal para cancelar la edición
        <?php if ($actividad_para_editar): ?>
        window.location.href = "actividad.php";
        <?php endif; ?>
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>