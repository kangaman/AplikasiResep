<?php 
include __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $default_margin = (float)$_POST['default_margin'];
    $fixed_cost = (int)$_POST['fixed_cost'];
    $default_overhead = (float)$_POST['default_overhead']; // Data baru
    $theme = $_POST['theme'] === 'dark' ? 'dark' : 'light';

    $stmt_check = $conn->prepare("SELECT user_id FROM user_settings WHERE user_id = ?");
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE user_settings SET default_margin = ?, fixed_cost = ?, theme = ?, default_overhead = ? WHERE user_id = ?");
        $stmt->bind_param("disdi", $default_margin, $fixed_cost, $theme, $default_overhead, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO user_settings (user_id, default_margin, fixed_cost, theme, default_overhead) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idisd", $user_id, $default_margin, $fixed_cost, $theme, $default_overhead);
    }
    
    if ($stmt->execute()) {
        $pesan = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6' role='alert'>Pengaturan berhasil disimpan! Halaman akan dimuat ulang...</div>";
        echo '<meta http-equiv="refresh" content="2">';
    } else {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6' role='alert'>Gagal menyimpan pengaturan.</div>";
    }
    $stmt->close();
}

$stmt_get = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt_get->bind_param("i", $user_id);
$stmt_get->execute();
$current_settings = $stmt_get->get_result()->fetch_assoc();
$stmt_get->close();

$default_margin = $current_settings['default_margin'] ?? 100.00;
$fixed_cost = $current_settings['fixed_cost'] ?? 0;
$default_overhead = $current_settings['default_overhead'] ?? 5.00; // Nilai default 5% jika belum diatur
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Catatan Rasa Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: { extend: { colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "content-light": "#181411", "content-dark": "#f8f7f6", "input-light": "#f4f2f0", "input-dark": "#3a2d21", "subtle-dark": "#3a2e22" }, fontFamily: { "display": ["Epilogue", "sans-serif"] } } }
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-content-light dark:text-content-dark">
<div class="flex flex-col min-h-screen pb-20">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="max-w-2xl mx-auto p-4 flex-grow w-full">
        <?php if ($pesan) echo $pesan; ?>
        <form method="POST" class="space-y-8">
            <div class="bg-white dark:bg-subtle-dark p-6 rounded-lg shadow">
                <h3 class="font-bold text-lg mb-4">Pengaturan Keuangan</h3>
                <div class="space-y-4">
                    <div>
                        <label for="default_margin" class="block text-sm font-medium mb-1">Margin Keuntungan Default (%)</label>
                        <input type="number" step="0.01" id="default_margin" name="default_margin" value="<?php echo htmlspecialchars($default_margin); ?>" class="w-full h-12 p-3 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                        <p class="text-xs text-gray-500 mt-1">Angka ini akan terisi otomatis saat Anda menghitung harga jual.</p>
                    </div>
                    
                    <div>
                        <label for="default_overhead" class="block text-sm font-medium mb-1">Alokasi Biaya Overhead (%)</label>
                        <input type="number" step="0.01" id="default_overhead" name="default_overhead" value="<?php echo htmlspecialchars($default_overhead); ?>" class="w-full h-12 p-3 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                        <p class="text-xs text-gray-500 mt-1">Persentase dari total biaya bahan untuk biaya tak terduga (gas, listrik, dll). Akan ditambahkan ke HPP.</p>
                    </div>

                    <div>
                        <label for="fixed_cost" class="block text-sm font-medium mb-1">Biaya Operasional Tetap / Bulan (Rp)</label>
                        <input type="number" id="fixed_cost" name="fixed_cost" value="<?php echo htmlspecialchars($fixed_cost); ?>" class="w-full h-12 p-3 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                        <p class="text-xs text-gray-500 mt-1">Contoh: Gaji, sewa. Akan digunakan di halaman Laporan.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-subtle-dark p-6 rounded-lg shadow">
                <h3 class="font-bold text-lg mb-4">Pengaturan Tampilan</h3>
                <div>
                    <label for="theme" class="block text-sm font-medium mb-1">Tema Aplikasi</label>
                    <select id="theme" name="theme" class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                        <option value="light" <?php if ($theme === 'light') echo 'selected'; ?>>Terang (Light)</option>
                        <option value="dark" <?php if ($theme === 'dark') echo 'selected'; ?>>Gelap (Dark)</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full h-12 px-5 rounded-full bg-primary text-white font-bold tracking-wide shadow-lg hover:bg-opacity-90">
                Simpan Pengaturan
            </button>
        </form>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
</body>
</html>