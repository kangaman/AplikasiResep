<?php
include __DIR__ . '/../config/config.php';
// Hanya bisa diakses jika sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Akses ditolak');
}
// Set header agar browser tahu ini adalah file JSON
header('Content-Type: application/json');

$response = [];

if (isset($_GET['term'])) {
    $user_id = $_SESSION['user_id'];
    $term = trim($_GET['term']);
    $search_param = "%" . $term . "%";
    
    // Cari nama bahan yang cocok dengan ketikan pengguna
    $stmt = $conn->prepare("SELECT nama_bahan FROM base_ingredients WHERE nama_bahan LIKE ? AND user_id = ? LIMIT 10");
    $stmt->bind_param("si", $search_param, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($bahan = $result->fetch_assoc()) {
        $response[] = $bahan['nama_bahan']; // Tambahkan nama bahan ke array response
    }
    $stmt->close();
}

// Kembalikan data dalam format JSON
echo json_encode($response);
?>