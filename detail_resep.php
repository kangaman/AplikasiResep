<?php
// ========================================================================
// 1. INISIALISASI & LOGIKA (SEBELUM HTML)
// ========================================================================

// Mulai sesi dengan pengecekan aman
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/config/config.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Validasi ID Resep
$resep_id = $_GET['id'] ?? null;
if (!$resep_id) {
    header("Location: dashboard.php");
    exit();
}

// Ambil Pengaturan (Overhead & Margin) untuk Kalkulator JS nanti
$stmt_settings = $conn->prepare("SELECT default_margin, default_overhead FROM user_settings WHERE user_id = ?");
$stmt_settings->bind_param("i", $user_id);
$stmt_settings->execute();
$settings_result = $stmt_settings->get_result();
$settings = $settings_result->fetch_assoc();
$default_margin = $settings['default_margin'] ?? 100;
$default_overhead = $settings['default_overhead'] ?? 5; 
$stmt_settings->close();

// ------------------------------------------------------------------------
// LOGIKA POST: TAMBAH KE DAFTAR BELANJA
// ------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_ke_belanjaan'])) {
    $porsi_dihitung = (int) $_POST['porsi_dihitung'];
    
    // Ambil porsi default resep
    $resep_temp_stmt = $conn->prepare("SELECT porsi_default FROM resep WHERE id = ?");
    $resep_temp_stmt->bind_param("i", $resep_id);
    $resep_temp_stmt->execute();
    $porsi_default_result = $resep_temp_stmt->get_result();
    $porsi_default = ($porsi_default_result->num_rows > 0) ? (int) $porsi_default_result->fetch_assoc()['porsi_default'] : 1;
    $resep_temp_stmt->close();

    if ($porsi_default > 0) {
        // Ambil semua bahan
        $ing_stmt = $conn->prepare("SELECT * FROM ingredients WHERE resep_id = ?");
        $ing_stmt->bind_param("i", $resep_id);
        $ing_stmt->execute();
        $result_ing = $ing_stmt->get_result();
        
        // [OPTIMASI] Siapkan statement di luar loop agar lebih cepat
        $cek_stmt = $conn->prepare("SELECT id, jumlah FROM shopping_list WHERE user_id = ? AND nama_bahan = ? AND satuan = ?");
        $update_stmt = $conn->prepare("UPDATE shopping_list SET jumlah = ? WHERE id = ?");
        $insert_stmt = $conn->prepare("INSERT INTO shopping_list (user_id, nama_bahan, jumlah, satuan) VALUES (?, ?, ?, ?)");

        while ($ing = $result_ing->fetch_assoc()) {
            // Hitung kebutuhan bahan sesuai porsi target
            $jumlah_baru = ($ing['jumlah'] / $porsi_default) * $porsi_dihitung;
            
            // Cek apakah item sudah ada di daftar belanja
            $cek_stmt->bind_param("iss", $user_id, $ing['nama_bahan'], $ing['satuan']);
            $cek_stmt->execute();
            $item_ada = $cek_stmt->get_result()->fetch_assoc();

            if ($item_ada) {
                // Update jumlah
                $jumlah_total = $item_ada['jumlah'] + $jumlah_baru;
                $update_stmt->bind_param("di", $jumlah_total, $item_ada['id']);
                $update_stmt->execute();
            } else {
                // Insert baru
                $insert_stmt->bind_param("isds", $user_id, $ing['nama_bahan'], $jumlah_baru, $ing['satuan']);
                $insert_stmt->execute();
            }
        }
        
        // Tutup semua statement
        $ing_stmt->close();
        $cek_stmt->close();
        $update_stmt->close();
        $insert_stmt->close();

        $_SESSION['pesan_sukses'] = "Bahan berhasil ditambahkan ke daftar belanja!";
    } else {
        $_SESSION['pesan_error'] = "Porsi default resep adalah 0, tidak dapat menghitung bahan.";
    }
    
    // Redirect aman karena belum ada HTML
    header("Location: detail_resep.php?id=" . $resep_id);
    exit();
}

// ------------------------------------------------------------------------
// AMBIL DATA UNTUK TAMPILAN
// ------------------------------------------------------------------------

