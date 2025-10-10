<?php
session_start();
include __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = (int)$_POST['order_id'];
$nama_pelanggan = trim($_POST['nama_pelanggan']);
$kontak_pelanggan = trim($_POST['kontak_pelanggan']);
$tanggal_pengiriman = $_POST['tanggal_pengiriman'];
$alamat_pengiriman = trim($_POST['alamat_pengiriman']);
$items = $_POST['items'] ?? [];
$expenses = $_POST['expenses'] ?? [];

if (empty($nama_pelanggan) || empty($tanggal_pengiriman) || empty($items)) {
    header("Location: form_pesanan.php?status=error_data_tidak_lengkap" . ($order_id ? "&edit_id=$order_id" : ""));
    exit();
}

$conn->begin_transaction();

try {
    $total_hpp_items = 0;
    $total_jual_keseluruhan = 0;
    foreach ($items as $item) {
        $jumlah = (int)$item['jumlah'];
        $hpp_per_item = (float)$item['hpp_per_item'];
        $harga_jual_per_item = (float)$item['harga_jual_per_item'];
        $total_hpp_items += $jumlah * $hpp_per_item;
        $total_jual_keseluruhan += $jumlah * $harga_jual_per_item;
    }

    $total_biaya_lain = 0;
    if (!empty($expenses['deskripsi'])) {
        foreach ($expenses['jumlah'] as $jumlah_biaya) {
            if (!empty($jumlah_biaya)) {
                $total_biaya_lain += (float)$jumlah_biaya;
            }
        }
    }
    
    // Total modal adalah HPP item + biaya lain-lain
    $total_modal_keseluruhan = $total_hpp_items + $total_biaya_lain;

    if ($order_id > 0) { // Mode UPDATE
        $stmt_order = $conn->prepare(
            "UPDATE orders SET nama_pelanggan=?, kontak_pelanggan=?, tanggal_pengiriman=?, alamat_pengiriman=?, total_modal=?, total_jual=?, biaya_lain=?
             WHERE id=? AND user_id=?"
        );
        $stmt_order->bind_param("ssssdddii", $nama_pelanggan, $kontak_pelanggan, $tanggal_pengiriman, $alamat_pengiriman, $total_modal_keseluruhan, $total_jual_keseluruhan, $total_biaya_lain, $order_id, $user_id);
        $stmt_order->execute();
        $stmt_order->close();
        
        $conn->query("DELETE FROM order_items WHERE order_id = $order_id");
        $conn->query("DELETE FROM order_expenses WHERE order_id = $order_id");
        $new_order_id = $order_id;

    } else { // Mode INSERT
        $stmt_order = $conn->prepare(
            "INSERT INTO orders (user_id, nama_pelanggan, kontak_pelanggan, tanggal_pengiriman, alamat_pengiriman, total_modal, total_jual, biaya_lain, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Baru')"
        );
        $stmt_order->bind_param("issssddd", $user_id, $nama_pelanggan, $kontak_pelanggan, $tanggal_pengiriman, $alamat_pengiriman, $total_modal_keseluruhan, $total_jual_keseluruhan, $total_biaya_lain);
        $stmt_order->execute();
        $new_order_id = $stmt_order->insert_id;
        $stmt_order->close();
    }

    // Masukkan kembali semua item
    $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, tipe_item, item_id, nama_item, jumlah, hpp_per_item, harga_jual_per_item) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmt_items->bind_param("isisidd", $new_order_id, $item['tipe_item'], $item['item_id'], $item['nama_item'], $item['jumlah'], $item['hpp_per_item'], $item['harga_jual_per_item']);
        $stmt_items->execute();
    }
    $stmt_items->close();
    
    // Masukkan biaya tambahan
    if (!empty($expenses['deskripsi'])) {
        $stmt_expenses = $conn->prepare("INSERT INTO order_expenses (order_id, deskripsi, jumlah) VALUES (?, ?, ?)");
        foreach ($expenses['deskripsi'] as $key => $deskripsi) {
            $jumlah_biaya = $expenses['jumlah'][$key];
            if (!empty($deskripsi) && !empty($jumlah_biaya)) {
                $stmt_expenses->bind_param("isd", $new_order_id, $deskripsi, $jumlah_biaya);
                $stmt_expenses->execute();
            }
        }
        $stmt_expenses->close();
    }

    $conn->commit();
    header("Location: detail_pesanan.php?id=" . $new_order_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Gagal menyimpan/update pesanan: " . $e->getMessage());
    header("Location: manajemen_pesanan.php?status=gagal");
    exit();
}
?>