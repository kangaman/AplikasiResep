<?php
session_start();
include __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    if ($action === 'add') {
        $nama_bahan = trim($data['nama_bahan']);
        $jumlah = (float)$data['jumlah'];
        $satuan = trim($data['satuan']);
        $ingredient_id = isset($data['ingredient_id']) ? (int)$data['ingredient_id'] : null;

        if (empty($nama_bahan) || empty($jumlah) || empty($satuan)) {
            throw new Exception('Semua kolom wajib diisi.');
        }

        $stmt = $conn->prepare("INSERT INTO shopping_list (user_id, ingredient_id, nama_bahan, jumlah, satuan) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisds", $user_id, $ingredient_id, $nama_bahan, $jumlah, $satuan);
        $stmt->execute();
        $new_id = $stmt->insert_id;
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Item berhasil ditambahkan.', 'new_id' => $new_id]);

    } elseif ($action === 'update_qty') {
        $id = (int)$data['id'];
        $jumlah = (float)$data['jumlah'];
        $stmt = $conn->prepare("UPDATE shopping_list SET jumlah = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("dii", $jumlah, $id, $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Jumlah berhasil diperbarui.']);

    } elseif ($action === 'delete') {
        $id = (int)$data['id'];
        $stmt = $conn->prepare("DELETE FROM shopping_list WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Item berhasil dihapus.']);

    } elseif ($action === 'update_price') {
        $ingredient_id = (int)$data['ingredient_id'];
        $harga_beli = (int)$data['harga_beli'];
        
        // Ambil harga lama
        $stmt_old = $conn->prepare("SELECT harga_beli FROM base_ingredients WHERE id = ? AND user_id = ?");
        $stmt_old->bind_param("ii", $ingredient_id, $user_id);
        $stmt_old->execute();
        $old_price = (int)$stmt_old->get_result()->fetch_assoc()['harga_beli'];
        $stmt_old->close();

        // Jika harga berubah, simpan ke histori dan update
        if ($old_price !== $harga_beli) {
            $stmt_history = $conn->prepare("INSERT INTO base_ingredients_history (ingredient_id, user_id, harga_beli) VALUES (?, ?, ?)");
            $stmt_history->bind_param("iii", $ingredient_id, $user_id, $harga_beli);
            $stmt_history->execute();
            $stmt_history->close();

            $stmt_update = $conn->prepare("UPDATE base_ingredients SET harga_beli = ? WHERE id = ? AND user_id = ?");
            $stmt_update->bind_param("iii", $harga_beli, $ingredient_id, $user_id);
            $stmt_update->execute();
            $stmt_update->close();
        }
        echo json_encode(['status' => 'success', 'message' => 'Harga bahan berhasil diperbarui.']);
        
    } else {
        throw new Exception('Aksi tidak valid.');
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>