// 1. Ambil Data Resep Utama
$stmt = $conn->prepare("SELECT * FROM resep WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $resep_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}
$resep = $result->fetch_assoc();
$stmt->close();

// 2. Ambil Bahan-bahan Resep
$ingredients = [];
$ing_stmt = $conn->prepare("SELECT * FROM ingredients WHERE resep_id = ?");
$ing_stmt->bind_param("i", $resep_id);
$ing_stmt->execute();
$res_ing = $ing_stmt->get_result();
while ($row = $res_ing->fetch_assoc()) {
    $ingredients[] = $row;
}
$ing_stmt->close();

// 3. Ambil Harga Bahan Dasar (untuk kalkulasi real-time)
$base_ingredients_data = [];
$stmt_base = $conn->prepare("SELECT nama_bahan as nama, jumlah_beli as jumlah, satuan_beli as satuan, harga_beli as harga FROM base_ingredients WHERE user_id = ?");
$stmt_base->bind_param("i", $user_id);
$stmt_base->execute();
$res_base = $stmt_base->get_result();
if ($res_base) {
    while ($row = $res_base->fetch_assoc()) {
        $base_ingredients_data[] = $row;
    }
}
$stmt_base->close();

// Helper Function: YouTube Embed
function get_youtube_embed_url($url) {
    $regex = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    if (preg_match($regex, $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    return '';
}

// ========================================================================
// 2. TAMPILAN HTML (MULAI DARI SINI)
// ========================================================================
include __DIR__ . '/includes/header.php'; // Header sekarang aman di-include
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Resep: <?php echo htmlspecialchars($resep['nama_kue']); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "content-light": "#181411", "content-dark": "#f8f7f6", "subtle-light": "#897561", "subtle-dark": "#a19183", "border-light": "#e6e0db", "border-dark": "#392d21" },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="relative flex min-h-screen w-full flex-col justify-between overflow-x-hidden pb-20">
    <div>
        <?php include __DIR__ . '/includes/navigation.php'; ?>

        <main class="max-w-4xl mx-auto p-6">
            <h1 class="text-content-light dark:text-content-dark text-4xl font-bold leading-tight tracking-tight mb-2"><?php echo htmlspecialchars($resep['nama_kue']); ?></h1>
            
            <div class="flex items-center gap-4 mb-6">
                <a href="form_resep.php?id=<?php echo $resep['id']; ?>" class="px-4 py-2 text-sm font-bold bg-primary/20 text-primary rounded-full">Edit Resep</a>
                <a href="duplikat_resep.php?id=<?php echo $resep['id']; ?>" class="px-4 py-2 text-sm font-bold bg-blue-500/20 text-blue-500 rounded-full">Duplikat</a>
                <form action="hapus_resep.php" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus resep ini?');" class="m-0">
                    <input type="hidden" name="id" value="<?php echo $resep['id']; ?>">
                    <button type="submit" class="px-4 py-2 text-sm font-bold bg-red-500/20 text-red-500 rounded-full">Hapus Resep</button>
                </form>
            </div>

            <?php if (isset($_SESSION['pesan_sukses'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['pesan_sukses']; unset($_SESSION['pesan_sukses']); ?></span>
                </div>
            <?php endif; ?>
             <?php if (isset($_SESSION['pesan_error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['pesan_error']; unset($_SESSION['pesan_error']); ?></span>
                </div>
            <?php endif; ?>

            <section class="mb-8 p-4 rounded-lg bg-primary/10 dark:bg-primary/20 flex flex-wrap items-center justify-between">
                 <div class="w-full md:w-auto mb-4 md:mb-0">
                     <label for="portion-input" class="font-bold text-primary text-lg">Hitung Ulang Porsi</label>
                 </div>
                 <div class="flex items-center gap-4">
                      <input type="number" id="portion-input" value="<?php echo htmlspecialchars($resep['porsi_default']); ?>" min="1" class="w-20 text-center bg-transparent font-bold text-lg border-b-2 border-primary/50 focus:border-primary focus:ring-0">
                      <span class="text-subtle-light dark:text-subtle-dark font-medium">Porsi</span>
                 </div>
                 <div id="total-cost-container" class="w-full md:w-auto text-right mt-4 md:mt-0">
                     <p class="text-sm text-subtle-light dark:text-subtle-dark">Total Biaya Bahan</p>
                     <span class="text-lg font-bold text-content-light dark:text-content-dark" id="total-bahan-cost">Rp 0</span>
                     <p class="text-xs text-subtle-light dark:text-subtle-dark mt-1">Overhead (<span id="overhead-percent-display">0</span>%)</p>
                     <span class="text-sm font-medium text-content-light dark:text-content-dark" id="total-overhead-cost">Rp 0</span>
                 </div>
            </section>
            
            <section class="mb-8 p-4 rounded-lg bg-gray-200 dark:bg-gray-800">
                <h3 class="font-bold text-lg mb-4 text-content-light dark:text-content-dark">Kalkulator Harga Jual</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-end">
                    <div>
                        <label for="margin-input" class="text-sm font-medium text-subtle-light dark:text-subtle-dark">Margin Keuntungan</label>
                        <div class="flex items-center mt-1">
                            <input type="number" id="margin-input" value="<?php echo htmlspecialchars($default_margin); ?>" class="w-full text-lg bg-transparent border-b-2 border-gray-400 focus:border-primary focus:ring-0 p-1">
                            <div class="flex border-2 border-primary rounded-lg ml-2">
                                <button id="margin-type-percent" class="px-3 py-1 bg-primary text-white text-sm font-semibold">%</button>
                                <button id="margin-type-rp" class="px-3 py-1 bg-transparent text-primary text-sm font-semibold">Rp</button>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-right">
                        <div>
                            <p class="text-xs text-subtle-light dark:text-subtle-dark">Modal / Porsi</p>
                            <span id="harga-modal-porsi" class="text-base font-bold text-content-light dark:text-content-dark">Rp 0</span>
                        </div>
                        <div>
                            <p class="text-xs text-subtle-light dark:text-subtle-dark">Untung / Porsi</p>
                            <span id="keuntungan-porsi" class="text-base font-bold text-green-600">Rp 0</span>
                        </div>
                        <div>
                            <p class="text-xs text-subtle-light dark:text-subtle-dark">Harga Jual</p>
                            <span id="harga-jual-porsi" class="text-base font-bold text-primary">Rp 0</span>
                        </div>
                        <div class="border-l border-gray-400 pl-2">
                            <p class="text-xs text-subtle-light dark:text-subtle-dark">Total Untung</p>
                            <span id="total-keuntungan" class="text-lg font-bold text-green-600">Rp 0</span>
                        </div>
                    </div>
                </div>
            </section>

            <form method="POST" action="detail_resep.php?id=<?php echo $resep_id; ?>" class="mb-8">
                <input type="hidden" name="porsi_dihitung" id="porsi-dihitung-hidden" value="<?php echo htmlspecialchars($resep['porsi_default']); ?>">
                <button type="submit" name="tambah_ke_belanjaan" class="w-full h-12 px-5 rounded-lg bg-primary text-white font-bold tracking-wide shadow-lg hover:bg-opacity-90">
                    Tambahkan ke Daftar Belanja
                </button>
            </form>

            <section class="mb-8">
                <h3 class="text-primary text-2xl font-bold leading-tight mb-4">Bahan</h3>
                <div class="space-y-4" id="ingredient-list-ul">
                    <?php foreach ($ingredients as $ing): ?>
                    <div class="flex justify-between items-center border-b border-border-light dark:border-border-dark py-4" data-base-qty="<?php echo htmlspecialchars($ing['jumlah']); ?>" data-nama-bahan="<?php echo htmlspecialchars($ing['nama_bahan']); ?>">
                        <div>
                            <p class="ingredient-name text-lg font-medium text-content-light dark:text-content-dark"><?php echo htmlspecialchars($ing['nama_bahan']); ?></p>
                            <span class="ingredient-cost text-sm text-primary"></span>
                        </div>
                        <p class="text-lg font-medium text-content-light dark:text-content-dark">
                            <span class="ingredient-qty"><?php echo rtrim(rtrim(number_format($ing['jumlah'], 2, ',', '.'), '0'), ','); ?></span>
                            <span class="ingredient-unit"><?php echo htmlspecialchars($ing['satuan']); ?></span>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <?php 
            if (!empty($resep['youtube_url'])): 
                $embed_link = get_youtube_embed_url($resep['youtube_url']); 
                if(!empty($embed_link)): 
            ?>
            <section class="mb-8">
                <h3 class="text-primary text-2xl font-bold leading-tight mb-4">Video Tutorial</h3>
                <div class="max-w-2xl mx-auto">
                    <div class="relative h-0 pb-[56.25%]">
                        <iframe 
                            class="absolute top-0 left-0 w-full h-full rounded-lg shadow-lg" 
                            src="<?php echo htmlspecialchars($embed_link); ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </section>
            <?php 
                endif; 
            endif; 
            ?>

            <section>
                <h3 class="text-primary text-2xl font-bold leading-tight mb-4">Cara Membuat</h3>
                <div class="prose dark:prose-invert max-w-none">
                    <?php echo nl2br(htmlspecialchars($resep['langkah'])); ?>
                </div>
            </section>
        </main>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const portionInput = document.getElementById('portion-input');
    const ingredientList = document.getElementById('ingredient-list-ul');
    const totalBahanCostEl = document.getElementById('total-bahan-cost');
    const totalOverheadCostEl = document.getElementById('total-overhead-cost');
    const overheadPercentDisplayEl = document.getElementById('overhead-percent-display');
    const marginInput = document.getElementById('margin-input');
    const marginTypePercentBtn = document.getElementById('margin-type-percent');
    const marginTypeRpBtn = document.getElementById('margin-type-rp');
    const hargaModalPorsiEl = document.getElementById('harga-modal-porsi');
    const keuntunganPorsiEl = document.getElementById('keuntungan-porsi');
    const hargaJualPorsiEl = document.getElementById('harga-jual-porsi');
    const totalKeuntunganEl = document.getElementById('total-keuntungan');
    
    const baseIngredientsData = <?php echo json_encode($base_ingredients_data); ?>;
    const basePortion = <?php echo (float)($resep['porsi_default'] > 0 ? $resep['porsi_default'] : 1); ?>;
    const overheadPercentage = <?php echo (float)$default_overhead; ?>;
    let totalBahanModal = 0;
    let totalModalHPP = 0;
    let marginType = '%'; 

    overheadPercentDisplayEl.textContent = overheadPercentage;

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Math.ceil(angka));
    }
    
    function calculateSellingPrice() {
        const porsi = parseFloat(portionInput.value) || 1;
        if (porsi <= 0) return;

        // Gunakan totalModalHPP yang sudah termasuk overhead
        const modalPerPorsi = totalModalHPP / porsi;
        let hargaJual = 0;
        let keuntunganPerPorsi = 0;
        const marginValue = parseFloat(marginInput.value) || 0;

        if (marginType === '%') {
            keuntunganPerPorsi = modalPerPorsi * (marginValue / 100);
        } else {
            keuntunganPerPorsi = marginValue;
        }
        hargaJual = modalPerPorsi + keuntunganPerPorsi;
        const totalKeuntungan = keuntunganPerPorsi * porsi;

        hargaModalPorsiEl.textContent = formatRupiah(modalPerPorsi);
        keuntunganPorsiEl.textContent = formatRupiah(keuntunganPerPorsi);
        hargaJualPorsiEl.textContent = formatRupiah(hargaJual);
        totalKeuntunganEl.textContent = formatRupiah(totalKeuntungan);
    }

    function calculateCost() {
        const targetPortion = parseFloat(portionInput.value) || basePortion;
        document.getElementById('porsi-dihitung-hidden').value = targetPortion;
        
        let currentTotalBahan = 0;
        ingredientList.querySelectorAll('[data-nama-bahan]').forEach(item => {
            const baseQty = parseFloat(item.dataset.baseQty);
            const namaBahan = item.dataset.namaBahan.toLowerCase();
            const newQty = (baseQty / basePortion) * targetPortion;
            
            item.querySelector('.ingredient-qty').textContent = newQty.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

            const bahanDasar = baseIngredientsData.find(b => b.nama.toLowerCase() === namaBahan);
            const costElement = item.querySelector('.ingredient-cost');
            
            if (bahanDasar && bahanDasar.harga > 0 && bahanDasar.jumlah > 0) {
                const hargaPerSatuan = bahanDasar.harga / bahanDasar.jumlah;
                const biayaItem = newQty * hargaPerSatuan;
                currentTotalBahan += biayaItem;
                if(costElement) costElement.textContent = `(${formatRupiah(biayaItem)})`;
            } else {
                if(costElement) costElement.textContent = '';
            }
        });
        
        // --- INI LOGIKA PENTINGNYA ---
        totalBahanModal = currentTotalBahan;
        const overheadCost = totalBahanModal * (overheadPercentage / 100);
        totalModalHPP = totalBahanModal + overheadCost; // HPP dihitung di sini
        
        // Update tampilan
        totalBahanCostEl.textContent = formatRupiah(totalBahanModal);
        totalOverheadCostEl.textContent = formatRupiah(overheadCost);
        
        // Panggil kalkulasi harga jual setelah HPP dihitung
        calculateSellingPrice();
    }
    
    portionInput.addEventListener('input', calculateCost);
    marginInput.addEventListener('input', calculateSellingPrice);

    function toggleMarginType(selectedType) {
        marginType = selectedType;
        if (selectedType === '%') {
            marginTypePercentBtn.classList.add('bg-primary', 'text-white');
            marginTypePercentBtn.classList.remove('bg-transparent', 'text-primary');
            marginTypeRpBtn.classList.add('bg-transparent', 'text-primary');
            marginTypeRpBtn.classList.remove('bg-primary', 'text-white');
        } else {
            marginTypeRpBtn.classList.add('bg-primary', 'text-white');
            marginTypeRpBtn.classList.remove('bg-transparent', 'text-primary');
            marginTypePercentBtn.classList.add('bg-transparent', 'text-primary');
            marginTypePercentBtn.classList.remove('bg-primary', 'text-white');
        }
        calculateSellingPrice();
    }

    marginTypePercentBtn.addEventListener('click', () => toggleMarginType('%'));
    marginTypeRpBtn.addEventListener('click', () => toggleMarginType('Rp'));

    // Panggil kalkulasi awal saat halaman dimuat
    calculateCost();
});
</script>
</body>
</html>
