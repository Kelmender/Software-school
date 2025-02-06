<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Portal Educativo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #52b788, #2d6a4f);
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            transition: transform 0.3s ease;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar.hidden {
            transform: translateX(250px);
        }

        .toggle-btn {
            position: fixed;
            right: 270px;
            top: 20px;
            background: #2d6a4f;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1001;
        }

        .toggle-btn.hidden {
            right: 20px;
        }

        .toggle-btn:hover {
            background: #52b788;
            transform: scale(1.05);
        }

        .menu-item {
            display: block;
            padding: 15px;
            margin: 10px 0;
            text-decoration: none;
            color: #2d6a4f;
            background: white;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .menu-item:hover {
            transform: translateX(-10px);
            background: #52b788;
            color: white;
        }

        .menu-item i {
            margin-right: 10px;
            font-size: 1.2em;
        }

        .dashboard-title {
            text-align: center;
            color: white;
            margin: 20px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .welcome-container {
            text-align: center;
            padding: 50px;
            color: white;
            animation: slideIn 1s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .user-info {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            font-size: 1.1em;
        }

        .logout-btn {
            display: block;
            margin-top: 20px;
            padding: 10px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background 0.3s ease;
            cursor: pointer;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .logout-icon {
            position: fixed;
            top: 20px;
            right: 330px;
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1001;
        }

        .logout-icon:hover {
            background: #c82333;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="user-info">
        <i class="fas fa-user"></i> 
        Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?>
    </div>

    <button class="toggle-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <button class="logout-icon" onclick="confirmarCerrarSesion()">
        <i class="fas fa-sign-out-alt"></i>
    </button>

    <div class="sidebar">
        <h2 style="color: #2d6a4f; margin-bottom: 30px; text-align: center;">
            <i class="fas fa-school"></i> Menu
        </h2>
        
        <a href="../Grado/grado.php" class="menu-item">
            <i class="fas fa-graduation-cap"></i> Gestión de Grados
        </a>
        
        <a href="../Estudiantes/estudiantes.php" class="menu-item">
            <i class="fas fa-user-graduate"></i> Gestión de Estudiantes
        </a>

        <a href="../Materias/materias.php" class="menu-item">
            <i class="fas fa-book"></i> Gestión de Materias
        </a>

        <a href="../Profesores/profesores.php" class="menu-item">
            <i class="fas fa-chalkboard-teacher"></i> Gestión de Profesores
        </a>

        <a href="../Calificaciones/calificaciones.php" class="menu-item">
            <i class="fas fa-star"></i> Gestión de Calificaciones
        </a>

        <a href="../Horarios/horarios.php" class="menu-item">
            <i class="fas fa-clock"></i> Gestión de Horarios
        </a>

        <div class="logout-btn" onclick="confirmarCerrarSesion()">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </div>
    </div>

    <h1 class="dashboard-title">
        <i class="fas fa-school"></i> Portal Educativo
    </h1>

    <div class="welcome-container">
        <h2>¡Bienvenido al Sistema!</h2>
        <p style="margin-top: 20px; font-size: 1.2em;">
            Selecciona una opción del menú para comenzar
        </p>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('.toggle-btn');
            sidebar.classList.toggle('hidden');
            toggleBtn.classList.toggle('hidden');
        }

        function confirmarCerrarSesion() {
            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea cerrar la sesión?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cerrar sesión',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../logout.php';
                }
            });
        }
    </script>
</body>
</html>
