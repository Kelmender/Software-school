<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "escueladb";

define('BASE_URL', '/software-school/');

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit;
}

// Esto erifica si hay una sesión activa
function verificarSesion() {
    session_start();
    if (!isset($_SESSION['username'])) {
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}
?>