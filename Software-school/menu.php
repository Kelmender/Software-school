<?php
require_once 'db.php';
verificarSesion();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú Principal</title>
    <link rel="stylesheet" href="Styles/menu.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
</head>
<body>
    <div class="top-bar">
        <h2>Sistema Escolar</h2>
        <div>
            <span>Bienvenido, <?php echo $_SESSION['username']; ?></span>
            <a href="<?php echo BASE_URL; ?>logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>
    </div>

    <div class="menu-container">
        <h1>Menú Principal</h1>
        <div class="menu-grid">
            <a href="<?php echo BASE_URL; ?>Grados/grado.php" class="menu-item">
                <i class="fas fa-graduation-cap"></i>
                Grados
            </a>
            <a href="<?php echo BASE_URL; ?>Salones/salon.php" class="menu-item">
                <i class="fas fa-chalkboard"></i>
                Salones
            </a>
            <a href="<?php echo BASE_URL; ?>Asignatura/asignatura.php" class="menu-item">
                <i class="fas fa-book"></i>
                Asignaturas
            </a>
            <a href="<?php echo BASE_URL; ?>Actividad/actividad.php" class="menu-item">
                <i class="fas fa-tasks"></i>
                Actividades
            </a>
            <a href="<?php echo BASE_URL; ?>Estudiantes/estudiante.php" class="menu-item">
                <i class="fas fa-user-graduate"></i>
                Estudiantes
            </a>
            <a href="<?php echo BASE_URL; ?>Notas/nota.php" class="menu-item">
                <i class="fas fa-clipboard-list"></i>
                Notas
            </a>
        </div>
    </div>
</body>
</html>