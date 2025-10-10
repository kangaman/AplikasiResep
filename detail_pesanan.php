<?php
session_start();
include __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = (int)$_GET['id'];
$pesan = '';

// Menampilkan notifikasi setelah update status
if (isset($_GET['status_updated'])) {
    if ($_GET['status_updated'] == '1') {
        $pesan = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>Status pesanan berhasil diperbarui!</div>";
    } else {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Gagal memperbarui status.</div>";
    }
}

// Ambil data pesanan utama
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: manajemen_pesanan.php");
    exit();
}
$order = $result->fetch_assoc();
$stmt->close();

// Ambil item-item dari pesanan ini
$items = [];
$stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

// Ambil data biaya tambahan
$expenses = [];
$stmt_expenses = $conn->prepare("SELECT * FROM order_expenses WHERE order_id = ?");
$stmt_expenses->bind_param("i", $order_id);
$stmt_expenses->execute();
$expenses = $stmt_expenses->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_expenses->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Catatan Rasa Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "content-light": "#181411", "content-dark": "#f8f7f6", "input-light": "#f4f2f0", "input-dark": "#3a2d21", "subtle-dark": "#3a2e22" },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-content-light dark:text-content-dark">
<div class="flex flex-col min-h-screen pb-20">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="max-w-2xl mx-auto p-4 space-y-6 flex-grow w-full">
        <?php if ($pesan) echo $pesan; ?>

        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Detail Pesanan #<?php echo $order['id']; ?></h1>
        </div>

        <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow">
            <div class="flex justify-between items-start mb-3">
                <h3 class="font-bold text-xl"><?php echo htmlspecialchars($order['nama_pelanggan']); ?></h3>
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
            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                <p><strong>Kontak:</strong> <?php echo htmlspecialchars($order['kontak_pelanggan'] ?: '-'); ?></p>
                <p><strong>Tgl Kirim:</strong> <?php echo date("d F Y", strtotime($order['tanggal_pengiriman'])); ?></p>
                <?php if(!empty($order['alamat_pengiriman'])): ?>
                <p><strong>Alamat:</strong> <?php echo nl2br(htmlspecialchars($order['alamat_pengiriman'])); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow">
            <h3 class="font-bold mb-4">Rincian Item</h3>
            <div class="space-y-3">
                <?php foreach ($items as $item): ?>
                <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                    <div>
                        <p class="font-semibold"><?php echo htmlspecialchars($item['nama_item']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo $item['jumlah']; ?> x Rp <?php echo number_format($item['harga_jual_per_item'], 0, ',', '.'); ?></p>
                    </div>
                    <p class="font-semibold">Rp <?php echo number_format($item['jumlah'] * $item['harga_jual_per_item'], 0, ',', '.'); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if (!empty($expenses)): ?>
        <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow">
            <h3 class="font-bold mb-4">Rincian Biaya Tambahan</h3>
            <div class="space-y-3">
                <?php foreach ($expenses as $expense): ?>
                <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2 text-sm">
                    <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($expense['deskripsi']); ?></p>
                    <p class="font-medium">Rp <?php echo number_format($expense['jumlah'], 0, ',', '.'); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow space-y-3">
            <div class="flex justify-between items-center">
                <h3 class="font-bold">Ringkasan Finansial</h3>
                <button id="recalculate-btn" class="text-sm font-semibold text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">refresh</span>
                    Hitung Ulang
                </button>
            </div>
            <div id="financial-summary-original">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Total Modal (Saat Pesan)</span>
                    <span class="font-medium">Rp <?php echo number_format($order['total_modal'], 0, ',', '.'); ?></span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Total Penjualan</span>
                    <span class="font-medium">Rp <?php echo number_format($order['total_jual'], 0, ',', '.'); ?></span>
                </div>
                <hr class="border-gray-200 dark:border-gray-700 my-2">
                <div class="flex justify-between items-center">
                    <span class="font-bold text-green-600">Keuntungan (Saat Pesan)</span>
                    <span class="font-bold text-xl text-green-600">Rp <?php echo number_format($order['total_jual'] - $order['total_modal'], 0, ',', '.'); ?></span>
                </div>
            </div>
            <div id="financial-summary-recalculated" class="hidden mt-4 pt-4 border-t border-dashed border-gray-400">
                </div>
        </div>

        <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow">
            <h3 class="font-bold mb-3">Ubah Status Pesanan</h3>
            <form action="update_status_pesanan.php" method="POST" class="flex items-center gap-4">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <select name="status" class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                    <option value="Baru" <?php if($order['status'] == 'Baru') echo 'selected'; ?>>Baru</option>
                    <option value="Diproses" <?php if($order['status'] == 'Diproses') echo 'selected'; ?>>Diproses</option>
                    <option value="Siap Diambil/Kirim" <?php if($order['status'] == 'Siap Diambil/Kirim') echo 'selected'; ?>>Siap Diambil/Kirim</option>
                    <option value="Selesai" <?php if($order['status'] == 'Selesai') echo 'selected'; ?>>Selesai</option>
                    <option value="Dibatalkan" <?php if($order['status'] == 'Dibatalkan') echo 'selected'; ?>>Dibatalkan</option>
                </select>
                <button type="submit" class="h-12 px-6 bg-primary text-white font-bold rounded-lg whitespace-nowrap">Update Status</button>
            </form>
        </div>
    </main>


</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const recalculateBtn = document.getElementById('recalculate-btn');
    const recalculatedContainer = document.getElementById('financial-summary-recalculated');
    const orderId = <?php echo $order['id']; ?>;
    const originalTotalModal = <?php echo $order['total_modal']; ?>;
    const originalTotalJual = <?php echo $order['total_jual']; ?>;

    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

    recalculateBtn.addEventListener('click', async function() {
        this.innerHTML = '<span class="text-sm">Menghitung...</span>';
        this.disabled = true;

        try {
            const response = await fetch(`ajax/ajax_recalculate_order.php?order_id=${orderId}`);
            const data = await response.json();

            if (data.status === 'success') {
                const newTotalModal = data.new_total_modal;
                const newProfit = originalTotalJual - newTotalModal;
                const diff = newTotalModal - originalTotalModal;
                
                let diffText = '';
                if (diff > 0) {
                    diffText = `<span class="text-red-500 text-xs ml-2">(+${formatRupiah(diff)})</span>`;
                } else if (diff < 0) {
                    diffText = `<span class="text-green-500 text-xs ml-2">(${formatRupiah(diff)})</span>`;
                }

                recalculatedContainer.innerHTML = `
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Total Modal (Harga Sekarang)</span>
                        <span class="font-medium">Rp ${newTotalModal.toLocaleString('id-ID')} ${diffText}</span>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="font-bold text-blue-600">Keuntungan (Harga Sekarang)</span>
                        <span class="font-bold text-xl text-blue-600">${formatRupiah(newProfit)}</span>
                    </div>
                `;
                recalculatedContainer.classList.remove('hidden');
            } else {
                alert('Gagal menghitung ulang: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi.');
        } finally {
            this.innerHTML = '<span class="material-symbols-outlined text-base">refresh</span> Hitung Ulang';
            this.disabled = false;
        }
    });
});
</script>
</body>
</html>