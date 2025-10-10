<?php
session_start();
include __DIR__ . '/config/config.php';

// 1. Keamanan: Pastikan pengguna sudah login dan requestnya adalah POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = (int)$_POST['order_id'];
$new_status = $_POST['status'];

// 2. Validasi: Buat daftar status yang diizinkan untuk mencegah input yang tidak valid
$valid_statuses = ['Baru', 'Diproses', 'Siap Diambil/Kirim', 'Selesai', 'Dibatalkan'];

if (empty($order_id) || !in_array($new_status, $valid_statuses)) {
    // Jika data yang dikirim tidak valid, kembalikan ke halaman manajemen pesanan
    header("Location: manajemen_pesanan.php");
    exit();
}

try {
    // 3. Proses Update ke Database
    // Query ini akan mengubah kolom 'status' pada tabel 'orders'
    // WHERE id = ? AND user_id = ? memastikan bahwa pengguna hanya bisa mengubah status pesanannya sendiri
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $new_status, $order_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // 4. Redirect Kembali: Arahkan pengguna kembali ke halaman detail pesanan
    // dengan parameter 'status_updated=1' sebagai penanda bahwa update berhasil
    header("Location: detail_pesanan.php?id=" . $order_id . "&status_updated=1");
    exit();

} catch (Exception $e) {
    // Jika terjadi error, catat error tersebut (berguna untuk developer)
    error_log("Gagal update status pesanan: " . $e->getMessage());
    
    // Redirect kembali ke halaman detail pesanan dengan penanda 'status_updated=0' (gagal)
    header("Location: detail_pesanan.php?id=" . $order_id . "&status_updated=0");
    exit();
}
?>