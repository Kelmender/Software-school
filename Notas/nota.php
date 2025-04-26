<?php
include('../db.php');
verificarSesion();  


// Eliminar nota
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM notas WHERE id_nota = :id");
    $stmt->execute(['id' => $id]);
    header("Location: notas.php");
    exit;
}

// Agregar o editar nota
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valor = $_POST['valor'];
    $id_nota = $_POST['id_nota'] ?? null;

    if (!empty($valor)) {
        if ($id_nota) {
            $stmt = $pdo->prepare("UPDATE notas SET valor = :valor WHERE id_nota = :id");
            $stmt->execute(['valor' => $valor, 'id' => $id_nota]);
            $success_message = "Nota editada correctamente.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO notas (valor) VALUES (:valor)");
            $stmt->execute(['valor' => $valor]);
            $success_message = "Nota agregada correctamente.";
        }
    } else {
        $error_message = "El valor de la nota es obligatorio.";
    }
}

// Obtener notas existentes
$notas = $pdo->query("SELECT * FROM notas")->fetchAll(PDO::FETCH_ASSOC);

// Para edición
$nota_para_editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM notas WHERE id_nota = :id");
    $stmt->execute(['id' => $id]);
    $nota_para_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notas</title>
    <link rel="stylesheet" href="../Styles/styles_general.css">
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

        <button class="btn-agregar" id="btn-agregar">Agregar Nota</button>

        <table>
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Actividad</th>
                    <th>Valor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notas as $nota): ?>
                    <tr>
                        <td><?= $nota['valor']; ?></td>
                        <td>
                            <a href="notas.php?editar=<?= $nota['id_nota']; ?>" class="btn-editar">Editar</a>
                            <a href="notas.php?eliminar=<?= $nota['id_nota']; ?>" onclick="return confirm('¿Eliminar esta nota?');" class="eliminar">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="modalAgregar" class="modal" style="display: <?= $nota_para_editar ? 'block' : 'none' ?>;">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2><?= $nota_para_editar ? 'Editar Nota' : 'Agregar Nota'; ?></h2>
            <form action="notas.php" method="POST">
                <div class="input-group">
                    <label for="valor">Valor</label>
                    <input type="text" id="valor" name="valor" value="<?= $nota_para_editar['valor'] ?? '' ?>" required>
                </div>
                <?php if ($nota_para_editar): ?>
                    <input type="hidden" name="id_nota" value="<?= $nota_para_editar['id_nota']; ?>">
                <?php endif; ?>
                <button type="submit"><?= $nota_para_editar ? 'Guardar Cambios' : 'Agregar Nota'; ?></button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("modalAgregar");
        const btnAgregar = document.getElementById("btn-agregar");
        const btnCerrar = document.getElementById("closeModal");

        btnAgregar.onclick = () => modal.style.display = "block";
        btnCerrar.onclick = () => modal.style.display = "none";
        window.onclick = e => { if (e.target == modal) modal.style.display = "none"; }
    </script>
</body>
</html>
