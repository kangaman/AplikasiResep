<?php
include __DIR__ . '/config/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$pesan = '';
$bahan_edit = null;

// Definisikan daftar kategori yang akan digunakan di seluruh halaman
$kategori_list = [
    'Bahan Kering',
    'Bahan Basah',
    'Lemak & Produk Susu',
    'Pelengkap & Isian',
    'Bahan Olahan',
    'Non-Pangan & Kemasan',
    'Lain-lain'
];

// Definisikan daftar satuan yang umum digunakan
$satuan_list = [
    'gram',
    'kg',
    'ml',
    'liter',
    'buah',
    'pack',
    'sachet',
    'sdm', // sendok makan
    'sdt'  // sendok teh
];

// Logika Simpan / Update
if (isset($_POST['simpan'])) {
    $id = $_POST['id'];
    $nama = trim($_POST['nama_bahan']);
    $kategori = $_POST['kategori'];
    $jumlah = $_POST['jumlah_beli'];
    $satuan = trim($_POST['satuan_beli']);
    $harga = $_POST['harga_beli'];

    if (empty($nama) || empty($jumlah) || empty($satuan) || empty($harga) || empty($kategori)) {
        $pesan = "Semua kolom wajib diisi.";
    } else {
        if (empty($id)) {
            $stmt = $conn->prepare("INSERT INTO base_ingredients (user_id, nama_bahan, kategori, jumlah_beli, satuan_beli, harga_beli) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdsi", $user_id, $nama, $kategori, $jumlah, $satuan, $harga);
            $pesan = "Bahan baru berhasil ditambahkan!";
        } else {
            $stmt = $conn->prepare("UPDATE base_ingredients SET nama_bahan=?, kategori=?, jumlah_beli=?, satuan_beli=?, harga_beli=? WHERE id=? AND user_id=?");
            $stmt->bind_param("ssdsiii", $nama, $kategori, $jumlah, $satuan, $harga, $id, $user_id);
            $pesan = "Data bahan berhasil diperbarui!";
        }
        $stmt->execute();
        $stmt->close();
        header("Location: manajemen_bahan.php?pesan=" . urlencode($pesan));
        exit();
    }
}

// Logika Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM base_ingredients WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manajemen_bahan.php?pesan=" . urlencode("Bahan berhasil dihapus."));
    exit();
}

// Logika untuk Mode Edit
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM base_ingredients WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $bahan_edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Ambil semua bahan dan kelompokkan berdasarkan kategori
$semua_bahan_grouped = [];
$result = $conn->query("SELECT * FROM base_ingredients WHERE user_id = $user_id ORDER BY kategori, nama_bahan ASC");
while ($row = $result->fetch_assoc()) {
    $kategori = $row['kategori'];
    if (!isset($semua_bahan_grouped[$kategori])) {
        $semua_bahan_grouped[$kategori] = [];
    }
    $semua_bahan_grouped[$kategori][] = $row;
}

if (isset($_GET['pesan'])) $pesan = htmlspecialchars($_GET['pesan']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Bahan</title>
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
<div class="flex flex-col min-h-screen">
    <header class="sticky top-0 z-10 flex items-center bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm p-4 justify-between">
        <a href="dashboard.php" class="text-lg font-bold text-gray-900 dark:text-white hidden md:block">Catatan Rasa Digital</a>
        <a href="dashboard.php" class="p-2 md:hidden"><span class="material-symbols-outlined">arrow_back</span></a>
        <div class="hidden md:flex items-center gap-4">
            <a href="daftar_belanja.php" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary">Daftar Belanja</a>
            <a href="manajemen_bahan.php" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary">Harga Bahan</a>
            <a href="snackbox.php" class="text-sm font-bold text-primary">Kalkulator Snack Box</a>
	    <a href="kalkulator_bahan.php" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary">Kalkulator Olahan</a>
            <a href="ganti_password.php" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary">Ganti Password</a>
            <a href="logout.php" class="text-sm font-medium bg-red-500 text-white px-3 py-1.5 rounded-full hover:bg-red-600">Logout</a>


        </div>
        <div class="w-10 md:hidden"></div>
    </header>

    <main class="flex-grow p-4">
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-6 text-center"><?php echo $bahan_edit ? 'Edit Bahan' : 'Tambah Bahan Baru'; ?></h2>
            <?php if ($pesan && !isset($_GET['edit'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 max-w-lg mx-auto" role="alert">
                    <p><?php echo $pesan; ?></p>
                </div>
            <?php endif; ?>
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
                        <select class="w-full h-14 bg-subtle-light dark:bg-subtle-dark border-0 rounded-lg px-4 focus:ring-2 focus:ring-primary" id="purchase-unit" name="satuan_beli" required>
                            <option value="" disabled <?php echo !$bahan_edit ? 'selected' : ''; ?>>Pilih Satuan</option>
                             <?php foreach ($satuan_list as $sat): ?>
                                <option value="<?php echo $sat; ?>" <?php if (($bahan_edit['satuan_beli'] ?? '') == $sat) echo 'selected'; ?>><?php echo $sat; ?></option>
                            <?php endforeach; ?>
                        </select>
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
                                        <td class="p-3 text-right text-gray-600 dark:text-gray-400">Rp <?php echo number_format($bahan['harga_beli'], 0, ',', '.'); ?> / <?php echo rtrim(rtrim(number_format($bahan['jumlah_beli'], 2, ',', '.'), '0'), ','); ?> <?php echo htmlspecialchars($bahan['satuan_beli']); ?></td>
                                        <td class="p-3 flex gap-3 justify-end">
                                            <a href="manajemen_bahan.php?edit=<?php echo $bahan['id']; ?>" class="text-primary hover:underline text-sm font-semibold">Edit</a>
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
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const kategoriFilter = document.getElementById('kategoriFilter');

    function applyFilters() {
        const searchText = searchInput.value.toLowerCase();
        const selectedKategori = kategoriFilter.value;

        document.querySelectorAll('.kategori-group').forEach(group => {
            let groupHasVisibleRow = false;
            const groupKategori = group.dataset.kategori;

            if (selectedKategori !== 'semua' && groupKategori !== selectedKategori) {
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

            if (groupHasVisibleRow) {
                group.style.display = '';
            } else {
                group.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('keyup', applyFilters);
    kategoriFilter.addEventListener('change', applyFilters);
</script>
</body>
</html>