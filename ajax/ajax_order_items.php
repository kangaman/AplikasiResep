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

if (empty($query)) {
    echo json_encode([]);
    exit();
}

$search_query = "%" . $conn->real_escape_string($query) . "%";
$items = [];

// 1. Cari dari Resep
// Asumsi: Harga jual resep adalah 2x lipat dari HPP.
$stmt_resep = $conn->prepare("
    SELECT 
        r.id, 
        r.nama_kue as nama, 
        'resep' as tipe,
        (SELECT SUM((i.jumlah / b.jumlah_beli) * b.harga_beli) 
         FROM ingredients i 
         JOIN base_ingredients b ON i.nama_bahan = b.nama_bahan AND b.user_id = r.user_id
         WHERE i.resep_id = r.id) as hpp,
        COALESCE(((SELECT hpp) * 2), 0) as harga_jual
    FROM resep r
    WHERE r.user_id = ? AND r.nama_kue LIKE ?
    LIMIT 5
");
$stmt_resep->bind_param("is", $user_id, $search_query);
$stmt_resep->execute();
$result_resep = $stmt_resep->get_result();
while ($row = $result_resep->fetch_assoc()) {
    $row['hpp'] = $row['hpp'] ?? 0;
    $row['harga_jual'] = $row['harga_jual'] ?? 0;
    $items[] = $row;
}
$stmt_resep->close();

// 2. Cari dari Paket Snack Box
$stmt_paket = $conn->prepare("
    SELECT 
        id, 
        nama_paket as nama, 
        'paket' as tipe,
        total_hpp as hpp,
        harga_jual 
    FROM snackbox_paket
    WHERE user_id = ? AND nama_paket LIKE ?
    LIMIT 5
");
$stmt_paket->bind_param("is", $user_id, $search_query);
$stmt_paket->execute();
$result_paket = $stmt_paket->get_result();
while ($row = $result_paket->fetch_assoc()) {
    $items[] = $row;
}
$stmt_paket->close();

echo json_encode($items);
?>