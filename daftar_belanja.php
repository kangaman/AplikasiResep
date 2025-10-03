<?php
include __DIR__ . '/config/config.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$user_id = $_SESSION['user_id'];
// ... (logika PHP lainnya tetap sama) ...
if (isset($_GET['aksi']) && $_GET['aksi'] == 'kosongkan') {
    $conn->query("DELETE FROM shopping_list WHERE user_id = $user_id");
    header("Location: daftar_belanja.php");
    exit();
}
$sql = "SELECT sl.nama_bahan, sl.jumlah, sl.satuan, bi.harga_beli / bi.jumlah_beli as harga_per_satuan FROM shopping_list sl LEFT JOIN base_ingredients bi ON sl.nama_bahan = bi.nama_bahan AND sl.satuan = bi.satuan_beli AND bi.user_id = $user_id WHERE sl.user_id = $user_id ORDER BY sl.nama_bahan ASC";
$daftar_belanja = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
$total_biaya = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Belanja</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "foreground-light": "#181411", "foreground-dark": "#f8f7f6", "subtle-light": "#f4f2f0", "subtle-dark": "#3a2e22" },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-foreground-light dark:text-foreground-dark">
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
<main class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Daftar Belanja Anda</h2>
        <?php if (!empty($daftar_belanja)): ?>
        <a href="daftar_belanja.php?aksi=kosongkan" class="bg-red-500 text-white font-bold px-4 py-2 rounded-full text-sm" onclick="return confirm('Yakin ingin mengosongkan daftar belanja?');">Kosongkan</a>
        <?php endif; ?>
    </div>
    <div class="bg-white dark:bg-subtle-dark rounded-lg shadow-lg overflow-hidden">
        <?php if (empty($daftar_belanja)): ?>
            <p class="p-8 text-center text-gray-500">Daftar belanja kosong.</p>
        <?php else: ?>
        <table class="w-full">
             <thead class="bg-subtle-light dark:bg-gray-800">
                <tr>
                    <th class="p-3 text-left text-sm font-bold uppercase">Bahan</th>
                    <th class="p-3 text-left text-sm font-bold uppercase">Jumlah</th>
                    <th class="p-3 text-left text-sm font-bold uppercase">Estimasi Biaya</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($daftar_belanja as $item): ?>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <td class="p-3"><?php echo htmlspecialchars($item['nama_bahan']); ?></td>
                    <td class="p-3"><?php echo rtrim(rtrim(number_format($item['jumlah'], 2, ',', '.'), '0'), ','); ?> <?php echo htmlspecialchars($item['satuan']); ?></td>
                    <td class="p-3">
                        <?php
                        if ($item['harga_per_satuan']) {
                            $biaya_item = $item['jumlah'] * $item['harga_per_satuan'];
                            $total_biaya += $biaya_item;
                            echo "Rp " . number_format($biaya_item, 0, ',', '.');
                        } else { echo "-"; }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot class="font-bold bg-subtle-light dark:bg-gray-800">
                <tr>
                    <td colspan="2" class="p-3 text-right">Total Estimasi Biaya</td>
                    <td class="p-3">Rp <?php echo number_format($total_biaya, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
    </div>
</main>
</body>
</html>