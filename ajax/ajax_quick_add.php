<?php
include __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['nama_bahan']) || !isset($data['jumlah']) || !isset($data['satuan'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    exit();
}

$nama_bahan = $data['nama_bahan'];
$jumlah = (float)$data['jumlah'];
$satuan = $data['satuan'];

try {
    // Cek apakah item sudah ada di daftar belanja
    $stmt_cek = $conn->prepare("SELECT id, jumlah FROM shopping_list WHERE user_id = ? AND nama_bahan = ? AND satuan = ?");
    $stmt_cek->bind_param("iss", $user_id, $nama_bahan, $satuan);
    $stmt_cek->execute();
    $item_ada = $stmt_cek->get_result()->fetch_assoc();
    $stmt_cek->close();

    if ($item_ada) {
        // Jika sudah ada, update jumlahnya
        $jumlah_baru = $item_ada['jumlah'] + $jumlah;
        $stmt_update = $conn->prepare("UPDATE shopping_list SET jumlah = ? WHERE id = ?");
        $stmt_update->bind_param("di", $jumlah_baru, $item_ada['id']);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Jika belum ada, insert baru
        $stmt_insert = $conn->prepare("INSERT INTO shopping_list (user_id, nama_bahan, jumlah, satuan) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("isds", $user_id, $nama_bahan, $jumlah, $satuan);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    echo json_encode(['status' => 'success', 'message' => 'Item ditambahkan.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>