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

// =======================================================================
// 1. AMBIL PENGATURAN USER (MARGIN & OVERHEAD)
// =======================================================================
// Ini agar perhitungan harganya SAMA dengan yang tampil di detail resep.
$default_margin = 0;
$default_overhead = 0;

$stmt_settings = $conn->prepare("SELECT default_margin, default_overhead FROM user_settings WHERE user_id = ?");
$stmt_settings->bind_param("i", $user_id);
$stmt_settings->execute();
$res_settings = $stmt_settings->get_result();
if ($res_settings->num_rows > 0) {
    $row_settings = $res_settings->fetch_assoc();
    $default_margin = (float)$row_settings['default_margin'];
    $default_overhead = (float)$row_settings['default_overhead'];
}
$stmt_settings->close();


// =======================================================================
// 2. CARI DARI RESEP (DENGAN PERHITUNGAN HARGA ASLI)
// =======================================================================
$stmt_resep = $conn->prepare("
    SELECT 
        r.id, 
        r.nama_kue as nama, 
        r.porsi_default, 
        'resep' as tipe,
        (SELECT SUM((i.jumlah / b.jumlah_beli) * b.harga_beli) 
         FROM ingredients i 
         JOIN base_ingredients b ON i.nama_bahan = b.nama_bahan AND b.user_id = r.user_id
         WHERE i.resep_id = r.id) as total_modal_batch
    FROM resep r
    WHERE r.user_id = ? AND r.nama_kue LIKE ?
    LIMIT 5
");
$stmt_resep->bind_param("is", $user_id, $search_query);
$stmt_resep->execute();
$result_resep = $stmt_resep->get_result();

while ($row = $result_resep->fetch_assoc()) {
    $total_batch_cost = (float)($row['total_modal_batch'] ?? 0);
    $porsi = (int)$row['porsi_default'];
    if ($porsi <= 0) $porsi = 1;

    // 1. Hitung Modal Bahan per Porsi (HPP Murni)
    $hpp_murni = $total_batch_cost / $porsi;

    // 2. Tambahkan Overhead (Misal 5% untuk gas/listrik)
    $biaya_overhead = $hpp_murni * ($default_overhead / 100);
    $hpp_plus_overhead = $hpp_murni + $biaya_overhead;

    // 3. Hitung Harga Jual berdasarkan Margin User
    $keuntungan = $hpp_plus_overhead * ($default_margin / 100);
    $harga_jual_asli = $hpp_plus_overhead + $keuntungan;

    // --- PILIHAN ANDA DI SINI ---
    
    // OPSI A: Gunakan Harga Jual "Asli" (Sesuai Pengaturan Margin & Overhead)
    // Ini akan menghasilkan harga yg mirip dengan Rp 3.393 di screenshot Anda.
    $row['harga_jual'] = $harga_jual_asli;

    // OPSI B: Gunakan Perkalian Sederhana (Misal HPP x 1.3)
    // Jika ingin pakai ini, hapus tanda // di baris bawah ini:
    // $row['harga_jual'] = $hpp_murni * 1.3; 

    // Simpan HPP (Modal) untuk laporan
    $row['hpp'] = $hpp_plus_overhead; 
    
    // Bersihkan data yang tidak perlu dikirim
    unset($row['total_modal_batch']);
    unset($row['porsi_default']);

    $items[] = $row;
}
$stmt_resep->close();


// =======================================================================
// 3. CARI DARI PAKET SNACK BOX (HARGA SUDAH TERSIMPAN)
// =======================================================================
// Untuk Snack Box, kita ambil langsung dari database karena harganya fixed.
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
    $row['hpp'] = (float)$row['hpp'];
    $row['harga_jual'] = (float)$row['harga_jual'];
    $items[] = $row;
}
$stmt_paket->close();

echo json_encode($items);
?>
