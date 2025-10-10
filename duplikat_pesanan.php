<?php
session_start();
include __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$source_order_id = (int)$_GET['id'];

$conn->begin_transaction();

try {
    // 1. Ambil data pesanan sumber (yang akan diduplikasi)
    $stmt_source_order = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt_source_order->bind_param("ii", $source_order_id, $user_id);
    $stmt_source_order->execute();
    $result_source_order = $stmt_source_order->get_result();
    if ($result_source_order->num_rows === 0) {
        throw new Exception("Pesanan sumber tidak ditemukan.");
    }
    $source_order = $result_source_order->fetch_assoc();
    $stmt_source_order->close();

    // 2. Buat pesanan baru (duplikat)
    $new_customer_name = $source_order['nama_pelanggan'] . " (Salinan)";
    $new_delivery_date = date('Y-m-d'); // Tanggal diatur ke hari ini
    $new_status = 'Baru';

    $stmt_new_order = $conn->prepare(
        "INSERT INTO orders (user_id, customer_id, nama_pelanggan, kontak_pelanggan, tanggal_pengiriman, alamat_pengiriman, total_modal, total_jual, biaya_lain, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    
    // ## INI BAGIAN YANG DIPERBAIKI ##
    // Tipe data yang dikirim ke database disesuaikan agar cocok (iissssddds)
    $stmt_new_order->bind_param(
        "iissssddds",
        $user_id,
        $source_order['customer_id'],
        $new_customer_name,
        $source_order['kontak_pelanggan'],
        $new_delivery_date,
        $source_order['alamat_pengiriman'],
        $source_order['total_modal'],
        $source_order['total_jual'],
        $source_order['biaya_lain'],
        $new_status
    );
    $stmt_new_order->execute();
    $new_order_id = $stmt_new_order->insert_id;
    $stmt_new_order->close();

    // 3. Ambil semua item & biaya dari pesanan sumber
    $items = $conn->query("SELECT * FROM order_items WHERE order_id = $source_order_id")->fetch_all(MYSQLI_ASSOC);
    $expenses = $conn->query("SELECT * FROM order_expenses WHERE order_id = $source_order_id")->fetch_all(MYSQLI_ASSOC);

    // 4. Salin semua item ke pesanan baru
    if (!empty($items)) {
        $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, tipe_item, item_id, nama_item, jumlah, hpp_per_item, harga_jual_per_item) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt_items->bind_param("isisidd", $new_order_id, $item['tipe_item'], $item['item_id'], $item['nama_item'], $item['jumlah'], $item['hpp_per_item'], $item['harga_jual_per_item']);
            $stmt_items->execute();
        }
        $stmt_items->close();
    }
    
    // 5. Salin semua biaya tambahan ke pesanan baru
    if (!empty($expenses)) {
        $stmt_expenses = $conn->prepare("INSERT INTO order_expenses (order_id, deskripsi, jumlah) VALUES (?, ?, ?)");
        foreach ($expenses as $expense) {
            $stmt_expenses->bind_param("isd", $new_order_id, $expense['deskripsi'], $expense['jumlah']);
            $stmt_expenses->execute();
        }
        $stmt_expenses->close();
    }

    $conn->commit();

    // 6. Arahkan pengguna ke halaman EDIT pesanan yang baru diduplikasi
    header("Location: form_pesanan.php?edit_id=" . $new_order_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    // Tampilkan error untuk debugging jika terjadi masalah
    // die("Error: " . $e->getMessage()); 
    header("Location: manajemen_pesanan.php?status=gagal_duplikat");
    exit();
}
?>