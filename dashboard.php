<?php
session_start();
include __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- PENGAMBILAN DATA BARU UNTUK RINGKASAN BISNIS ---
$current_month = date('Y-m');
$stmt_summary = $conn->prepare(
    "SELECT 
        COUNT(id) as total_pesanan_bulan_ini,
        SUM(total_jual) as pendapatan_bulan_ini,
        SUM(total_jual - total_modal) as keuntungan_bulan_ini,
        (SELECT COUNT(id) FROM orders WHERE user_id = ? AND status IN ('Baru', 'Diproses')) as pesanan_aktif
    FROM orders 
    WHERE user_id = ? AND DATE_FORMAT(tanggal_pengiriman, '%Y-%m') = ?"
);
$stmt_summary->bind_param("iis", $user_id, $user_id, $current_month);
$stmt_summary->execute();
$summary = $stmt_summary->get_result()->fetch_assoc();
$stmt_summary->close();

// --- PENGAMBILAN DATA RESEP ---
$resep_list = [];
$stmt_resep = $conn->prepare("
    SELECT r.id, r.nama_kue, r.gambar, r.kategori,
           (SELECT SUM((i.jumlah / b.jumlah_beli) * b.harga_beli) / r.porsi_default
            FROM ingredients i
            JOIN base_ingredients b ON i.nama_bahan = b.nama_bahan AND b.user_id = r.user_id
            WHERE i.resep_id = r.id) as hpp_per_porsi
    FROM resep r 
    WHERE r.user_id = ? 
    ORDER BY r.id DESC
");
$stmt_resep->bind_param("i", $user_id);
$stmt_resep->execute();
$result = $stmt_resep->get_result();
if ($result) {
    $resep_list = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt_resep->close();
$total_resep = count($resep_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Catatan Rasa Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: { extend: { colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "content-light": "#181411", "content-dark": "#f8f7f6" }, fontFamily: { "display": ["Epilogue", "sans-serif"] } } }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .view-btn.active { color: #ec7f13; }
        .view-btn.active .material-symbols-outlined { font-variation-settings: 'FILL' 1; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-content-light dark:text-content-dark">
<div class="relative flex min-h-screen w-full flex-col justify-between pb-20">
    <div class="flex-grow">
        
        <?php include __DIR__ . '/includes/navigation.php'; ?>

        <main class="p-4">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-100 dark:bg-blue-900/50 p-4 rounded-xl">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Pendapatan Bulan Ini</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-200">Rp <?php echo number_format($summary['pendapatan_bulan_ini'] ?? 0, 0, ',', '.'); ?></p>
                </div>
                <div class="bg-green-100 dark:bg-green-900/50 p-4 rounded-xl">
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">Keuntungan Bulan Ini</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-200">Rp <?php echo number_format($summary['keuntungan_bulan_ini'] ?? 0, 0, ',', '.'); ?></p>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900/50 p-4 rounded-xl">
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Pesanan Aktif</p>
                    <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-200"><?php echo $summary['pesanan_aktif'] ?? 0; ?></p>
                </div>
                 <div class="bg-primary/10 dark:bg-primary/20 p-4 rounded-xl">
                    <p class="text-sm font-medium text-primary/80 dark:text-primary/70">Total Resep</p>
                    <p class="text-2xl font-bold text-primary dark:text-primary/90"><?php echo $total_resep; ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" id="search-input" placeholder="Ketik untuk mencari resep..." class="w-full h-12 p-4 rounded-lg bg-gray-200 dark:bg-gray-800 border-none focus:ring-2 focus:ring-primary">
                <select id="kategori-filter" class="w-full h-12 p-3 rounded-lg bg-gray-200 dark:bg-gray-800 border-none focus:ring-2 focus:ring-primary">
                    <option value="semua">Tampilkan Semua Kategori</option>
                    <?php 
                    $kategori_tersedia = array_unique(array_column($resep_list, 'kategori'));
                    sort($kategori_tersedia);
                    foreach ($kategori_tersedia as $kat): if(!empty($kat)):
                    ?>
                        <option value="<?php echo htmlspecialchars($kat); ?>"><?php echo htmlspecialchars($kat); ?></option>
                    <?php endif; endforeach; ?>
                </select>
            </div>

            <section class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Semua Resep</h2>
                    <div class="flex items-center gap-4">
                        <select id="sort-select" class="h-10 text-sm bg-gray-200 dark:bg-gray-800 border-none rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="terbaru">Urutkan: Terbaru</option>
                            <option value="terlama">Urutkan: Terlama</option>
                            <option value="a-z">Urutkan: Nama A-Z</option>
                            <option value="z-a">Urutkan: Nama Z-A</option>
                        </select>
                        <div class="flex items-center gap-2">
                            <button id="view-list-btn" class="view-btn active" title="Tampilan Daftar"><span class="material-symbols-outlined">view_list</span></button>
                            <button id="view-grid-btn" class="view-btn" title="Tampilan Grid"><span class="material-symbols-outlined">grid_view</span></button>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-3" id="resep-list-container">
                    <?php if (!empty($resep_list)): ?>
                        <?php foreach ($resep_list as $resep): ?>
                            <a href="detail_resep.php?id=<?php echo $resep['id']; ?>" 
                               class="resep-item flex items-center gap-4 rounded-xl bg-gray-100 dark:bg-gray-800 p-3" 
                               data-kategori="<?php echo htmlspecialchars($resep['kategori']); ?>"
                               data-id="<?php echo $resep['id']; ?>">
                                <?php $gambar_url = !empty($resep['gambar']) ? 'uploads/' . htmlspecialchars($resep['gambar']) : 'https://api.dicebear.com/8.x/initials/svg?seed=' . urlencode($resep['nama_kue']); ?>
                                <div class="resep-img w-16 h-16 bg-center bg-no-repeat bg-cover rounded-lg flex-shrink-0" style='background-image: url("<?php echo $gambar_url; ?>");'></div>
                                <div class="resep-info flex-1">
                                    <p class="nama-kue text-base font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($resep['nama_kue']); ?></p>
                                    <p class="resep-subtitle text-sm font-normal text-gray-500 dark:text-gray-400">Lihat detail resep</p>
                                </div>
                                <div class="resep-hpp text-right">
                                    <p class="text-xs text-gray-500">Modal/Porsi</p>
                                    <p class="font-bold text-primary text-sm">Rp <?php echo number_format($resep['hpp_per_porsi'] ?? 0, 0, ',', '.'); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (empty($resep_list)): ?>
                    <p id="no-resep-message" class="text-center text-gray-500 dark:text-gray-400 mt-8">Anda belum memiliki resep. Silakan <a href="form_resep.php" class="text-primary font-bold">tambahkan resep baru</a>!</p>
                <?php else: ?>
                    <p id="no-resep-message" class="hidden text-center text-gray-500 dark:text-gray-400 mt-8"></p>
                <?php endif; ?>

            </section>
        </main>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const kategoriFilter = document.getElementById('kategori-filter');
    const resepContainer = document.getElementById('resep-list-container');
    const noResepMessage = document.getElementById('no-resep-message');
    const sortSelect = document.getElementById('sort-select');
    const viewListBtn = document.getElementById('view-list-btn');
    const viewGridBtn = document.getElementById('view-grid-btn');

    let resepItems = Array.from(resepContainer.querySelectorAll('.resep-item')).map(el => {
        return {
            id: parseInt(el.dataset.id),
            nama: el.querySelector('.nama-kue').textContent.toLowerCase(),
            element: el
        };
    });

    function applyFiltersAndSort() {
        const searchQuery = searchInput.value.toLowerCase().trim();
        const selectedKategori = kategoriFilter.value;
        let visibleItems = [];

        resepItems.forEach(item => {
            const isSearchMatch = item.nama.includes(searchQuery);
            const isKategoriMatch = (selectedKategori === 'semua' || item.element.dataset.kategori === selectedKategori);

            if (isSearchMatch && isKategoriMatch) {
                visibleItems.push(item);
            }
        });

        const sortValue = sortSelect.value;
        if (sortValue === 'terbaru') visibleItems.sort((a, b) => b.id - a.id);
        else if (sortValue === 'terlama') visibleItems.sort((a, b) => a.id - b.id);
        else if (sortValue === 'a-z') visibleItems.sort((a, b) => a.nama.localeCompare(b.nama));
        else if (sortValue === 'z-a') visibleItems.sort((a, b) => b.nama.localeCompare(a.nama));
        
        resepContainer.innerHTML = '';
        if (visibleItems.length > 0) {
            visibleItems.forEach(item => resepContainer.appendChild(item.element));
        }
        
        if (noResepMessage) {
            noResepMessage.classList.toggle('hidden', visibleItems.length > 0 || resepItems.length === 0);
            if (visibleItems.length === 0 && resepItems.length > 0) {
                 noResepMessage.textContent = 'Tidak ada resep yang ditemukan.';
            }
        }
    }

    // ## PERUBAHAN UTAMA UNTUK GRID LEBIH KECIL ADA DI SINI ##
    function setView(view) {
        resepContainer.className = ''; // Hapus semua kelas layout
        
        if (view === 'grid') {
            viewGridBtn.classList.add('active');
            viewListBtn.classList.remove('active');
            // Terapkan kelas grid yang lebih rapat: 3 kolom di HP, 4 di tablet, 5 di desktop
            resepContainer.classList.add('grid', 'grid-cols-3', 'md:grid-cols-4', 'lg:grid-cols-5', 'gap-3');
            resepItems.forEach(({ element }) => {
                element.className = 'resep-item group flex flex-col rounded-lg bg-gray-100 dark:bg-gray-800 p-2 shadow-sm hover:shadow-md transition';
                // Ukuran gambar lebih kecil dan berbentuk kotak
                element.querySelector('.resep-img').className = 'resep-img w-full aspect-square bg-center bg-no-repeat bg-cover rounded-md flex-shrink-0';
                // Sembunyikan HPP dan subjudul di tampilan grid yang kecil
                element.querySelector('.resep-info').className = 'resep-info flex flex-col px-1 pt-2';
                element.querySelector('.nama-kue').className = 'nama-kue text-xs font-bold text-gray-800 dark:text-white group-hover:text-primary leading-tight';
                element.querySelector('.resep-subtitle').classList.add('hidden');
                element.querySelector('.resep-hpp').classList.add('hidden');
            });
            localStorage.setItem('resepView', 'grid');
        } else { // 'list'
            viewListBtn.classList.add('active');
            viewGridBtn.classList.remove('active');
            resepContainer.classList.add('space-y-3');
            resepItems.forEach(({ element }) => {
                element.className = 'resep-item flex items-center gap-4 rounded-xl bg-gray-100 dark:bg-gray-800 p-3 shadow-sm hover:shadow-md transition';
                element.querySelector('.resep-img').className = 'resep-img w-16 h-16 bg-center bg-no-repeat bg-cover rounded-lg flex-shrink-0';
                element.querySelector('.resep-info').className = 'resep-info flex-1';
                element.querySelector('.nama-kue').className = 'nama-kue text-base font-bold text-gray-900 dark:text-white';
                // Tampilkan kembali subjudul dan HPP
                element.querySelector('.resep-subtitle').classList.remove('hidden');
                element.querySelector('.resep-hpp').classList.remove('hidden');
            });
            localStorage.setItem('resepView', 'list');
        }
        applyFiltersAndSort();
    }

    searchInput.addEventListener('input', applyFiltersAndSort);
    kategoriFilter.addEventListener('change', applyFiltersAndSort);
    sortSelect.addEventListener('change', applyFiltersAndSort);
    viewListBtn.addEventListener('click', () => setView('list'));
    viewGridBtn.addEventListener('click', () => setView('grid'));

    const savedView = localStorage.getItem('resepView');
    setView(savedView === 'grid' ? 'grid' : 'list');
});
</script>
</body>
</html>