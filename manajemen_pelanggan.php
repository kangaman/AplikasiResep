<?php
// 1. Inisialisasi Session & Config DULUAN (Sebelum ada HTML)
// Kita panggil manual di sini agar bisa memproses data sebelum header.php dimuat
session_start();
include __DIR__ . '/config/config.php';

// 2. Cek Login
if (!isset($_SESSION['user_id'])) { 
    header("Location: index.php"); 
    exit(); 
}

$user_id = $_SESSION['user_id'];
$pesan = '';
$customer_edit = null;

// --- LOGIKA PROSES DATA (Dipindah ke Atas) ---

// A. Logika Simpan / Update Pelanggan
if (isset($_POST['simpan_pelanggan'])) {
    $id = $_POST['customer_id'];
    $nama = trim($_POST['nama_pelanggan']);
    $kontak = trim($_POST['kontak']);
    $alamat = trim($_POST['alamat']);

    if (empty($nama)) {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Nama pelanggan wajib diisi.</div>";
    } else {
        if (empty($id)) { // INSERT BARU
            $stmt = $conn->prepare("INSERT INTO customers (user_id, nama_pelanggan, kontak, alamat) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $nama, $kontak, $alamat);
            $msg_text = "Pelanggan baru berhasil ditambahkan!";
        } else { // UPDATE
            $stmt = $conn->prepare("UPDATE customers SET nama_pelanggan=?, kontak=?, alamat=? WHERE id=? AND user_id=?");
            $stmt->bind_param("sssii", $nama, $kontak, $alamat, $id, $user_id);
            $msg_text = "Data pelanggan berhasil diperbarui!";
        }
        $stmt->execute();
        $stmt->close();
        
        // Redirect AMAN di sini karena belum ada HTML yang dicetak
        header("Location: manajemen_pelanggan.php?pesan=" . urlencode($msg_text));
        exit();
    }
}

// B. Logika Hapus Pelanggan
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Redirect AMAN
    header("Location: manajemen_pelanggan.php?pesan=" . urlencode("Pelanggan berhasil dihapus."));
    exit();
}

// C. Logika Ambil Data untuk Mode Edit
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $customer_edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// D. Ambil semua data pelanggan untuk tabel
$customers = [];
$stmt_all = $conn->prepare("SELECT * FROM customers WHERE user_id = ? ORDER BY nama_pelanggan ASC");
$stmt_all->bind_param("i", $user_id);
$stmt_all->execute();
$result = $stmt_all->get_result();
if($result) {
    $customers = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt_all->close();

// E. Tangkap pesan dari URL (hasil redirect)
if (isset($_GET['pesan'])) {
    $pesan = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>" . htmlspecialchars($_GET['pesan']) . "</div>";
}

// --- AKHIR LOGIKA PHP ---
// Sekarang baru aman untuk memanggil header yang berisi HTML
include __DIR__ . '/includes/header.php'; 
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pelanggan - Catatan Rasa Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "foreground-light": "#181411", "foreground-dark": "#f8f7f6", "input-light": "#f4f2f0", "input-dark": "#3a2d21", "subtle-dark": "#3a2e22" },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-foreground-light dark:text-foreground-dark">
<div class="flex flex-col min-h-screen pb-20">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="p-4 flex-grow">
        <div id="form-pelanggan" class="max-w-2xl mx-auto mb-8">
            <h2 class="text-xl font-bold mb-4 text-center"><?php echo $customer_edit ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru'; ?></h2>
            <?php if ($pesan) echo $pesan; ?>
            <form class="space-y-4 bg-white dark:bg-subtle-dark p-6 rounded-lg shadow" method="POST">
                <input type="hidden" name="customer_id" value="<?php echo $customer_edit['id'] ?? ''; ?>">
                <div>
                    <label class="block text-sm font-medium mb-1" for="nama_pelanggan">Nama Pelanggan</label>
                    <input class="w-full h-12 bg-input-light dark:bg-input-dark border-0 rounded-lg px-4 focus:ring-2 focus:ring-primary" id="nama_pelanggan" name="nama_pelanggan" value="<?php echo htmlspecialchars($customer_edit['nama_pelanggan'] ?? ''); ?>" type="text" required/>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="kontak">Kontak (WA/Telp)</label>
                    <input class="w-full h-12 bg-input-light dark:bg-input-dark border-0 rounded-lg px-4 focus:ring-2 focus:ring-primary" id="kontak" name="kontak" value="<?php echo htmlspecialchars($customer_edit['kontak'] ?? ''); ?>" type="text"/>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="alamat">Alamat</label>
                    <textarea class="w-full min-h-[80px] bg-input-light dark:bg-input-dark border-0 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary" id="alamat" name="alamat"><?php echo htmlspecialchars($customer_edit['alamat'] ?? ''); ?></textarea>
                </div>
                <div class="flex gap-4 pt-2">
                    <button type="submit" name="simpan_pelanggan" class="w-full h-12 bg-primary text-white font-bold rounded-full flex items-center justify-center text-base tracking-wide shadow-lg">
                        <?php echo $customer_edit ? 'Update Pelanggan' : 'Simpan Pelanggan'; ?>
                    </button>
                    <?php if ($customer_edit): ?>
                        <a href="manajemen_pelanggan.php" class="w-full h-12 bg-gray-300 text-gray-800 font-bold rounded-full flex items-center justify-center text-base tracking-wide">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="max-w-4xl mx-auto mt-12">
            <h2 class="text-2xl font-bold mb-6 text-center">Daftar Pelanggan</h2>
            <div class="bg-white dark:bg-subtle-dark rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-gray-500 bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="p-3">Nama Pelanggan</th>
                                <th class="p-3">Kontak</th>
                                <th class="p-3 hidden md:table-cell">Alamat</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($customers)): ?>
                                <tr><td colspan="4" class="p-4 text-center text-gray-500">Belum ada data pelanggan.</td></tr>
                            <?php else: ?>
                                <?php foreach($customers as $customer): ?>
                                    <tr class="border-t border-gray-200 dark:border-gray-700">
                                        <td class="p-3 font-semibold"><?php echo htmlspecialchars($customer['nama_pelanggan']); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($customer['kontak']); ?></td>
                                        <td class="p-3 hidden md:table-cell"><?php echo nl2br(htmlspecialchars($customer['alamat'])); ?></td>
                                        <td class="p-3 text-right">
                                            <a href="manajemen_pelanggan.php?edit=<?php echo $customer['id']; ?>#form-pelanggan" class="text-primary hover:underline font-semibold">Edit</a>
                                            <a href="manajemen_pelanggan.php?hapus=<?php echo $customer['id']; ?>" class="text-red-500 hover:underline font-semibold ml-4" onclick="return confirm('Yakin ingin menghapus pelanggan ini?');">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>
