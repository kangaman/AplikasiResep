<?php
// Mendapatkan nama file saat ini untuk menandai menu aktif
$current_page = basename($_SERVER['PHP_SELF']);

// Daftar menu navigasi lengkap
$nav_items = [
    'dashboard.php' => 'Dashboard',
    'manajemen_pesanan.php' => 'Pesanan',
    'manajemen_pelanggan.php' => 'Pelanggan', // Ditambahkan
    'manajemen_bahan.php' => 'Bahan',
    'snackbox.php' => 'Snack Box',
    'kalkulator_bahan.php' => 'Olahan',
    'laporan.php' => 'Laporan',
    'pengaturan.php' => 'Pengaturan'
];

// Menentukan judul dinamis untuk header mobile
$page_titles = [
    'dashboard.php' => 'Dashboard',
    'manajemen_pesanan.php' => 'Manajemen Pesanan',
    'manajemen_pelanggan.php' => 'Manajemen Pelanggan', // Ditambahkan
    'form_pesanan.php' => 'Formulir Pesanan',
    'detail_pesanan.php' => 'Detail Pesanan',
    'manajemen_bahan.php' => 'Manajemen Bahan',
    'form_resep.php' => 'Formulir Resep',
    'detail_resep.php' => 'Detail Resep',
    'ganti_password.php' => 'Ganti Password',
    'pengaturan.php' => 'Pengaturan',
    'laporan.php' => 'Laporan',
    'snackbox.php' => 'Kalkulator Snack Box',
    'kalkulator_bahan.php' => 'Kalkulator Olahan',
    'daftar_belanja.php' => 'Daftar Belanja',
];
$header_title = $page_titles[$current_page] ?? 'Catatan Rasa Digital';
?>

<header class="sticky top-0 z-20 flex items-center bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm p-4 justify-between">
    
    <div class="hidden md:flex items-center gap-6 flex-1">
        <a href="dashboard.php" class="text-lg font-bold text-gray-900 dark:text-white">Catatan Rasa Digital</a>
        <div class="flex items-center gap-4">
            <?php foreach ($nav_items as $url => $nama):
                $is_active = ($current_page == $url);
                $text_class = $is_active ? 'text-primary font-bold' : 'text-gray-600 dark:text-gray-300 font-medium';
            ?>
                <a href="<?php echo $url; ?>" class="text-sm <?php echo $text_class; ?> hover:text-primary"><?php echo $nama; ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="hidden md:flex items-center gap-4">
        <?php if ($current_page == 'manajemen_pesanan.php'): ?>
            <a href="form_pesanan.php" class="text-sm font-medium bg-primary text-white px-4 py-2 rounded-full hover:bg-opacity-90">+ Tambah Pesanan</a>
        <?php elseif ($current_page == 'manajemen_pelanggan.php'): ?>
            <a href="manajemen_pelanggan.php#form-pelanggan" class="text-sm font-medium bg-primary text-white px-4 py-2 rounded-full hover:bg-opacity-90">+ Tambah Pelanggan</a>
        <?php else: ?>
            <a href="form_resep.php" class="text-sm font-medium bg-primary text-white px-4 py-2 rounded-full hover:bg-opacity-90">+ Tambah Resep</a>
        <?php endif; ?>
        <a href="logout.php" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary">Logout</a>
    </div>

    <div class="md:hidden flex-1">
        <a href="logout.php" class="p-2" title="Logout"><span class="material-symbols-outlined">logout</span></a>
    </div>
    <h1 class="md:hidden text-lg font-bold text-center flex-1"><?php echo $header_title; ?></h1>
    <div class="md:hidden flex-1 flex justify-end">
        <?php if ($current_page == 'manajemen_pesanan.php'): ?>
            <a href="form_pesanan.php" class="p-2" title="Tambah Pesanan Baru">
                <span class="material-symbols-outlined">add_shopping_cart</span>
            </a>
        <?php elseif ($current_page == 'manajemen_pelanggan.php'): ?>
             <a href="manajemen_pelanggan.php#form-pelanggan" class="p-2" title="Tambah Pelanggan Baru">
                <span class="material-symbols-outlined">person_add</span>
            </a>
        <?php else: ?>
            <a href="form_resep.php" class="p-2" title="Tambah Resep Baru">
                <span class="material-symbols-outlined">add</span>
            </a>
        <?php endif; ?>
    </div>

</header>