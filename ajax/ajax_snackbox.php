<?php
include __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => 'Aksi tidak valid'];
$data = json_decode(file_get_contents('php://input'), true);

// Fungsi untuk menghitung HPP per porsi dari sebuah resep (real-time)
function get_resep_hpp($conn, $user_id, $nama_kue) {
    // Implementasi fungsi ini tidak berubah, sudah real-time
    $stmt_resep = $conn->prepare("SELECT id, porsi_default FROM resep WHERE nama_kue = ? AND user_id = ?");
    $stmt_resep->bind_param("si", $nama_kue, $user_id);
    $stmt_resep->execute();
    $resep_data = $stmt_resep->get_result()->fetch_assoc();
    $stmt_resep->close();

    if (!$resep_data || $resep_data['porsi_default'] == 0) return 0;
    
    $resep_id = $resep_data['id'];
    $porsi_default = $resep_data['porsi_default'];

    $stmt_ing = $conn->prepare("SELECT nama_bahan, jumlah FROM ingredients WHERE resep_id = ?");
    $stmt_ing->bind_param("i", $resep_id);
    $stmt_ing->execute();
    $ingredients = $stmt_ing->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_ing->close();

    $total_modal = 0;
    foreach ($ingredients as $ing) {
        $stmt_base = $conn->prepare("SELECT jumlah_beli, harga_beli FROM base_ingredients WHERE nama_bahan = ? AND user_id = ?");
        $stmt_base->bind_param("si", $ing['nama_bahan'], $user_id);
        $stmt_base->execute();
        $base_data = $stmt_base->get_result()->fetch_assoc();
        if ($base_data && $base_data['jumlah_beli'] > 0) {
            $harga_per_satuan = $base_data['harga_beli'] / $base_data['jumlah_beli'];
            $total_modal += $ing['jumlah'] * $harga_per_satuan;
        }
        $stmt_base->close();
    }
    return $total_modal / $porsi_default;
}

// Fungsi untuk menghitung HPP per item dari 'base_ingredients' (real-time)
function get_bahan_hpp($conn, $user_id, $nama_bahan) {
    $stmt_bahan = $conn->prepare("SELECT jumlah_beli, harga_beli FROM base_ingredients WHERE nama_bahan = ? AND user_id = ?");
    $stmt_bahan->bind_param("si", $nama_bahan, $user_id);
    $stmt_bahan->execute();
    $base_data = $stmt_bahan->get_result()->fetch_assoc();
    $stmt_bahan->close();

    if ($base_data && $base_data['jumlah_beli'] > 0) {
        return $base_data['harga_beli'] / $base_data['jumlah_beli'];
    }
    return 0;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action == 'search_items') {
        // ... (fungsi search tidak berubah)
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            echo json_encode(['status' => 'success', 'items' => []]);
            exit();
        }
        $items = [];
        $search_param = "%" . $q . "%";

        // Cari di Resep
        $stmt_resep = $conn->prepare("SELECT nama_kue FROM resep WHERE user_id = ? AND nama_kue LIKE ? LIMIT 5");
        $stmt_resep->bind_param("is", $user_id, $search_param);
        $stmt_resep->execute();
        $result_resep = $stmt_resep->get_result();
        while($row = $result_resep->fetch_assoc()) {
            $hpp = get_resep_hpp($conn, $user_id, $row['nama_kue']);
            $items[] = ['nama' => $row['nama_kue'], 'tipe' => 'resep', 'hpp' => $hpp];
        }
        $stmt_resep->close();

        // Cari di Bahan Dasar
        $stmt_bahan = $conn->prepare("SELECT nama_bahan FROM base_ingredients WHERE user_id = ? AND nama_bahan LIKE ? LIMIT 5");
        $stmt_bahan->bind_param("is", $user_id, $search_param);
        $stmt_bahan->execute();
        $result_bahan = $stmt_bahan->get_result();
        while($row = $result_bahan->fetch_assoc()) {
            $hpp = get_bahan_hpp($conn, $user_id, $row['nama_bahan']);
            if (!in_array($row['nama_bahan'], array_column($items, 'nama'))) {
                 $items[] = ['nama' => $row['nama_bahan'], 'tipe' => 'bahan', 'hpp' => $hpp];
            }
        }
        $stmt_bahan->close();
        
        $response = ['status' => 'success', 'items' => $items];

    } elseif ($action == 'get_list') {
        // --- LOGIKA REAL-TIME DIMULAI DI SINI ---
        $result = $conn->query("SELECT id, nama_paket, harga_jual FROM snackbox_paket WHERE user_id = $user_id ORDER BY nama_paket ASC");
        $list = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($list as $key => $paket) {
            $stmt_isi = $conn->prepare("SELECT tipe_item, nama_item FROM snackbox_paket_isi WHERE paket_id = ?");
            $stmt_isi->bind_param("i", $paket['id']);
            $stmt_isi->execute();
            $isi_paket = $stmt_isi->get_result()->fetch_all(MYSQLI_ASSOC);
            
            $realtime_hpp = 0;
            foreach ($isi_paket as $item) {
                if ($item['tipe_item'] == 'resep') {
                    $realtime_hpp += get_resep_hpp($conn, $user_id, $item['nama_item']);
                } else { // tipe 'bahan'
                    $realtime_hpp += get_bahan_hpp($conn, $user_id, $item['nama_item']);
                }
            }
            // Ganti HPP lama dengan HPP real-time yang baru dihitung
            $list[$key]['total_hpp'] = $realtime_hpp;
        }
        $response = ['status' => 'success', 'data' => $list];
        // --- AKHIR LOGIKA REAL-TIME ---

    } elseif ($action == 'get_detail') {
        // ... (tidak ada perubahan)
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (tidak ada perubahan pada logika POST)
}

