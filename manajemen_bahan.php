<?php
// 1. Mulai sesi & Config DULUAN sebelum ada HTML apapun
session_start();
include __DIR__ . '/config/config.php';

// 2. Cek Login
if (!isset($_SESSION['user_id'])) { 
    header("Location: index.php"); 
    exit(); 
}

$user_id = $_SESSION['user_id'];
$pesan = '';
$bahan_edit = null;

// Definisikan daftar kategori & satuan
$kategori_list = [
    'Bahan Kering', 'Bahan Basah', 'Lemak & Produk Susu', 
    'Pelengkap & Isian', 'Bahan Olahan', 'Non-Pangan & Kemasan', 'Lain-lain'
];
$satuan_list = [
    'gram', 'kg', 'ml', 'liter', 'buah', 'pack', 'sachet', 'sdm', 'sdt'
];

// --- LOGIKA PHP DIPINDAHKAN KE SINI (PALING ATAS) ---

// Logika Simpan / Update
if (isset($_POST['simpan'])) {
    $id = $_POST['id'];
    $nama = trim($_POST['nama_bahan']);
    $kategori = $_POST['kategori'];
    $jumlah = $_POST['jumlah_beli'];
    $satuan = trim($_POST['satuan_beli']);
    $harga = (int)$_POST['harga_beli'];

    if (empty($nama) || empty($jumlah) || empty($satuan) || !isset($harga) || empty($kategori)) {
        $pesan = "Semua kolom wajib diisi.";
    } else {
        if (empty($id)) { // INSERT BARU
            $stmt = $conn->prepare("INSERT INTO base_ingredients (user_id, nama_bahan, kategori, jumlah_beli, satuan_beli, harga_beli) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdsi", $user_id, $nama, $kategori, $jumlah, $satuan, $harga);
            $stmt->execute();
            $new_id = $stmt->insert_id;

            // Simpan harga pertama ke histori
            if ($new_id > 0) {
                $stmt_history = $conn->prepare("INSERT INTO base_ingredients_history (ingredient_id, user_id, harga_beli) VALUES (?, ?, ?)");
                $stmt_history->bind_param("iii", $new_id, $user_id, $harga);
                $stmt_history->execute();
                $stmt_history->close();
            }
            $pesan_teks = "Bahan baru berhasil ditambahkan!";
        } else { // UPDATE LAMA
            // Ambil harga lama
            $stmt_old = $conn->prepare("SELECT harga_beli FROM base_ingredients WHERE id = ? AND user_id = ?");
            $stmt_old->bind_param("ii", $id, $user_id);
            $stmt_old->execute();
            $result_old = $stmt_old->get_result();
            if($result_old->num_rows > 0) {
                $old_price = (int)$result_old->fetch_assoc()['harga_beli'];
                if ($old_price !== $harga) {
                    $stmt_history = $conn->prepare("INSERT INTO base_ingredients_history (ingredient_id, user_id, harga_beli) VALUES (?, ?, ?)");
                    $stmt_history->bind_param("iii", $id, $user_id, $harga);
                    $stmt_history->execute();
                    $stmt_history->close();
                }
            }
            $stmt_old->close();

            $stmt = $conn->prepare("UPDATE base_ingredients SET nama_bahan=?, kategori=?, jumlah_beli=?, satuan_beli=?, harga_beli=? WHERE id=? AND user_id=?");
            $stmt->bind_param("ssdsiii", $nama, $kategori, $jumlah, $satuan, $harga, $id, $user_id);
            $stmt->execute();
            $pesan_teks = "Data bahan berhasil diperbarui!";
        }
        $stmt->close();
        
        // REDIRECT AMAN DI SINI KARENA BELUM ADA HTML
        header("Location: manajemen_bahan.php?pesan=" . urlencode($pesan_teks));
        exit();
    }
}

// Logika Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM base_ingredients WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manajemen_bahan.php?pesan=" . urlencode("Bahan berhasil dihapus."));
    exit();
}

// Logika untuk Mode Edit
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM base_ingredients WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $bahan_edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Ambil Data untuk Tampilan
$semua_bahan_grouped = [];
$result = $conn->query("SELECT * FROM base_ingredients WHERE user_id = $user_id ORDER BY kategori, nama_bahan ASC");
while ($row = $result->fetch_assoc()) {
    $kategori = $row['kategori'];
    if (!isset($semua_bahan_grouped[$kategori])) {
        $semua_bahan_grouped[$kategori] = [];
    }
    $semua_bahan_grouped[$kategori][] = $row;
}

