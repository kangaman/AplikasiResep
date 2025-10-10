<?php
session_start();
include __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = (int)$_GET['order_id'];

try {
    // Ambil semua item dari pesanan
    $stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();

    if (empty($items)) {
        throw new Exception("Tidak ada item dalam pesanan ini.");
    }

    $new_total_modal = 0;

    $stmt_harga_baru = $conn->prepare("
        SELECT (b.harga_beli / b.jumlah_beli) as harga_per_satuan 
        FROM base_ingredients b 
        WHERE b.nama_bahan = ? AND b.user_id = ?
    ");

    foreach ($items as $item) {
        $hpp_per_item_baru = 0;
        if ($item['tipe_item'] == 'resep') {
            // Hitung ulang HPP untuk resep
            $stmt_ing = $conn->prepare("SELECT nama_bahan, jumlah FROM ingredients WHERE resep_id = ?");
            $stmt_ing->bind_param("i", $item['item_id']);
            $stmt_ing->execute();
            $ingredients = $stmt_ing->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt_ing->close();

            $hpp_resep_baru = 0;
            foreach($ingredients as $ing) {
                $stmt_harga_baru->bind_param("si", $ing['nama_bahan'], $user_id);
                $stmt_harga_baru->execute();
                $result_harga = $stmt_harga_baru->get_result();
                if($result_harga->num_rows > 0) {
                    $harga_baru = $result_harga->fetch_assoc()['harga_per_satuan'];
                    $hpp_resep_baru += $ing['jumlah'] * $harga_baru;
                }
            }
            $hpp_per_item_baru = $hpp_resep_baru;

        } elseif ($item['tipe_item'] == 'paket') {
            // Hitung ulang HPP untuk paket
            $stmt_paket = $conn->prepare("SELECT total_hpp FROM snackbox_paket WHERE id = ?");
            $stmt_paket->bind_param("i", $item['item_id']);
            $stmt_paket->execute();
            $hpp_per_item_baru = $stmt_paket->get_result()->fetch_assoc()['total_hpp'] ?? 0;
            $stmt_paket->close();
        }

        $new_total_modal += $item['jumlah'] * $hpp_per_item_baru;
    }

    $stmt_harga_baru->close();

    echo json_encode(['status' => 'success', 'new_total_modal' => $new_total_modal]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>