// Salin semua logika GET 'get_detail' dan POST dari file sebelumnya ke sini
// Ini untuk memastikan fungsi simpan, edit, hapus tetap berjalan
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') == 'get_detail') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM snackbox_paket WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $paket = $stmt->get_result()->fetch_assoc();
        
        $stmt_isi = $conn->prepare("SELECT * FROM snackbox_paket_isi WHERE paket_id = ?");
        $stmt_isi->bind_param("i", $id);
        $stmt_isi->execute();
        $isi = $stmt_isi->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Hitung ulang HPP real-time saat mengambil detail
        foreach($isi as $key => $item) {
            if ($item['tipe_item'] == 'resep') {
                $isi[$key]['hpp_per_item'] = get_resep_hpp($conn, $user_id, $item['nama_item']);
            } else {
                $isi[$key]['hpp_per_item'] = get_bahan_hpp($conn, $user_id, $item['nama_item']);
            }
        }
        
        $paket['isi'] = $isi;
        $response = ['status' => 'success', 'data' => $paket];
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $data['action'] ?? null;
    if ($action == 'save_paket') {
        $id = $data['id'] ?? null;
        $nama_paket = trim($data['nama_paket']);
        $harga_jual = $data['harga_jual'];
        $total_hpp = $data['total_hpp'];
        $isi = $data['isi'];

        if (empty($nama_paket) || empty($harga_jual) || empty($isi)) {
            $response['message'] = 'Nama paket, harga jual, dan isi tidak boleh kosong.';
        } else {
            $conn->begin_transaction();
            try {
                if ($id) { // Update
                    $stmt = $conn->prepare("UPDATE snackbox_paket SET nama_paket=?, total_hpp=?, harga_jual=? WHERE id=? AND user_id=?");
                    $stmt->bind_param("sddii", $nama_paket, $total_hpp, $harga_jual, $id, $user_id);
                    $stmt->execute();
                    $stmt_del = $conn->prepare("DELETE FROM snackbox_paket_isi WHERE paket_id = ?");
                    $stmt_del->bind_param("i", $id);
                    $stmt_del->execute();
                } else { // Insert
                    $stmt = $conn->prepare("INSERT INTO snackbox_paket (user_id, nama_paket, total_hpp, harga_jual) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isdd", $user_id, $nama_paket, $total_hpp, $harga_jual);
                    $stmt->execute();
                    $id = $conn->insert_id;
                }
                
                $stmt_isi = $conn->prepare("INSERT INTO snackbox_paket_isi (paket_id, tipe_item, nama_item, hpp_per_item) VALUES (?, ?, ?, ?)");
                foreach ($isi as $item) {
                    $stmt_isi->bind_param("issd", $id, $item['tipe'], $item['nama'], $item['hpp']);
                    $stmt_isi->execute();
                }
                
                $conn->commit();
                $response = ['status' => 'success', 'message' => 'Paket snack box berhasil disimpan!'];
            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = 'Gagal menyimpan: ' . $e->getMessage();
            }
        }
    } elseif ($action == 'delete_paket') {
        $id = $data['id'] ?? null;
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM snackbox_paket WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Paket berhasil dihapus.'];
            }
        }
    }
}


echo json_encode($response);
?>