if (isset($_GET['pesan'])) {
    $pesan = "<div id='pesan-notifikasi' class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 max-w-lg mx-auto' role='alert'>" . htmlspecialchars($_GET['pesan']) . "</div>";
}

// --- BARU DI SINI HTML DIMULAI (Include Header) ---
// Kita set variabel theme manual karena include header.php akan kita bypass sedikit bagian atasnya
// Atau kita include header.php TETAPI kita tahu session_start di dalamnya akan di-ignore (aman)
// Namun agar style.css termuat, kita tetap butuh HTML headernya.
?>

<?php
$theme = 'light';
$stmt_theme = $conn->prepare("SELECT theme FROM user_settings WHERE user_id = ?");
$stmt_theme->bind_param("i", $user_id);
$stmt_theme->execute();
if ($row_theme = $stmt_theme->get_result()->fetch_assoc()) {
    $theme = $row_theme['theme'];
}
$stmt_theme->close();
?>
<!DOCTYPE html>
<html lang="id" class="<?php if($theme === 'dark') echo 'dark'; ?>">
<?php include __DIR__ . '/includes/header.php'; ?> 

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Bahan - Catatan Rasa Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
     <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "foreground-light": "#181411", "foreground-dark": "#f8f7f6", "subtle-light": "#f4f2f0", "subtle-dark": "#3a2e22", "placeholder-light": "#897561", "placeholder-dark": "#9d8e7e", "input-light": "#f4f2f0", "input-dark": "#3a2d21" },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-foreground-light dark:text-foreground-dark">
