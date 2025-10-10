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

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- AKSI UNTUK MENYIMPAN DRAFT ---
    if ($action == 'save_draft') {
        $draft_name = trim($_POST['draft_name']);
        $ingredients = json_decode($_POST['ingredients'], true);
        $draft_id = $_POST['draft_id'] ?? null;

        if (empty($draft_name) || empty($ingredients)) {
            $response['message'] = 'Nama draft dan bahan tidak boleh kosong.';
        } else {
            // Jika draft_id ada, update. Jika tidak, buat baru.
            if ($draft_id) { // Update
                $stmt = $conn->prepare("UPDATE calculator_drafts SET draft_name = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("sii", $draft_name, $draft_id, $user_id);
                $stmt->execute();
                // Hapus bahan lama
                $del_stmt = $conn->prepare("DELETE FROM calculator_ingredients WHERE draft_id = ?");
                $del_stmt->bind_param("i", $draft_id);
                $del_stmt->execute();
            } else { // Insert baru
                $stmt = $conn->prepare("INSERT INTO calculator_drafts (user_id, draft_name) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $draft_name);
                $stmt->execute();
                $draft_id = $conn->insert_id;
            }

            // Masukkan bahan-bahan yang baru
            $ing_stmt = $conn->prepare("INSERT INTO calculator_ingredients (draft_id, nama_bahan, jumlah, satuan) VALUES (?, ?, ?, ?)");
            foreach ($ingredients as $ing) {
                $ing_stmt->bind_param("isds", $draft_id, $ing['nama'], $ing['jumlah'], $ing['satuan']);
                $ing_stmt->execute();
            }
            $response = ['status' => 'success', 'message' => 'Draft berhasil disimpan!', 'new_draft_id' => $draft_id];
        }
    }

    // --- AKSI UNTUK MENGHAPUS DRAFT ---
    elseif ($action == 'delete_draft') {
        $draft_id = $_POST['draft_id'];
        if ($draft_id) {
            $stmt = $conn->prepare("DELETE FROM calculator_drafts WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $draft_id, $user_id);
            $stmt->execute();
            $response = ['status' => 'success', 'message' => 'Draft berhasil dihapus!'];
        }
    }
}

// --- AKSI UNTUK MENGAMBIL DAFTAR DRAFT ATAU DETAIL DRAFT (GET request) ---
elseif (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Ambil semua daftar draft
    if ($action == 'get_drafts') {
        $result = $conn->query("SELECT id, draft_name FROM calculator_drafts WHERE user_id = $user_id ORDER BY draft_name ASC");
        $drafts = $result->fetch_all(MYSQLI_ASSOC);
        $response = ['status' => 'success', 'drafts' => $drafts];
    }
    // Ambil bahan dari satu draft spesifik
    elseif ($action == 'load_draft' && isset($_GET['draft_id'])) {
        $draft_id = $_GET['draft_id'];
        $stmt = $conn->prepare("SELECT nama_bahan, jumlah, satuan FROM calculator_ingredients WHERE draft_id = ?");
        $stmt->bind_param("i", $draft_id);
        $stmt->execute();
        $ingredients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $response = ['status' => 'success', 'ingredients' => $ingredients];
    }
}

echo json_encode($response);
?>