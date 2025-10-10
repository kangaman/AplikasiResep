<?php 
include __DIR__ . '/includes/header.php'; // Menggunakan header global

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
// user_id sudah ada dari header.php

// --- PENGAMBILAN DATA UNTUK KARTU RINGKASAN ---
// 1. Bulan Ini (Hanya dari pesanan yang statusnya 'Selesai')
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');
$stmt_month = $conn->prepare(
    "SELECT 
        COUNT(id) as total_pesanan,
        SUM(total_jual) as pendapatan,
        SUM(total_jual - total_modal) as keuntungan
    FROM orders 
    WHERE user_id = ? AND status = 'Selesai' AND tanggal_pengiriman BETWEEN ? AND ?"
);
$stmt_month->bind_param("iss", $user_id, $current_month_start, $current_month_end);
$stmt_month->execute();
$summary_month = $stmt_month->get_result()->fetch_assoc();
$stmt_month->close();

// 2. 7 Hari Terakhir (Hanya dari pesanan yang statusnya 'Selesai')
$seven_days_ago = date('Y-m-d', strtotime('-6 days'));
$today = date('Y-m-d');
$stmt_week = $conn->prepare(
    "SELECT SUM(total_jual - total_modal) as keuntungan FROM orders WHERE user_id = ? AND status = 'Selesai' AND tanggal_pengiriman BETWEEN ? AND ?"
);
$stmt_week->bind_param("iss", $user_id, $seven_days_ago, $today);
$stmt_week->execute();
$summary_week = $stmt_week->get_result()->fetch_assoc();
$stmt_week->close();

// 3. Pesanan Aktif (Status 'Baru' atau 'Diproses')
$stmt_active = $conn->prepare("SELECT COUNT(id) as total FROM orders WHERE user_id = ? AND status IN ('Baru', 'Diproses')");
$stmt_active->bind_param("i", $user_id);
$stmt_active->execute();
$summary_active = $stmt_active->get_result()->fetch_assoc();
$stmt_active->close();

// --- PENGAMBILAN DATA UNTUK PRODUK TERLARIS ---
$stmt_top_products = $conn->prepare("
    SELECT nama_item, tipe_item, SUM(jumlah) as total_terjual, SUM(jumlah * harga_jual_per_item) as total_pendapatan
    FROM order_items
    WHERE order_id IN (SELECT id FROM orders WHERE user_id = ? AND status = 'Selesai')
    GROUP BY nama_item, tipe_item
    ORDER BY total_terjual DESC
    LIMIT 10
");
$stmt_top_products->bind_param("i", $user_id);
$stmt_top_products->execute();
$top_products = $stmt_top_products->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_top_products->close();

// --- PENGAMBILAN DATA UNTUK GRAFIK PENJUALAN ---
$chart_labels = [];
$chart_data = [];
$thirty_days_ago = date('Y-m-d', strtotime('-29 days'));
$stmt_chart = $conn->prepare("
    SELECT DATE(tanggal_pengiriman) as tanggal, SUM(total_jual) as total_harian
    FROM orders
    WHERE user_id = ? AND status = 'Selesai' AND tanggal_pengiriman >= ?
    GROUP BY DATE(tanggal_pengiriman)
    ORDER BY tanggal ASC
");
$stmt_chart->bind_param("is", $user_id, $thirty_days_ago);
$stmt_chart->execute();
$result_chart = $stmt_chart->get_result();
$sales_data = [];
while ($row = $result_chart->fetch_assoc()) {
    $sales_data[$row['tanggal']] = $row['total_harian'];
}
$stmt_chart->close();

// Isi data untuk 30 hari terakhir, beri nilai 0 jika tidak ada penjualan
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d M', strtotime($date));
    $chart_data[] = $sales_data[$date] ?? 0;
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis & Laporan - Catatan Rasa Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: { extend: { colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "content-light": "#181411", "content-dark": "#f8f7f6", "subtle-dark": "#3a2e22" }, fontFamily: { "display": ["Epilogue", "sans-serif"] } } }
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-content-light dark:text-content-dark">
<div class="flex flex-col min-h-screen pb-20">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="p-4 flex-grow space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-100 dark:bg-blue-900/50 p-4 rounded-xl">
                <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Pendapatan Bulan Ini</p>
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-200">Rp <?php echo number_format($summary_month['pendapatan'] ?? 0, 0, ',', '.'); ?></p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/50 p-4 rounded-xl">
                <p class="text-sm font-medium text-green-800 dark:text-green-300">Keuntungan Bulan Ini</p>
                <p class="text-2xl font-bold text-green-900 dark:text-green-200">Rp <?php echo number_format($summary_month['keuntungan'] ?? 0, 0, ',', '.'); ?></p>
            </div>
            <div class="bg-purple-100 dark:bg-purple-900/50 p-4 rounded-xl">
                <p class="text-sm font-medium text-purple-800 dark:text-purple-300">Untung 7 Hari Terakhir</p>
                <p class="text-2xl font-bold text-purple-900 dark:text-purple-200">Rp <?php echo number_format($summary_week['keuntungan'] ?? 0, 0, ',', '.'); ?></p>
            </div>
            <div class="bg-yellow-100 dark:bg-yellow-900/50 p-4 rounded-xl">
                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Pesanan Aktif</p>
                <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-200"><?php echo $summary_active['total'] ?? 0; ?></p>
            </div>
        </div>

        <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow">
            <h3 class="font-bold mb-4">Grafik Penjualan (30 Hari Terakhir)</h3>
            <div>
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow">
            <h3 class="font-bold mb-4">Produk Terlaris (Berdasarkan Jumlah Terjual)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="p-2">#</th>
                            <th class="p-2">Nama Produk</th>
                            <th class="p-2 text-center">Terjual</th>
                            <th class="p-2 text-right">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($top_products)): ?>
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-500">Belum ada data penjualan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($top_products as $index => $product): ?>
                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                <td class="p-2 font-bold"><?php echo $index + 1; ?></td>
                                <td class="p-2">
                                    <p class="font-semibold"><?php echo htmlspecialchars($product['nama_item']); ?></p>
                                    <p class="text-xs text-gray-500 capitalize"><?php echo htmlspecialchars($product['tipe_item']); ?></p>
                                </td>
                                <td class="p-2 text-center font-medium"><?php echo $product['total_terjual']; ?></td>
                                <td class="p-2 text-right font-semibold">Rp <?php echo number_format($product['total_pendapatan'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Pendapatan Harian',
                data: <?php echo json_encode($chart_data); ?>,
                backgroundColor: 'rgba(236, 127, 19, 0.2)',
                borderColor: 'rgba(236, 127, 19, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
</body>
</html>