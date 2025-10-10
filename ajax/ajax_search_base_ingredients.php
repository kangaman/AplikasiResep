<?php
session_start();
include __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];
$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

$search_query = "%" . $conn->real_escape_string($query) . "%";
$ingredients = [];

$stmt = $conn->prepare("SELECT id, nama_bahan, satuan_beli FROM base_ingredients WHERE user_id = ? AND nama_bahan LIKE ? LIMIT 10");
$stmt->bind_param("is", $user_id, $search_query);
$stmt->execute();
$result = $stmt->get_result();
if($result) {
    $ingredients = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

echo json_encode($ingredients);
?>