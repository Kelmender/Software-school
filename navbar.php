<?php
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>Styles/narvar.css">

<div class="top-bar">
    <div class="top-bar-left">
        <h2>Sistema Escolar</h2>
    </div>
    <div class="top-bar-right">
        <nav class="main-nav">
            <a href="<?php echo BASE_URL; ?>menu.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'menu.php') !== false ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Inicio
            </a>
            <a href="<?php echo BASE_URL; ?>Grados/grado.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'grado.php') !== false ? 'active' : ''; ?>">
                <i class="fas fa-graduation-cap"></i> Grados
            </a>
            <a href="<?php echo BASE_URL; ?>Salones/salon.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'salon.php') !== false ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard"></i> Salones
            </a>
            <a href="<?php echo BASE_URL; ?>Asignatura/asignatura.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'asignatura.php') !== false ? 'active' : ''; ?>">
                <i class="fas fa-book"></i> Asignaturas
            </a>
            <a href="<?php echo BASE_URL; ?>Actividad/actividad.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'actividad.php') !== false ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i> Actividades
            </a>
            <a href="<?php echo BASE_URL; ?>Estudiantes/estudiante.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'estudiante.php') !== false ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i> Estudiantes
            </a>
            <a href="<?php echo BASE_URL; ?>Notas/nota.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'nota.php') !== false ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> Notas
            </a>
        </nav>
        <div class="user-menu">
            <span>Bienvenido, <?php echo $_SESSION['username']; ?></span>
            <a href="<?php echo BASE_URL; ?>logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</div>