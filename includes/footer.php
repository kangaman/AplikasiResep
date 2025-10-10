<?php
// Mendapatkan nama file saat ini untuk menandai menu aktif
$current_page_footer = basename($_SERVER['PHP_SELF']);

// Tentukan halaman mana yang masuk ke dalam grup menu apa
$menu_groups = [
    'dashboard' => ['dashboard.php'],
    'pesanan' => ['manajemen_pesanan.php', 'form_pesanan.php', 'detail_pesanan.php'],
    'bahan' => ['manajemen_bahan.php'],
    'kalkulator' => ['kalkulator_bahan.php', 'snackbox.php'],
    'laporan' => ['laporan.php'],
    'pengaturan' => ['pengaturan.php', 'ganti_password.php']
];

function get_active_class($page_name, $group_name, $groups) {
    if (in_array($page_name, $groups[$group_name])) {
        return 'text-primary';
    }
    return 'text-gray-500 dark:text-gray-400';
}
?>
<footer class="fixed bottom-0 left-0 w-full bg-background-light dark:bg-background-dark border-t border-primary/20 dark:border-primary/30 z-30">
    <nav class="flex justify-around items-center h-20 max-w-3xl mx-auto">
        <a class="flex flex-col items-center justify-center gap-1 <?php echo get_active_class($current_page_footer, 'dashboard', $menu_groups); ?>" href="dashboard.php">
            <span class="material-symbols-outlined">home</span>
            <p class="text-xs font-medium">Dashboard</p>
        </a>
        <a class="flex flex-col items-center justify-center gap-1 <?php echo get_active_class($current_page_footer, 'pesanan', $menu_groups); ?>" href="manajemen_pesanan.php">
            <span class="material-symbols-outlined">list_alt</span>
            <p class="text-xs font-medium">Pesanan</p>
        </a>
        <a class="flex flex-col items-center justify-center gap-1 <?php echo get_active_class($current_page_footer, 'bahan', $menu_groups); ?>" href="manajemen_bahan.php">
            <span class="material-symbols-outlined">menu_book</span>
            <p class="text-xs font-medium">Bahan</p>
        </a>
        <a class="flex flex-col items-center justify-center gap-1 <?php echo get_active_class($current_page_footer, 'kalkulator', $menu_groups); ?>" href="kalkulator_bahan.php">
            <span class="material-symbols-outlined">calculate</span>
            <p class="text-xs font-medium">Kalkulator</p>
        </a>
        <a class="flex flex-col items-center justify-center gap-1 <?php echo get_active_class($current_page_footer, 'pengaturan', $menu_groups); ?>" href="pengaturan.php">
            <span class="material-symbols-outlined">settings</span>
            <p class="text-xs font-medium">Pengaturan</p>
        </a>
    </nav>
</footer>