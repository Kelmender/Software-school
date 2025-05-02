<?php
include('../db.php');
verificarSesion();  

// Eliminar estudiante
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM estudiantes WHERE id_estudiante = :id");
    $stmt->execute(['id' => $id]);
    header("Location: estudiantes.php");
    exit;
}

// Agregar o editar estudiante
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $id_estudiante = $_POST['id_estudiante'] ?? null;

    if (!empty($nombre)) {
        if ($id_estudiante) {
            $stmt = $pdo->prepare("UPDATE estudiantes SET nombre = :nombre WHERE id_estudiante = :id_estudiante");
            $stmt->execute(['nombre' => $nombre, 'id_estudiante' => $id_estudiante]);
            $success_message = "Estudiante editado correctamente.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO estudiantes (nombre) VALUES (:nombre)");
            $stmt->execute(['nombre' => $nombre]);
            $success_message = "Estudiante agregado correctamente.";
        }
    } else {
        $error_message = "El nombre del estudiante es obligatorio.";
    }
}

// Obtener estudiantes
$estudiantes = $pdo->query("SELECT * FROM estudiantes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Contar estudiantes
$total_estudiantes = count($estudiantes);

// Verificar si se quiere editar
$estudiante_para_editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $estudiante_para_editar = $pdo->prepare("SELECT * FROM estudiantes WHERE id_estudiante = :id");
    $estudiante_para_editar->execute(['id' => $id]);
    $estudiante_para_editar = $estudiante_para_editar->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estudiantes</title>
    <link rel="stylesheet" href="../Styles/styles_general.css">
</head>
<body>
<?php include('../navbar.php'); ?>

    <div class="container">
        <h1>Estudiantes Registrados</h1>
        <p><strong>Total de estudiantes:</strong> <?= $total_estudiantes ?></p>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?= $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?= $error_message; ?></div>
        <?php endif; ?>

        <button class="btn-agregar" id="btn-agregar">Agregar Estudiante</button>

        <table>
            <thead>
                <tr>
                    <th>Nombre del Estudiante</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estudiantes as $estudiante): ?>
                <tr>
                    <td><?= $estudiante['nombre']; ?></td>
                    <td>
                        <a href="estudiantes.php?editar=<?= $estudiante['id_estudiante']; ?>" class="btn-editar">Editar</a>
                        <a href="estudiantes.php?eliminar=<?= $estudiante['id_estudiante']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este estudiante?');" class="eliminar">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="modalAgregar" class="modal" style="<?= $estudiante_para_editar ? 'display:block;' : 'display:none;' ?>">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2><?= $estudiante_para_editar ? 'Editar Estudiante' : 'Agregar Estudiante'; ?></h2>
            <form action="estudiantes.php" method="POST">
                <div class="input-group">
                    <label for="nombre">Nombre del Estudiante</label>
                    <input type="text" id="nombre" name="nombre" value="<?= $estudiante_para_editar['nombre'] ?? ''; ?>" required>
                </div>
                <?php if ($estudiante_para_editar): ?>
                    <input type="hidden" name="id_estudiante" value="<?= $estudiante_para_editar['id_estudiante']; ?>">
                <?php endif; ?>
                <div class="button-group">
                    <button type="button" class="btn-cancelar" id="btn-cancelar">Cancelar</button>
                    <button type="submit"><?= $estudiante_para_editar ? 'Guardar Cambios' : 'Agregar Estudiante'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("modalAgregar");
        const btn = document.getElementById("btn-agregar");
        const closeBtn = document.getElementById("closeModal");
        const btnCancelar = document.getElementById("btn-cancelar");

        btn.addEventListener("click", () => modal.style.display = "block");
        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
            if (window.location.href.includes('editar=')) {
                window.location.href = 'estudiantes.php';
            }
        });
        btnCancelar.addEventListener("click", () => {
            modal.style.display = "none";
            if (window.location.href.includes('editar=')) {
                window.location.href = 'estudiantes.php';
            }
        });
        window.onclick = (event) => {
            if (event.target == modal) {
                modal.style.display = "none";
                if (window.location.href.includes('editar=')) {
                    window.location.href = 'estudiantes.php';
                }
            }
        };
    </script>
</body>
</html>