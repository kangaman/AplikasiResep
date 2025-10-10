<?php
// ajax/ajax_bahan.php

// PENTING: Mulai session agar bisa mengakses $_SESSION
session_start();

// Sertakan file konfigurasi database
include __DIR__ . '/../config/config.php';

// Atur header sebagai JSON di awal agar semua output konsisten
header('Content-Type: application/json');

// Periksa otorisasi
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // 401 Unauthorized
    echo json_encode(['error' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Jika query kosong, kembalikan array JSON kosong
if ($q === '') {
    echo json_encode([]); // Mengembalikan '[]'
    exit();
}

$stmt = $conn->prepare("SELECT id, nama_bahan as nama, jumlah_beli as jumlah, satuan_beli as satuan, harga_beli as harga
                        FROM base_ingredients
                        WHERE user_id = ? AND nama_bahan LIKE CONCAT('%', ?, '%')
                        ORDER BY nama_bahan ASC LIMIT 10");
$search_param = "%" . $q . "%"; // Tambahkan wildcard untuk LIKE
$stmt->bind_param("is", $user_id, $search_param); // Gunakan $search_param
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row; // Data sudah di-alias di query, jadi langsung gunakan
}

$stmt->close();
$conn->close();

echo json_encode($suggestions);
?>
