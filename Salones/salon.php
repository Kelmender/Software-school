<?php
require_once('../db.php');
verificarSesion();

// Variables para manejar la solicitud de eliminación
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM salones WHERE id_salon = :id");
    $stmt->execute(['id' => $id]);
    header("Location: salon.php");
    exit;
}

// Variables para manejar la solicitud de agregar o editar salón
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $id_grado = $_POST['id_grado']; // Capturar el id_grado del formulario
    $id_salon = $_POST['id_salon'] ?? null;

    if (!empty($nombre) && !empty($id_grado)) {
        if ($id_salon) {
            // Editar salón
            $stmt = $pdo->prepare("UPDATE salones SET nombre = :nombre, id_grado = :id_grado WHERE id_salon = :id_salon");
            $stmt->execute([
                'nombre' => $nombre,
                'id_grado' => $id_grado,
                'id_salon' => $id_salon
            ]);
            $success_message = "Salón editado correctamente.";
        } else {
            // Agregar salón
            $stmt = $pdo->prepare("INSERT INTO salones (nombre, id_grado) VALUES (:nombre, :id_grado)");
            $stmt->execute([
                'nombre' => $nombre,
                'id_grado' => $id_grado
            ]);
            $success_message = "Salón agregado correctamente.";
        }
    } else {
        $error_message = "El nombre del salón y el grado son obligatorios.";
    }
}

// Obtener los grados para el select
$grados = $pdo->query("SELECT * FROM grados ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Obtener los salones existentes con información del grado
$salones = $pdo->query("SELECT s.id_salon, s.nombre, s.id_grado, g.nombre as nombre_grado 
                        FROM salones s 
                        LEFT JOIN grados g ON s.id_grado = g.id_grado 
                        ORDER BY g.nombre, s.nombre")->fetchAll(PDO::FETCH_ASSOC);

// Contar el número total de salones
$total_salones = count($salones);

// Verificar si es una solicitud de edición
$salon_para_editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM salones WHERE id_salon = :id");
    $stmt->execute(['id' => $id]);
    $salon_para_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Salones</title>
    <link rel="stylesheet" href="../Styles/styles_general.css">
    <link rel="stylesheet" href="../Styles/navbar.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
</head>
<body>
    <?php include('../navbar.php'); ?>

    <div class="container">
        <h1>Salones Registrados</h1>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?= $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?= $error_message; ?></div>
        <?php endif; ?>

        <button class="btn-agregar" id="btn-agregar">
            <i class="fas fa-plus"></i> Agregar Nuevo Salón
        </button>

        <div class="table-header">
            <div class="table-title">
                <h3>Lista de Salones</h3>
                <span class="record-count"><?= $total_salones ?> registros</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Grado</th>
                    <th>Nombre o número del Salón</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salones as $salon): ?>
                <tr>
                    <td><?= $salon['nombre_grado']; ?></td>
                    <td><?= $salon['nombre']; ?></td>
                    <td>
                        <div class="action-icons">
                            <a href="salon.php?editar=<?= $salon['id_salon']; ?>" class="edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="salon.php?eliminar=<?= $salon['id_salon']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este salón?');" class="delete" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de agregar/editar salón -->
    <div id="modalAgregar" class="modal" <?= (isset($_GET['editar'])) ? 'style="display:block;"' : ''; ?>>
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2><?= $salon_para_editar ? 'Editar Salón' : 'Agregar Nuevo Salón'; ?></h2>
            <form action="salon.php" method="POST">
                <div class="input-group">
                    <label for="id_grado">Grado</label>
                    <select id="id_grado" name="id_grado" required>
                        <option value="">-- Seleccione un grado --</option>
                        <?php foreach ($grados as $grado): ?>
                            <option value="<?= $grado['id_grado']; ?>" <?= ($salon_para_editar && $salon_para_editar['id_grado'] == $grado['id_grado']) ? 'selected' : ''; ?>>
                                <?= $grado['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="nombre">Nombre del Salón</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej. Salón 101" value="<?= $salon_para_editar ? $salon_para_editar['nombre'] : ''; ?>" required>
                </div>
                <!-- Campo oculto para editar -->
                <?php if ($salon_para_editar): ?>
                    <input type="hidden" name="id_salon" value="<?= $salon_para_editar['id_salon']; ?>">
                <?php endif; ?>
                
                <div class="button-group">
                    <button type="button" class="btn-cancelar" id="btn-cancelar">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit">
                        <?php if ($salon_para_editar): ?>
                            <i class="fas fa-save"></i> Guardar Cambios
                        <?php else: ?>
                            <i class="fas fa-plus-circle"></i> Agregar Salón
                        <?php endif; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Obtener el modal
        var modal = document.getElementById("modalAgregar");

        // Obtener el botón que abre el modal
        var btn = document.getElementById("btn-agregar");

        // Obtener el <span> que cierra el modal
        var span = document.getElementById("closeModal");
        
        // Obtener el botón cancelar
        var btnCancelar = document.getElementById("btn-cancelar");

        // Función para cerrar el modal y redirigir si es necesario
        function cerrarModal() {
            modal.style.display = "none";
            // Si hay un parámetro de edición en la URL, redirigir a la página sin ese parámetro
            if (window.location.href.includes('editar=')) {
                window.location.href = 'salon.php';
            }
        }

        // Cuando el usuario haga clic en el botón, abrir el modal
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // Cuando el usuario haga clic en <span> (x), cerrar el modal
        span.onclick = cerrarModal;
        
        // Cuando el usuario haga clic en el botón cancelar, cerrar el modal
        btnCancelar.onclick = cerrarModal;

        // Cuando el usuario haga clic fuera del modal, cerrarlo
        window.onclick = function(event) {
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>