<div class="flex flex-col min-h-screen pb-20">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="flex-grow p-4">
        <div id="form-bahan" class="mb-8">
            <h2 class="text-xl font-bold mb-6 text-center"><?php echo $bahan_edit ? 'Edit Bahan' : 'Tambah Bahan Baru'; ?></h2>
            <?php if ($pesan) echo $pesan; ?>
            
            <form class="space-y-6 max-w-lg mx-auto" method="POST">
                 <input type="hidden" name="id" value="<?php echo $bahan_edit['id'] ?? ''; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" for="ingredient-name">Nama Bahan</label>
                        <input class="w-full h-14 bg-subtle-light dark:bg-subtle-dark border-0 rounded-lg px-4 focus:ring-2 focus:ring-primary" id="ingredient-name" name="nama_bahan" value="<?php echo htmlspecialchars($bahan_edit['nama_bahan'] ?? ''); ?>" placeholder="Tepung Terigu" type="text" required/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" for="kategori">Kategori</label>
                        <select class="w-full h-14 bg-subtle-light dark:bg-subtle-dark border-0 rounded-lg px-4 focus:ring-2 focus:ring-primary" id="kategori" name="kategori" required>
                            <option value="" disabled <?php echo !$bahan_edit ? 'selected' : ''; ?>>Pilih Kategori</option>
                            <?php foreach ($kategori_list as $kat): ?>
                                <option value="<?php echo $kat; ?>" <?php if (($bahan_edit['kategori'] ?? '') == $kat) echo 'selected'; ?>><?php echo $kat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" for="purchase-quantity">Jumlah Beli</label>
                        <input class="w-full h-14 bg-subtle-light dark:bg-subtle-dark border-0 rounded-lg px-4 focus:ring-2 focus:ring-primary" id="purchase-quantity" name="jumlah_beli" value="<?php echo htmlspecialchars($bahan_edit['jumlah_beli'] ?? ''); ?>" placeholder="1000" type="number" step="0.01" required/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" for="purchase-unit">Satuan Beli</label>
                        <input list="satuan-options" class="w-full h-14 bg-subtle-light dark:bg-subtle-dark border-0 rounded-lg px-4 focus:ring-2 focus:ring-primary" id="purchase-unit" name="satuan_beli" value="<?php echo htmlspecialchars($bahan_edit['satuan_beli'] ?? ''); ?>" placeholder="gram" required>
                        <datalist id="satuan-options">
                             <?php foreach ($satuan_list as $sat): ?>
                                <option value="<?php echo $sat; ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" for="purchase-price">Harga Beli</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-placeholder-light dark:text-placeholder-dark">Rp</span>
                        <input class="w-full h-14 bg-subtle-light dark:bg-subtle-dark border-0 rounded-lg pl-11 pr-4 focus:ring-2 focus:ring-primary" id="purchase-price" name="harga_beli" value="<?php echo htmlspecialchars($bahan_edit['harga_beli'] ?? ''); ?>" placeholder="15000" type="number" required/>
                    </div>
                </div>
                <div class="flex gap-4 pt-2">
                    <button type="submit" name="simpan" class="w-full h-12 bg-primary text-white font-bold rounded-full flex items-center justify-center text-base tracking-wide shadow-lg">
                        <?php echo $bahan_edit ? 'Update Bahan' : 'Simpan Bahan'; ?>
                    </button>
                    <?php if ($bahan_edit): ?>
                        <a href="manajemen_bahan.php" class="w-full h-12 bg-gray-300 text-gray-800 font-bold rounded-full flex items-center justify-center text-base tracking-wide">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="mt-12">
             <h2 class="text-xl font-bold mb-4 text-center">Daftar Bahan Tersimpan</h2>
             
             <div class="mb-6 max-w-3xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="searchInput" class="sr-only">Cari Bahan</label>
                    <input type="text" id="searchInput" placeholder="Ketik untuk mencari bahan..." class="w-full h-12 p-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label for="kategoriFilter" class="sr-only">Filter Kategori</label>
                    <select id="kategoriFilter" class="w-full h-12 bg-input-light dark:bg-input-dark border-0 rounded-lg px-4 focus:ring-2 focus:ring-primary">
                        <option value="semua">Tampilkan Semua Kategori</option>
                        <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?php echo htmlspecialchars($kat); ?>"><?php echo htmlspecialchars($kat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
             </div>

             <div class="max-w-3xl mx-auto space-y-6">
                <?php if (empty($semua_bahan_grouped)): ?>
                    <p class="p-4 text-center text-gray-500">Belum ada data bahan.</p>
                <?php else: ?>
                    <?php foreach ($semua_bahan_grouped as $kategori => $bahans): ?>
                    <div class="kategori-group" data-kategori="<?php echo htmlspecialchars($kategori); ?>">
                        <h3 class="kategori-title text-lg font-bold mb-3 text-primary dark:text-gray-300"><?php echo htmlspecialchars($kategori); ?></h3>
                        <div class="bg-white dark:bg-subtle-dark rounded-lg shadow overflow-hidden">
                            <table class="w-full bahan-table">
                                <tbody>
                                    <?php foreach ($bahans as $bahan): ?>
                                    <tr class="border-b border-gray-200 dark:border-gray-700 bahan-row">
                                        <td class="p-3 nama-bahan"><?php echo htmlspecialchars($bahan['nama_bahan']); ?></td>
                                        <td class="p-3 text-right text-gray-600 dark:text-gray-400">
                                            Rp <?php echo number_format($bahan['harga_beli'], 0, ',', '.'); ?> / 
                                            <?php echo rtrim(rtrim(number_format($bahan['jumlah_beli'], 2, ',', '.'), '0'), ','); ?> 
                                            <?php echo htmlspecialchars($bahan['satuan_beli']); ?>
                                        </td>
                                        <td class="p-3 flex gap-3 justify-end items-center">
                                            <button title="Lihat Histori Harga" class="history-btn text-blue-500 hover:underline" data-id="<?php echo $bahan['id']; ?>" data-nama="<?php echo htmlspecialchars($bahan['nama_bahan']); ?>">
                                                <span class="material-symbols-outlined text-base">history</span>
                                            </button>
                                            <button title="Tambah ke Daftar Belanja" class="quick-add-btn" 
                                                    data-nama="<?php echo htmlspecialchars($bahan['nama_bahan']); ?>"
                                                    data-jumlah="<?php echo htmlspecialchars($bahan['jumlah_beli']); ?>"
                                                    data-satuan="<?php echo htmlspecialchars($bahan['satuan_beli']); ?>">
                                                <span class="material-symbols-outlined text-primary text-lg">add_shopping_cart</span>
                                            </button>
                                            <a href="manajemen_bahan.php?edit=<?php echo $bahan['id']; ?>#form-bahan" class="text-primary hover:underline text-sm font-semibold">Edit</a>
                                            <a href="manajemen_bahan.php?hapus=<?php echo $bahan['id']; ?>" class="text-red-500 hover:underline text-sm font-semibold" onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
             </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>

