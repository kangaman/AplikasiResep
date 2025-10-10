<?php
include __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$resep_id_sumber = (int)$_GET['id'];

$conn->begin_transaction();

try {
    // 1. Ambil data resep sumber
    $stmt_sumber = $conn->prepare("SELECT * FROM resep WHERE id = ? AND user_id = ?");
    $stmt_sumber->bind_param("ii", $resep_id_sumber, $user_id);
    $stmt_sumber->execute();
    $result_sumber = $stmt_sumber->get_result();

    if ($result_sumber->num_rows === 0) {
        throw new Exception("Resep sumber tidak ditemukan.");
    }
    $resep_sumber = $result_sumber->fetch_assoc();
    $stmt_sumber->close();

    // 2. Buat resep baru (duplikat) dengan nama yang dimodifikasi
    $nama_kue_baru = $resep_sumber['nama_kue'] . " (Salinan)";
    $stmt_baru = $conn->prepare("INSERT INTO resep (nama_kue, bahan, langkah, user_id, gambar, youtube_url, porsi_default) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_baru->bind_param("sssisid", $nama_kue_baru, $resep_sumber['bahan'], $resep_sumber['langkah'], $user_id, $resep_sumber['gambar'], $resep_sumber['youtube_url'], $resep_sumber['porsi_default']);
    $stmt_baru->execute();
    $resep_id_baru = $stmt_baru->insert_id;
    $stmt_baru->close();

    // 3. Ambil semua bahan dari resep sumber
    $stmt_ing_sumber = $conn->prepare("SELECT * FROM ingredients WHERE resep_id = ?");
    $stmt_ing_sumber->bind_param("i", $resep_id_sumber);
    $stmt_ing_sumber->execute();
    $ingredients = $stmt_ing_sumber->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_ing_sumber->close();

    // 4. Salin semua bahan ke resep baru
    if (!empty($ingredients)) {
        $stmt_ing_baru = $conn->prepare("INSERT INTO ingredients (resep_id, nama_bahan, jumlah, satuan) VALUES (?, ?, ?, ?)");
        foreach ($ingredients as $ing) {
            $stmt_ing_baru->bind_param("isds", $resep_id_baru, $ing['nama_bahan'], $ing['jumlah'], $ing['satuan']);
            $stmt_ing_baru->execute();
        }
        $stmt_ing_baru->close();
    }

    $conn->commit();

    // 5. Arahkan pengguna ke halaman edit resep yang baru diduplikasi
    header("Location: form_resep.php?id=" . $resep_id_baru);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    // Jika gagal, kembalikan ke dashboard dengan pesan error
    header("Location: dashboard.php?pesan_error=" . urlencode("Gagal menduplikasi resep: " . $e->getMessage()));
    exit();
}
?>