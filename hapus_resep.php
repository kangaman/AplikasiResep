<?php
include __DIR__ . '/config/config.php';
// Cek sesi login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $resep_id = $_POST['id'];
    $user_id = $_SESSION['user_id'];

    // Ambil nama file gambar sebelum menghapus resep
    $stmt_get_gambar = $conn->prepare("SELECT gambar FROM resep WHERE id = ? AND user_id = ?");
    $stmt_get_gambar->bind_param("ii", $resep_id, $user_id);
    $stmt_get_gambar->execute();
    $result_gambar = $stmt_get_gambar->get_result();
    $gambar_data = $result_gambar->fetch_assoc();
    $stmt_get_gambar->close();

    // Hapus resep dari database
    $stmt = $conn->prepare("DELETE FROM resep WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $resep_id, $user_id);
    if ($stmt->execute()) {
        // Jika penghapusan dari DB berhasil, hapus juga file gambar fisiknya
        if ($gambar_data && !empty($gambar_data['gambar'])) {
            $file_path = 'uploads/' . $gambar_data['gambar'];
            if (file_exists($file_path)) {
                unlink($file_path); // Hapus file
            }
        }
    }
    $stmt->close();
}

header("Location: dashboard.php");
exit();
?>