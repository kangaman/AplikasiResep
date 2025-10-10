<?php
session_start();
include __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['ingredient_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$ingredient_id = (int)$_GET['ingredient_id'];

try {
    $stmt = $conn->prepare("
        SELECT harga_beli, tanggal_update 
        FROM base_ingredients_history 
        WHERE ingredient_id = ? AND user_id = ? 
        ORDER BY tanggal_update DESC
    ");
    $stmt->bind_param("ii", $ingredient_id, $user_id);
    $stmt->execute();
    $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(['status' => 'success', 'history' => $history]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>