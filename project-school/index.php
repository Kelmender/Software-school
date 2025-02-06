<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal Educativo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #52b788, #2d6a4f);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            transform: translateY(20px);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .school-icon {
            text-align: center;
            margin-bottom: 2rem;
        }

        .school-icon i {
            font-size: 4rem;
            color: #2d6a4f;
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            100% { transform: rotate(360deg); }
        }

        h2 {
            color: #2d6a4f;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #52b788;
        }

        .input-group input {
            width: 100%;
            padding: 12px 40px;
            border: 2px solid #52b788;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #2d6a4f;
            box-shadow: 0 0 10px rgba(45, 106, 79, 0.2);
        }

        button {
            width: 100%;
            padding: 12px;
            background: #52b788;
            border: none;
            border-radius: 50px;
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #2d6a4f;
            transform: scale(1.05);
        }

        .error-message {
            color: #dc3545;
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .shake {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once "db.php";
        
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        try {
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email AND password = :password");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['email'] = $email;
                header("Location: Grado/grado.php");
                exit();
            } else {
                $error = "Correo o contraseña incorrectos";
            }
        } catch(PDOException $e) {
            $error = "Error en el sistema. Por favor intente más tarde.";
        }
    }
    ?>

    <div class="login-container">
        <div class="school-icon">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <h2>Bienvenido</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit">
                Iniciar Sesión <i class="fas fa-arrow-right"></i>
            </button>
            <?php if(isset($error)): ?>
                <div class="error-message shake">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.05)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
