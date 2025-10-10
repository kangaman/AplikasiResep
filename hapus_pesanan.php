<?php
session_start();
include __DIR__ . '/config/config.php';

// Keamanan: Pastikan pengguna sudah login dan requestnya adalah POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = (int)$_POST['order_id'];

// Validasi: Pastikan order_id tidak kosong
if (empty($order_id)) {
    header("Location: manajemen_pesanan.php");
    exit();
}

try {
    // Proses Hapus dari Database
    // WHERE id = ? AND user_id = ? memastikan pengguna hanya bisa menghapus pesanannya sendiri
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Redirect Kembali: Arahkan pengguna kembali ke halaman manajemen pesanan
    // dengan parameter 'status=sukses_hapus' sebagai penanda bahwa hapus berhasil
    header("Location: manajemen_pesanan.php?status=sukses_hapus");
    exit();

} catch (Exception $e) {
    // Jika terjadi error, catat error tersebut
    error_log("Gagal menghapus pesanan: " . $e->getMessage());
    
    // Redirect kembali dengan notifikasi gagal
    header("Location: manajemen_pesanan.php?status=gagal_hapus");
    exit();
}
?>