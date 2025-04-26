<?php
session_start();

// Si ya hay una sesión activa, redirigir al menú
if (isset($_SESSION['username'])) {
    header("Location: menu.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "db.php";
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username AND password = :password");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['username'] = $username;
            header("Location: menu.php");
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
    } catch(PDOException $e) {
        $error = "Error en el sistema. Por favor intente más tarde.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Styles/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Inicio de Sesión</title>
</head>
<body>

<div class="login-container">
    <div class="school-icon">
        <i class="fas fa-graduation-cap"></i>
    </div>
    <h2>Bienvenido</h2>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Usuario" required>
        </div>
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Contraseña" required>
        </div>
        <button type="submit" class="login-btn">
            Iniciar Sesión <i class="fas fa-arrow-right"></i>
        </button>
        <?php if(isset($error)): ?>
            <div class="error-message shake">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
    </form>
</div>

</body>
</html>