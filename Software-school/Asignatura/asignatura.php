<?php
include('../db.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM asignaturas WHERE id_asignatura = :id");
    $stmt->execute(['id' => $id]);
    header("Location: asignatura.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $id_asignatura = $_POST['id_asignatura'] ?? null;
    $gradosSeleccionados = $_POST['grados'] ?? [];
    $salonesSeleccionados = $_POST['salones'] ?? [];

    if (!empty($nombre) && !empty($gradosSeleccionados) && !empty($salonesSeleccionados)) {
        if ($id_asignatura) {
            $stmt = $pdo->prepare("UPDATE asignaturas SET nombre = :nombre WHERE id_asignatura = :id");
            $stmt->execute(['nombre' => $nombre, 'id' => $id_asignatura]);
            $success_message = "Asignatura editada correctamente.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO asignaturas (nombre) VALUES (:nombre)");
            $stmt->execute(['nombre' => $nombre]);
            $id_asignatura_insertada = $pdo->lastInsertId();

            $stmtGrado = $pdo->prepare("INSERT INTO grado_asignatura (id_grado, id_asignatura) VALUES (:grado, :asignatura)");
            foreach ($gradosSeleccionados as $id_grado) {
                $stmtGrado->execute(['grado' => $id_grado, 'asignatura' => $id_asignatura_insertada]);
            }

            $stmtSalon = $pdo->prepare("INSERT INTO salon_asignatura (id_salon, id_asignatura, id_usuario) VALUES (:salon, :asignatura, :usuario)");
            foreach ($salonesSeleccionados as $id_salon) {
                $stmtSalon->execute([
                    'salon' => $id_salon,
                    'asignatura' => $id_asignatura_insertada,
                    'usuario' => $_SESSION['id_usuario'] ?? 1
                ]);
            }

            $success_message = "Asignatura agregada correctamente.";
        }
    } else {
        $error_message = "Todos los campos son obligatorios.";
    }
}

$asignaturas = $pdo->query("SELECT * FROM asignaturas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$grados = $pdo->query("SELECT * FROM grados ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

$asignatura_para_editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM asignaturas WHERE id_asignatura = :id");
    $stmt->execute(['id' => $id]);
    $asignatura_para_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignaturas</title>
    <link rel="stylesheet" href="../Styles/styles_general.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="container">
    <h1>Asignaturas</h1>

    <?php if (isset($success_message)): ?>
        <div class="success-message"><?= $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="error-message"><?= $error_message; ?></div>
    <?php endif; ?>

    <button class="btn-agregar" id="btn-agregar">Agregar Asignatura</button>

    <table>
        <thead>
            <tr>
                <th>Nombre de la Asignatura</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($asignaturas as $asignatura): ?>
                <tr>
                    <td><?= $asignatura['nombre']; ?></td>
                    <td>
                        <a href="asignatura.php?editar=<?= $asignatura['id_asignatura']; ?>" class="btn-editar">Editar</a>
                        <a href="asignatura.php?eliminar=<?= $asignatura['id_asignatura']; ?>" onclick="return confirm('¿Eliminar esta asignatura?');" class="eliminar">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="modalAsignatura" class="modal" <?= (isset($_GET['editar'])) ? 'style="display:block;"' : ''; ?> >
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2><?= $asignatura_para_editar ? 'Editar Asignatura' : 'Agregar Asignatura' ?></h2>
        <form method="POST" action="asignatura.php">
            <div class="input-group">
                <label for="nombre">Nombre de la Asignatura</label>
                <input type="text" id="nombre" name="nombre" required value="<?= $asignatura_para_editar['nombre'] ?? '' ?>">
            </div>

            <div class="input-group">
                <label for="grados">Grados</label>
                <select name="grados[]" id="grados" multiple required>
                    <?php foreach ($grados as $grado): ?>
                        <option value="<?= $grado['id_grado']; ?>"><?= $grado['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group">
                <label for="salones">Salones</label>
                <select name="salones[]" id="salones" multiple required></select>
            </div>

            <?php if ($asignatura_para_editar): ?>
                <input type="hidden" name="id_asignatura" value="<?= $asignatura_para_editar['id_asignatura']; ?>">
            <?php endif; ?>

            <div class="button-group">
                <button type="button" class="btn-cancelar" id="btn-cancelar">Cancelar</button>
                <button type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById("modalAsignatura");
    const btn = document.getElementById("btn-agregar");
    const closeBtn = document.getElementById("closeModal");
    const btnCancelar = document.getElementById("btn-cancelar");

    function cerrarModal() {
        modal.style.display = "none";
        if (window.location.href.includes('editar=')) {
            window.location.href = 'asignatura.php';
        }
    }

    btn.onclick = () => modal.style.display = "block";
    closeBtn.onclick = cerrarModal;
    btnCancelar.onclick = cerrarModal;
    window.onclick = e => { if (e.target == modal) cerrarModal(); };

    document.getElementById('grados').addEventListener('change', function () {
        const seleccionados = Array.from(this.selectedOptions).map(opt => opt.value);

        fetch('obtener_salones_por_grado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ grados: seleccionados })
        })
        .then(res => res.json())
        .then(salones => {
            const selectSalones = document.getElementById('salones');
            selectSalones.innerHTML = '';
            salones.forEach(salon => {
                const opt = document.createElement('option');
                opt.value = salon.id_salon;
                opt.textContent = salon.nombre;
                selectSalones.appendChild(opt);
            });
        })
        .catch(err => console.error('Error al cargar salones:', err));
    });
</script>
</body>
</html>