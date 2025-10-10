<?php
include __DIR__ . '/../config/config.php';
// Hanya bisa diakses jika sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    exit('Akses ditolak');
}
// Set header agar browser tahu ini adalah file JSON
header('Content-Type: application/json');

$response = ['satuan' => ''];

if (isset($_GET['nama_bahan'])) {
    $user_id = $_SESSION['user_id'];
    $nama_bahan = trim($_GET['nama_bahan']);
    
    $stmt = $conn->prepare("SELECT satuan_beli FROM base_ingredients WHERE nama_bahan = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("si", $nama_bahan, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($bahan = $result->fetch_assoc()) {
        $response['satuan'] = $bahan['satuan_beli'];
    }
    $stmt->close();
}

// Kembalikan data dalam format JSON
echo json_encode($response);
?>