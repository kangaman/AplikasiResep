<?php
// Menggunakan header global, yang sudah berisi session_start() dan config.php
include __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
// Variabel $user_id sudah ada dari header.php

$pesan = '';
// Menampilkan notifikasi setelah aksi
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sukses_hapus') {
        $pesan = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>Pesanan berhasil dihapus.</div>";
    }
    if ($_GET['status'] == 'sukses_duplikat') {
        $pesan = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>Pesanan berhasil diduplikasi! Anda dialihkan ke form edit.</div>";
    }
}


// Ambil semua data pesanan untuk pengguna ini
$orders = [];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY tanggal_pengiriman DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $orders = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Catatan Rasa Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "content-light": "#181411", "content-dark": "#f8f7f6", "subtle-light": "#f4f2f0", "subtle-dark": "#3a2e22" },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-content-light dark:text-content-dark">
<div class="flex flex-col min-h-screen pb-20">
    
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="p-4 flex-grow">
        <?php if ($pesan) echo $pesan; ?>
        <div class="space-y-4">
            <?php if (empty($orders)): ?>
                <div class="text-center py-12">
                    <p class="text-gray-500">Belum ada pesanan yang dicatat.</p>
                    <a href="form_pesanan.php" class="mt-4 inline-block px-6 py-2 bg-primary text-white font-bold rounded-full">Catat Pesanan Baru</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-lg"><?php echo htmlspecialchars($order['nama_pelanggan']); ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Pengiriman: <?php echo date("d M Y", strtotime($order['tanggal_pengiriman'])); ?></p>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full 
                            <?php 
                                switch($order['status']) {
                                    case 'Baru': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'Selesai': echo 'bg-green-100 text-green-800'; break;
                                    case 'Dibatalkan': echo 'bg-red-100 text-red-800'; break;
                                    default: echo 'bg-yellow-100 text-yellow-800';
                                }
                            ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </div>
                    <hr class="my-3 border-gray-200 dark:border-gray-700">
                    <div class="space-y-2 text-sm mb-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Total Penjualan</span>
                            <span class="font-medium">Rp <?php echo number_format($order['total_jual'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-bold text-green-600 dark:text-green-400">Estimasi Keuntungan</span>
                            <span class="font-bold text-green-600 dark:text-green-400">Rp <?php echo number_format($order['total_jual'] - $order['total_modal'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-200 dark:border-gray-700 pt-3">
                        <form action="hapus_pesanan.php" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus pesanan untuk <?php echo htmlspecialchars(addslashes($order['nama_pelanggan'])); ?>? Tindakan ini tidak dapat dibatalkan.');">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button type="submit" class="text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                        </form>
                        <div class="flex items-center gap-4">
                            <a href="duplikat_pesanan.php?id=<?php echo $order['id']; ?>" class="text-sm font-semibold text-blue-500 hover:underline">Duplikat</a>
                            <a href="detail_pesanan.php?id=<?php echo $order['id']; ?>" class="text-sm font-semibold text-primary hover:underline">Detail & Edit</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>