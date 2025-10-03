<?php
session_start(); // Pastikan session dimulai di awal
include __DIR__ . '/config/config.php';

// Cek sesi login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$resep_list = [];
$stmt = $conn->prepare("SELECT id, nama_kue, gambar FROM resep WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $resep_list = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
$total_resep = count($resep_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Resep - Catatan Rasa Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "content-light": "#181411", "content-dark": "#f8f7f6" },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-content-light dark:text-content-dark">
<div class="relative flex min-h-screen w-full flex-col justify-between pb-20">
    <div class="flex-grow">
        <header class="sticky top-0 z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm">
            <div class="flex items-center p-4 justify-between">
                <div class="w-12">
                     <a href="logout.php" class="flex items-center justify-center h-10 w-10 rounded-full text-gray-900 dark:text-white">
                        <span class="material-symbols-outlined">logout</span>
                    </a>
                </div>
                <h1 class="text-lg font-bold flex-1 text-center text-gray-900 dark:text-white">Resep Kue</h1>
                <div class="flex w-12 items-center justify-end">
                    <a href="form_resep.php" class="flex items-center justify-center h-10 w-10 rounded-full text-gray-900 dark:text-white">
                        <span class="material-symbols-outlined">add</span>
                    </a>
                </div>
            </div>
        </header>

        <main class="p-4">
            <div class="grid grid-cols-1 gap-4">
                <div class="flex flex-col gap-2 rounded-xl bg-primary/10 dark:bg-primary/20 p-6 shadow-sm">
                    <p class="text-base font-medium text-gray-600 dark:text-gray-300">Total Resep</p>
                    <p class="text-4xl font-bold text-gray-900 dark:text-white"><?php echo $total_resep; ?></p>
                </div>
            </div>
            <div class="mt-4">
                <input type="text" id="search-input" placeholder="Ketik untuk mencari resep..." class="w-full h-12 p-4 rounded-lg bg-gray-200 dark:bg-gray-800 border-none focus:ring-2 focus:ring-primary placeholder:text-gray-500 dark:placeholder:text-gray-400 text-content-light dark:text-content-dark">
            </div>

            <section class="mt-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white px-1 pb-3">Semua Resep</h2>
                <div class="space-y-4" id="resep-list-container">
                    <?php if (!empty($resep_list)): ?>
                        <?php foreach ($resep_list as $resep): ?>
                            <a href="detail_resep.php?id=<?php echo $resep['id']; ?>" class="resep-item flex items-center justify-between gap-4 rounded-xl bg-gray-100 dark:bg-primary/20 p-4">
                                <div class="flex flex-col gap-1 flex-1">
                                    <p class="nama-kue text-base font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($resep['nama_kue']); ?></p>
                                    <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Lihat detail resep</p>
                                </div>
                                <?php
                                $gambar_url = !empty($resep['gambar']) ? 'uploads/' . htmlspecialchars($resep['gambar']) : 'https://api.dicebear.com/8.x/initials/svg?seed=' . urlencode($resep['nama_kue']);
                                ?>
                                <div class="w-24 h-24 bg-center bg-no-repeat bg-cover rounded-lg flex-shrink-0" style='background-image: url("<?php echo $gambar_url; ?>");'></div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p id="no-resep-message" class="text-center text-gray-500 dark:text-gray-400 mt-8">Anda belum memiliki resep. Silakan <a href="form_resep.php" class="text-primary font-bold">tambahkan resep baru</a>!</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <footer class="fixed bottom-0 left-0 w-full bg-background-light dark:bg-background-dark border-t border-primary/20 dark:border-primary/30">
        <nav class="flex justify-around items-center h-20 max-w-3xl mx-auto">
            <a class="flex flex-col items-center justify-center gap-1 text-primary" href="dashboard.php">
                <span class="material-symbols-outlined">home</span>
                <p class="text-xs font-medium">Dashboard</p>
            </a>
            <a class="flex flex-col items-center justify-center gap-1 text-gray-500 dark:text-gray-400" href="manajemen_bahan.php">
                <span class="material-symbols-outlined">menu_book</span>
                <p class="text-xs font-medium">Bahan</p>
            </a>
            
            <a class="flex flex-col items-center justify-center gap-1 text-gray-500 dark:text-gray-400" href="snackbox.php">
                <span class="material-symbols-outlined">calculate</span>
                <p class="text-xs font-medium">Kalkulator SnackBox</p>
            </a>
            
            <a class="flex flex-col items-center justify-center gap-1 text-gray-500 dark:text-gray-400" href="kalkulator_bahan.php">
                <span class="material-symbols-outlined">calculate</span>
                <p class="text-xs font-medium">Kalkulator Olahan</p>
            </a>
            </nav>
    </footer>
</div>

<script>
// PENCARIAN SISI KLIEN (LEBIH SEDERHANA & CEPAT)
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const resepContainer = document.getElementById('resep-list-container');
    const allResepItems = resepContainer.querySelectorAll('.resep-item');
    const noResepMessage = document.getElementById('no-resep-message');

    // Cek jika ada pesan "tidak ada resep"
    const originalNoResepMessage = noResepMessage ? noResepMessage.innerHTML : '';
    
    searchInput.addEventListener('input', function () {
        const query = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        allResepItems.forEach(item => {
            const namaKue = item.querySelector('.nama-kue').textContent.toLowerCase();
            if (namaKue.includes(query)) {
                item.style.display = 'flex'; // Tampilkan item
                visibleCount++;
            } else {
                item.style.display = 'none'; // Sembunyikan item
            }
        });

        // Tampilkan pesan yang sesuai
        if (noResepMessage) {
            if (allResepItems.length === 0) {
                 // Jika dari awal memang tidak ada resep
                 noResepMessage.innerHTML = originalNoResepMessage;
                 noResepMessage.style.display = 'block';
            } else if (visibleCount === 0) {
                // Jika ada resep tapi tidak ada yang cocok dengan pencarian
                noResepMessage.textContent = 'Tidak ada resep yang ditemukan.';
                noResepMessage.style.display = 'block';
            } else {
                // Jika ada hasil
                noResepMessage.style.display = 'none';
            }
        }
    });
});
</script>
</body>
</html>