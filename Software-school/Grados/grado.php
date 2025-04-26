<?php
require_once('../db.php');
verificarSesion();  

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM grados WHERE id_grado = :id");
    $stmt->execute(['id' => $id]);
    header("Location: grado.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $id_grado = $_POST['id_grado'] ?? null;

    if (!empty($nombre)) {
        if ($id_grado) {

            $stmt = $pdo->prepare("UPDATE grados SET nombre = :nombre WHERE id_grado = :id_grado");
            $stmt->execute(['nombre' => $nombre, 'id_grado' => $id_grado]);
            $success_message = "Grado editado correctamente.";
        } else {

            $stmt = $pdo->prepare("INSERT INTO grados (nombre) VALUES (:nombre)");
            $stmt->execute(['nombre' => $nombre]);
            $success_message = "Grado agregado correctamente.";
        }
    } else {
        $error_message = "El nombre del grado es obligatorio.";
    }
}


$grados = $pdo->query("SELECT * FROM grados")->fetchAll(PDO::FETCH_ASSOC);


$grado_para_editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $grado_para_editar = $pdo->prepare("SELECT * FROM grados WHERE id_grado = :id");
    $grado_para_editar->execute(['id' => $id]);
    $grado_para_editar = $grado_para_editar->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Grados</title>
    <link rel="stylesheet" href="../Styles/styles_general.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
</head>
<body>
    <?php include('../navbar.php'); ?>

    <div class="container">
        <h1>Grados Registrados</h1>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?= $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?= $error_message; ?></div>
        <?php endif; ?>

        <button class="btn-agregar" id="btn-agregar">Agregar Nuevo Grado</button>

        <table>
            <thead>
                <tr>
                    <th>Nombre del Grado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grados as $grado): ?>
                <tr>
                    <td><?= $grado['nombre']; ?></td>
                    <td>
                        <a href="grado.php?editar=<?= $grado['id_grado']; ?>" class="btn-editar">Editar</a>
                        <a href="grado.php?eliminar=<?= $grado['id_grado']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este grado?');" class="eliminar">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de agregar/editar grado -->
    <div id="modalAgregar" class="modal" <?= (isset($_GET['editar'])) ? 'style="display:block;"' : ''; ?>>
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2><?= $grado_para_editar ? 'Editar Grado' : 'Agregar Nuevo Grado'; ?></h2>
            <form action="grado.php" method="POST">
                <div class="input-group">
                    <label for="nombre">Nombre del Grado</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej. Grado 1" value="<?= $grado_para_editar ? $grado_para_editar['nombre'] : ''; ?>" required>
                </div>
                <!-- Campo oculto para editar -->
                <?php if ($grado_para_editar): ?>
                    <input type="hidden" name="id_grado" value="<?= $grado_para_editar['id_grado']; ?>">
                <?php endif; ?>
                <button type="submit"><?= $grado_para_editar ? 'Editar Grado' : 'Agregar Grado'; ?></button>
            </form>
        </div>
    </div>

    <script>

        var modal = document.getElementById("modalAgregar");

        var btn = document.getElementById("btn-agregar");

        var span = document.getElementById("closeModal");

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";

            if (window.location.href.includes('editar=')) {
                window.location.href = 'grado.php';
            }
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";

                if (window.location.href.includes('editar=')) {
                    window.location.href = 'grado.php';
                }
            }
        }
    </script>
</body>
</html>