<div id="history-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white dark:bg-subtle-dark rounded-lg shadow-xl max-w-md w-full">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 id="history-modal-title" class="font-bold text-lg">Histori Harga</h3>
            <button id="close-history-modal" class="font-bold text-xl">&times;</button>
        </div>
        <div id="history-modal-content" class="p-4 max-h-80 overflow-y-auto">
            </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const kategoriFilter = document.getElementById('kategoriFilter');
        const pesanNotifikasi = document.getElementById('pesan-notifikasi');

        if (pesanNotifikasi) {
            setTimeout(() => {
                pesanNotifikasi.style.transition = 'opacity 0.5s';
                pesanNotifikasi.style.opacity = '0';
                setTimeout(() => pesanNotifikasi.style.display = 'none', 500);
            }, 5000);
        }

        function applyFilters() {
            const searchText = searchInput.value.toLowerCase();
            const selectedKategori = kategoriFilter.value;
            document.querySelectorAll('.kategori-group').forEach(group => {
                let groupHasVisibleRow = false;
                const groupKategori = group.dataset.kategori;
                const isKategoriMatch = selectedKategori === 'semua' || groupKategori === selectedKategori;
                if (!isKategoriMatch) {
                    group.style.display = 'none';
                    return;
                }
                group.querySelectorAll('.bahan-row').forEach(row => {
                    const namaBahan = row.querySelector('.nama-bahan').textContent.toLowerCase();
                    if (namaBahan.includes(searchText)) {
                        row.style.display = '';
                        groupHasVisibleRow = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                group.style.display = groupHasVisibleRow ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', applyFilters);
        kategoriFilter.addEventListener('change', applyFilters);
        
        document.querySelectorAll('.quick-add-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const nama = this.dataset.nama;
                fetch('ajax/ajax_quick_add.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nama_bahan: nama, jumlah: this.dataset.jumlah, satuan: this.dataset.satuan })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') alert(`'${nama}' berhasil ditambahkan ke daftar belanja.`);
                    else alert('Gagal menambahkan: ' + data.message);
                })
                .catch(error => console.error('Error:', error));
            });
        });

        const historyModal = document.getElementById('history-modal');
        const historyModalTitle = document.getElementById('history-modal-title');
        const historyModalContent = document.getElementById('history-modal-content');
        const closeHistoryModal = document.getElementById('close-history-modal');

        document.querySelectorAll('.history-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const ingredientId = this.dataset.id;
                const ingredientName = this.dataset.nama;
                
                historyModalTitle.textContent = `Histori Harga: ${ingredientName}`;
                historyModalContent.innerHTML = '<p class="text-center">Memuat...</p>';
                historyModal.classList.remove('hidden');

                try {
                    const response = await fetch(`ajax/ajax_price_history.php?ingredient_id=${ingredientId}`);
                    const data = await response.json();
                    if (data.status === 'success' && data.history.length > 0) {
                        let tableHtml = '<table class="w-full text-sm">';
                        tableHtml += '<thead class="text-left bg-gray-100 dark:bg-gray-800"><tr><th class="p-2">Tanggal Update</th><th class="p-2 text-right">Harga</th></tr></thead><tbody>';
                        data.history.forEach(item => {
                            const date = new Date(item.tanggal_update).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                            const price = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.harga_beli);
                            tableHtml += `<tr class="border-t border-gray-200 dark:border-gray-700"><td class="p-2">${date}</td><td class="p-2 text-right">${price}</td></tr>`;
                        });
                        tableHtml += '</tbody></table>';
                        historyModalContent.innerHTML = tableHtml;
                    } else {
                        historyModalContent.innerHTML = '<p class="text-center text-gray-500">Belum ada histori harga untuk bahan ini.</p>';
                    }
                } catch (error) {
                    historyModalContent.innerHTML = '<p class="text-center text-red-500">Gagal memuat data.</p>';
                    console.error('Error fetching history:', error);
                }
            });
        });

        closeHistoryModal.addEventListener('click', () => historyModal.classList.add('hidden'));
        historyModal.addEventListener('click', (e) => {
            if (e.target === historyModal) historyModal.classList.add('hidden');
        });
    });
</script>
</body>
</html>
