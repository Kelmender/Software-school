<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $ids_grados = $data['grados'];

    if (!empty($ids_grados)) {
        $placeholders = implode(',', array_fill(0, count($ids_grados), '?'));
        $stmt = $pdo->prepare("SELECT * FROM salones WHERE id_grado IN ($placeholders)");
        $stmt->execute($ids_grados);
        $salones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($salones);
    } else {
        echo json_encode([]);
    }
}
?>
