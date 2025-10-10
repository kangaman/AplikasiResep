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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $data['action'] ?? null;

    if ($action == 'save_olahan') {
        $id = $data['id'] ?? null;
        $nama_olahan = trim($data['nama_olahan']);
        $total_hasil = $data['total_hasil'];
        $satuan_hasil = trim($data['satuan_hasil']);
        $total_biaya = $data['total_biaya'];
        $komposisi = $data['komposisi'];

        if (empty($nama_olahan) || empty($total_hasil) || empty($satuan_hasil) || empty($komposisi)) {
            $response['message'] = 'Data tidak lengkap.';
        } else {
            $conn->begin_transaction();
            try {
                $base_ingredient_id = null;
                if ($id) {
                    $stmt_get_base_id = $conn->prepare("SELECT base_ingredient_id FROM bahan_olahan WHERE id = ? AND user_id = ?");
                    $stmt_get_base_id->bind_param("ii", $id, $user_id);
                    $stmt_get_base_id->execute();
                    $base_ingredient_id = $stmt_get_base_id->get_result()->fetch_assoc()['base_ingredient_id'] ?? null;
                    $stmt_get_base_id->close();
                }

                if ($base_ingredient_id) {
                    $stmt_base = $conn->prepare("UPDATE base_ingredients SET nama_bahan=?, jumlah_beli=?, satuan_beli=?, harga_beli=? WHERE id=? AND user_id=?");
                    $stmt_base->bind_param("sdsiii", $nama_olahan, $total_hasil, $satuan_hasil, $total_biaya, $base_ingredient_id, $user_id);
                } else {
                    $stmt_base = $conn->prepare("INSERT INTO base_ingredients (user_id, nama_bahan, jumlah_beli, satuan_beli, harga_beli) VALUES (?, ?, ?, ?, ?)");
                    $stmt_base->bind_param("isdsi", $user_id, $nama_olahan, $total_hasil, $satuan_hasil, $total_biaya);
                }
                $stmt_base->execute();
                if (!$base_ingredient_id) {
                    $base_ingredient_id = $conn->insert_id;
                }
                $stmt_base->close();

                if ($id) {
                    $stmt_olahan = $conn->prepare("UPDATE bahan_olahan SET nama_olahan=?, total_hasil=?, satuan_hasil=?, total_biaya=?, base_ingredient_id=? WHERE id=? AND user_id=?");
                    $stmt_olahan->bind_param("sdsdiii", $nama_olahan, $total_hasil, $satuan_hasil, $total_biaya, $base_ingredient_id, $id, $user_id);
                    $stmt_olahan->execute();
                    
                    $stmt_del_komposisi = $conn->prepare("DELETE FROM bahan_olahan_komposisi WHERE bahan_olahan_id = ?");
                    $stmt_del_komposisi->bind_param("i", $id);
                    $stmt_del_komposisi->execute();
                } else {
                    $stmt_olahan = $conn->prepare("INSERT INTO bahan_olahan (user_id, nama_olahan, total_hasil, satuan_hasil, total_biaya, base_ingredient_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_olahan->bind_param("isdsdi", $user_id, $nama_olahan, $total_hasil, $satuan_hasil, $total_biaya, $base_ingredient_id);
                    $stmt_olahan->execute();
                    $id = $conn->insert_id;
                }
                $stmt_olahan->close();

                $stmt_komposisi = $conn->prepare("INSERT INTO bahan_olahan_komposisi (bahan_olahan_id, nama_bahan_dasar, jumlah, satuan) VALUES (?, ?, ?, ?)");
                foreach ($komposisi as $item) {
                    $stmt_komposisi->bind_param("isds", $id, $item['nama'], $item['jumlah'], $item['satuan']);
                    $stmt_komposisi->execute();
                }
                $stmt_komposisi->close();
                
                $conn->commit();
                $response = ['status' => 'success', 'message' => 'Bahan olahan berhasil disimpan!'];
            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    } elseif ($action == 'delete_olahan') {
        $id = $data['id'] ?? null;
        if ($id) {
            $stmt_get = $conn->prepare("SELECT base_ingredient_id FROM bahan_olahan WHERE id = ? AND user_id = ?");
            $stmt_get->bind_param("ii", $id, $user_id);
            $stmt_get->execute();
            $result = $stmt_get->get_result()->fetch_assoc();
            $stmt_get->close();

            $stmt_del_olahan = $conn->prepare("DELETE FROM bahan_olahan WHERE id = ? AND user_id = ?");
            $stmt_del_olahan->bind_param("ii", $id, $user_id);
            $stmt_del_olahan->execute();

            if ($result && $result['base_ingredient_id']) {
                $stmt_del_base = $conn->prepare("DELETE FROM base_ingredients WHERE id = ? AND user_id = ?");
                $stmt_del_base->bind_param("ii", $result['base_ingredient_id'], $user_id);
                $stmt_del_base->execute();
            }
            $response = ['status' => 'success', 'message' => 'Bahan olahan berhasil dihapus.'];
        }
    } elseif ($action == 'duplicate_olahan') {
        $id = $data['id'] ?? null;
        if ($id) {
            $conn->begin_transaction();
            try {
                $stmt_get = $conn->prepare("SELECT * FROM bahan_olahan WHERE id = ? AND user_id = ?");
                $stmt_get->bind_param("ii", $id, $user_id);
                $stmt_get->execute();
                $original_olahan = $stmt_get->get_result()->fetch_assoc();
                $stmt_get->close();

                if ($original_olahan) {
                    $stmt_get_comp = $conn->prepare("SELECT * FROM bahan_olahan_komposisi WHERE bahan_olahan_id = ?");
                    $stmt_get_comp->bind_param("i", $id);
                    $stmt_get_comp->execute();
                    $original_komposisi = $stmt_get_comp->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt_get_comp->close();

                    $new_nama_olahan = $original_olahan['nama_olahan'] . ' - Salinan';
                    $stmt_insert_olahan = $conn->prepare("INSERT INTO bahan_olahan (user_id, nama_olahan, total_hasil, satuan_hasil, total_biaya, base_ingredient_id) VALUES (?, ?, ?, ?, ?, NULL)");
                    $stmt_insert_olahan->bind_param("isdsd", $user_id, $new_nama_olahan, $original_olahan['total_hasil'], $original_olahan['satuan_hasil'], $original_olahan['total_biaya']);
                    $stmt_insert_olahan->execute();
                    $new_olahan_id = $conn->insert_id;
                    $stmt_insert_olahan->close();

                    if (!empty($original_komposisi)) {
                        $stmt_insert_comp = $conn->prepare("INSERT INTO bahan_olahan_komposisi (bahan_olahan_id, nama_bahan_dasar, jumlah, satuan) VALUES (?, ?, ?, ?)");
                        foreach ($original_komposisi as $item) {
                            $stmt_insert_comp->bind_param("isds", $new_olahan_id, $item['nama_bahan_dasar'], $item['jumlah'], $item['satuan']);
                            $stmt_insert_comp->execute();
                        }
                        $stmt_insert_comp->close();
                    }

                    $conn->commit();
                    $response = ['status' => 'success', 'message' => 'Bahan olahan berhasil diduplikasi.'];
                } else {
                    $response['message'] = 'Bahan olahan asli tidak ditemukan.';
                    $conn->rollback();
                }
            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = 'Gagal menduplikasi: ' . $e->getMessage();
            }
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;
    
    // --- BAGIAN YANG DIPERBARUI ---
    if ($action == 'get_list') {
        // Query sekarang mengambil juga total_biaya, total_hasil, dan satuan_hasil
        $result = $conn->query("SELECT id, nama_olahan, total_biaya, total_hasil, satuan_hasil FROM bahan_olahan WHERE user_id = $user_id ORDER BY nama_olahan ASC");
        $list = $result->fetch_all(MYSQLI_ASSOC);
        $response = ['status' => 'success', 'data' => $list];
    } 
    // --- AKHIR BAGIAN YANG DIPERBARUI ---
    elseif ($action == 'get_detail') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM bahan_olahan WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $olahan = $stmt->get_result()->fetch_assoc();
            
            $stmt_komposisi = $conn->prepare("SELECT * FROM bahan_olahan_komposisi WHERE bahan_olahan_id = ?");
            $stmt_komposisi->bind_param("i", $id);
            $stmt_komposisi->execute();
            $komposisi = $stmt_komposisi->get_result()->fetch_all(MYSQLI_ASSOC);
            
            $olahan['komposisi'] = $komposisi;
            $response = ['status' => 'success', 'data' => $olahan];
        }
    }
}

echo json_encode($response);
?>