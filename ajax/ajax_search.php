<?php
include __DIR__ . '/../config/config.php';

// Pastikan hanya user yang sudah login yang bisa mengakses
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo "<p>Akses ditolak.</p>";
    exit();
}

$user_id = $_SESSION['user_id'];
$resep_list = [];
$output = '';

// Ambil query pencarian dari permintaan AJAX
$search_query = trim($_GET['search'] ?? '');

if (!empty($search_query)) {
    // Jika ada query, cari di database
    $search_param = "%" . $search_query . "%";
    $stmt = $conn->prepare("SELECT id, nama_kue, gambar FROM resep WHERE user_id = ? AND (nama_kue LIKE ? OR bahan LIKE ?) ORDER BY id DESC");
    $stmt->bind_param("iss", $user_id, $search_param, $search_param);
} else {
    // Jika query kosong, tampilkan semua resep
    $stmt = $conn->prepare("SELECT id, nama_kue, gambar FROM resep WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $resep_list = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

// Bangun output HTML berdasarkan hasil
if (!empty($resep_list)) {
    foreach ($resep_list as $resep) {
        $gambar_html = '';
        if (!empty($resep['gambar'])) {
            $gambar_html = '<img src="uploads/' . htmlspecialchars($resep['gambar']) . '" alt="' . htmlspecialchars($resep['nama_kue']) . '" class="resep-thumbnail">';
        } else {
            // Menggunakan layanan ikon otomatis DiceBear
            $gambar_html = '<img src="https://api.dicebear.com/8.x/initials/svg?seed=' . urlencode($resep['nama_kue']) . '" alt="Ikon resep" class="resep-thumbnail">';
        }

        $output .= '
            <div class="resep-card">
                ' . $gambar_html . '
                <h3><a href="detail_resep.php?id=' . $resep['id'] . '">' . htmlspecialchars($resep['nama_kue']) . '</a></h3>
            </div>';
    }
} else {
    $output = "<p>Tidak ada resep yang ditemukan.</p>";
}

// Kembalikan (echo) hasil HTML
echo $output;
?>