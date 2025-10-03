<?php
session_start(); // KRUSIAL: Harus ada di baris paling atas

include __DIR__ . '/config/config.php';

// ... (Fungsi resize_and_save_image tetap sama, tidak perlu diubah) ...
function resize_and_save_image($source_path, $destination_path, $max_width, $max_height, $quality = 85) {
    if (!file_exists($source_path) || filesize($source_path) === 0) return false;
    $image_info = getimagesize($source_path);
    if (!$image_info) return false;
    list($width, $height, $type) = $image_info;
    $source_image = null;
    switch ($type) {
        case IMAGETYPE_JPEG: $source_image = imagecreatefromjpeg($source_path); break;
        case IMAGETYPE_PNG: $source_image = imagecreatefrompng($source_path); break;
        case IMAGETYPE_GIF: $source_image = imagecreatefromgif($source_path); break;
        default: return false;
    }
    if (!$source_image) return false;
    $ratio = $width / $height;
    if ($max_width / $max_height > $ratio) {
        $new_width = $max_height * $ratio;
        $new_height = $max_height;
    } else {
        $new_width = $max_width;
        $new_height = $max_width / $ratio;
    }
    $new_image = imagecreatetruecolor($new_width, $new_height);
    if ($type == IMAGETYPE_PNG) {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }
    imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG: $success = imagejpeg($new_image, $destination_path, $quality); break;
        case IMAGETYPE_PNG: $png_quality = floor(($quality / 100) * 9); $success = imagepng($new_image, $destination_path, $png_quality); break;
        case IMAGETYPE_GIF: $success = imagegif($new_image, $destination_path); break;
    }
    imagedestroy($source_image);
    imagedestroy($new_image);
    return $success;
}


if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$resep = ['id' => '', 'nama_kue' => '', 'langkah' => '', 'gambar' => '', 'youtube_url' => '', 'porsi_default' => 1];
$ingredients = [];
$title = "Tambah Resep Baru";
$action_url = "form_resep.php";
$pesan = '';

if (isset($_GET['id'])) {
    $resep_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM resep WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $resep_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $resep = $result->fetch_assoc();
        $title = "Edit Resep";
        $action_url = "form_resep.php?id=" . $resep_id;

        // Query diubah untuk JOIN dengan base_ingredients agar mendapat data harga dasar
        $ing_stmt = $conn->prepare("
            SELECT i.jumlah, i.satuan, i.nama_bahan, b.jumlah_beli, b.harga_beli
            FROM ingredients i
            LEFT JOIN base_ingredients b ON i.nama_bahan = b.nama_bahan AND b.user_id = ?
            WHERE i.resep_id = ?
        ");
        $ing_stmt->bind_param("ii", $user_id, $resep_id);
        $ing_stmt->execute();
        $ingredients = $ing_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $ing_stmt->close();
    } else {
        header("Location: dashboard.php");
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kue = trim($_POST['nama_kue']);
    $langkah = trim($_POST['langkah']);
    $youtube_url = trim($_POST['youtube_url']);
    $porsi_default = (int)($_POST['porsi_default'] ?? 1);
    $resep_id_post = $_POST['id'];
    $current_gambar = $_POST['current_gambar'] ?? '';
    $gambar_file_name = $current_gambar;

    if (empty($nama_kue) || !isset($_POST['jumlah_bahan']) || empty($_POST['jumlah_bahan']) || empty($langkah)) {
        $pesan = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4'>Nama resep, langkah, dan minimal satu bahan wajib diisi!</div>";
    } else {
        if (isset($_FILES['gambar_resep']) && $_FILES['gambar_resep']['error'] == UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($_FILES['gambar_resep']['tmp_name']);
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($mime_type, $allowed_mime_types) && $_FILES['gambar_resep']['size'] <= 5 * 1024 * 1024) {
                $file_tmp_name = $_FILES['gambar_resep']['tmp_name'];
                $new_file_name = uniqid('resep_',true).'.'.pathinfo($_FILES['gambar_resep']['name'], PATHINFO_EXTENSION);
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $upload_path = $upload_dir.$new_file_name;
                if (resize_and_save_image($file_tmp_name, $upload_path, 800, 800)) {
                    if ($current_gambar && file_exists($upload_dir.$current_gambar)) unlink($upload_dir.$current_gambar);
                    $gambar_file_name = $new_file_name;
                }
            } else {
                $pesan = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4'>File gambar tidak valid atau terlalu besar (maks 5MB).</div>";
            }
        }
        if (empty($pesan)) {
            $conn->begin_transaction();
            try {
                if (empty($resep_id_post)) { // Insert resep baru
                    $stmt = $conn->prepare("INSERT INTO resep (nama_kue, langkah, gambar, youtube_url, porsi_default, user_id, bahan) VALUES (?, ?, ?, ?, ?, ?, '')");
                    $stmt->bind_param("ssssii", $nama_kue, $langkah, $gambar_file_name, $youtube_url, $porsi_default, $user_id);
                    $stmt->execute();
                    $new_resep_id = $stmt->insert_id;
                } else { // Update resep lama
                    $stmt = $conn->prepare("UPDATE resep SET nama_kue=?, langkah=?, gambar=?, youtube_url=?, porsi_default=? WHERE id=? AND user_id=?");
                    $stmt->bind_param("ssssiii", $nama_kue, $langkah, $gambar_file_name, $youtube_url, $porsi_default, $resep_id_post, $user_id);
                    $stmt->execute();
                    $conn->query("DELETE FROM ingredients WHERE resep_id=".(int)$resep_id_post);
                    $new_resep_id = $resep_id_post;
                }
                
                // Simpan ingredients
                $ing_stmt = $conn->prepare("INSERT INTO ingredients (resep_id, nama_bahan, jumlah, satuan) VALUES (?, ?, ?, ?)");
                foreach ($_POST['jumlah_bahan'] as $key => $jumlah) {
                    if (!empty($jumlah) && !empty($_POST['nama_bahan'][$key])) {
                        $jumlah_val = floatval(str_replace(',', '.', $jumlah));
                        $satuan = trim($_POST['satuan_bahan'][$key]);
                        $nama_bahan = trim($_POST['nama_bahan'][$key]);
                        $ing_stmt->bind_param("isds", $new_resep_id, $nama_bahan, $jumlah_val, $satuan);
                        $ing_stmt->execute();
                    }
                }
                $ing_stmt->close();
                $conn->commit();
                header("Location: detail_resep.php?id=" . $new_resep_id);
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $pesan = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4'>Terjadi kesalahan: " . $e->getMessage() . "</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: { extend: { colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "foreground-light": "#181411", "foreground-dark": "#f8f7f6", "input-light": "#f4f2f0", "input-dark": "#3a2d21" }, fontFamily: { "display": ["Epilogue", "sans-serif"] } } }
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-foreground-light dark:text-foreground-dark">
<div class="flex flex-col min-h-screen pb-20"> <header class="sticky top-0 z-10 flex items-center bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm p-4 justify-between">
        <a href="dashboard.php" class="p-2"><span class="material-symbols-outlined">arrow_back</span></a>
        <h1 class="text-lg font-bold"><?php echo htmlspecialchars($title); ?></h1>
        <div class="w-10"></div>
    </header>
    
    <main class="p-4 space-y-4 flex-grow">
        <?php echo $pesan; ?>
        <form method="POST" action="<?php echo htmlspecialchars($action_url); ?>" enctype="multipart/form-data" class="pb-20">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($resep['id']); ?>">
            <input type="hidden" name="current_gambar" value="<?php echo htmlspecialchars($resep['gambar']); ?>">
            
            <div class="space-y-2 mb-4">
                <label class="font-medium" for="nama_kue">Nama Resep</label>
                <input class="w-full h-14 p-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" id="nama_kue" name="nama_kue" placeholder="Contoh: Kue Nastar Keju" type="text" value="<?php echo htmlspecialchars($resep['nama_kue']); ?>" required/>
            </div>
              <div class="space-y-2 mb-4">
                <label class="font-medium" for="porsi_default">Hasil Jadi (Porsi)</label>
                <input class="w-full h-14 p-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" id="porsi_default" name="porsi_default" placeholder="Contoh: 40" type="number" value="<?php echo htmlspecialchars($resep['porsi_default']); ?>" required/>
            </div>

            <div class="space-y-2 mb-4">
                <label class="font-medium" for="bahan_autocomplete">Cari & Tambah Bahan</label>
                <div class="relative">
                    <input class="w-full h-14 p-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" id="bahan_autocomplete" placeholder="Ketik nama bahan untuk ditambahkan..." type="text" />
                    <div id="suggestions" class="absolute z-10 w-full bg-white dark:bg-input-dark shadow-lg rounded-b-lg max-h-60 overflow-y-auto" style="display: none;"></div>
                </div>
            </div>
            
            <div class="space-y-2 mb-4">
                <label class="font-medium">Daftar Bahan</label>
                <div class="overflow-x-auto bg-input-light/50 dark:bg-input-dark/50 p-2 rounded-lg">
                    <table class="w-full">
                        <thead class="text-left text-sm text-gray-500">
                             <tr>
                                <th class="p-2">Jumlah</th>
                                <th class="p-2">Satuan</th>
                                <th class="p-2">Nama Bahan</th>
                                <th class="p-2 text-right">Harga</th> <th class="p-2"></th>
                             </tr>
                        </thead>
                        <tbody id="ingredient-container">
                            <?php if (!empty($ingredients)): ?>
                                <?php foreach ($ingredients as $ing): ?>
                                <?php
                                    // Hitung harga awal untuk bahan yang sudah ada
                                    $harga_awal = 0;
                                    if (!empty($ing['jumlah_beli']) && $ing['jumlah_beli'] > 0) {
                                        $harga_awal = ($ing['jumlah'] / $ing['jumlah_beli']) * $ing['harga_beli'];
                                    }
                                ?>
                                <tr class="ingredient-row" 
                                    data-base-quantity="<?php echo htmlspecialchars($ing['jumlah_beli'] ?? 0); ?>" 
                                    data-base-price="<?php echo htmlspecialchars($ing['harga_beli'] ?? 0); ?>">
                                    <td class="p-1 w-24"><input type="text" name="jumlah_bahan[]" class="jumlah-bahan w-full h-12 p-2 rounded-md bg-white dark:bg-background-dark border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary" value="<?php echo htmlspecialchars(rtrim(rtrim(number_format($ing['jumlah'], 2, ',', '.'), '0'), ',')); ?>" required></td>
                                    <td class="p-1 w-24"><input type="text" name="satuan_bahan[]" class="satuan-bahan w-full h-12 p-2 rounded-md bg-white dark:bg-background-dark border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary" value="<?php echo htmlspecialchars($ing['satuan']); ?>" required></td>
                                    <td class="p-1"><input type="text" name="nama_bahan[]" class="nama-bahan w-full h-12 p-2 rounded-md bg-gray-200 dark:bg-gray-800 border-none" value="<?php echo htmlspecialchars($ing['nama_bahan']); ?>" readonly></td>
                                    <td class="p-1 w-32 text-right harga-terkalkulasi">
                                        Rp <?php echo number_format($harga_awal, 0, ',', '.'); ?>
                                    </td>
                                    <td class="p-1"><button type="button" class="remove-ingredient-btn text-red-500 text-2xl font-bold h-12 w-12 flex items-center justify-center">&times;</button></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                  <p id="empty-ingredients-msg" class="text-center text-gray-500 p-4 <?php if (!empty($ingredients)) echo 'hidden'; ?>">Belum ada bahan yang ditambahkan.</p>
            </div>
            <div class="space-y-2 mb-4">
                <label class="font-medium" for="langkah">Langkah-langkah</label>
                <textarea class="w-full min-h-36 p-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" id="langkah" name="langkah" placeholder="1. Campurkan..." required><?php echo htmlspecialchars($resep['langkah']); ?></textarea>
            </div>
              <div class="space-y-2 mb-4">
                <label class="font-medium" for="youtube_url">Link Video YouTube (Opsional)</label>
                <input class="w-full h-14 p-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" id="youtube_url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=xxxxx" type="url" value="<?php echo htmlspecialchars($resep['youtube_url']); ?>"/>
            </div>
              <div class="space-y-2 mb-4">
                <label class="font-medium" for="gambar_resep">Gambar Resep</label>
                <?php if (!empty($resep['gambar'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($resep['gambar']); ?>" alt="Gambar saat ini" class="w-32 h-32 object-cover rounded-lg mb-2">
                <?php endif; ?>
                <input class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30" id="gambar_resep" name="gambar_resep" type="file" accept="image/jpeg,image/png,image/gif"/>
            </div>


            </form>
    </main>

    <footer class="fixed bottom-0 left-0 w-full bg-background-light dark:bg-background-dark border-t border-gray-200 dark:border-gray-700 z-20">
        <div class="max-w-3xl mx-auto flex justify-around items-center h-20">
            <a class="flex flex-col items-center justify-center gap-1 text-gray-500 dark:text-gray-400" href="dashboard.php">
                <span class="material-symbols-outlined">home</span>
                <p class="text-xs font-medium">Dashboard</p>
            </a>
            <a class="flex flex-col items-center justify-center gap-1 text-gray-500 dark:text-gray-400" href="manajemen_bahan.php">
                <span class="material-symbols-outlined">menu_book</span>
                <p class="text-xs font-medium">Bahan</p>
            </a>
            <a class="flex flex-col items-center justify-center gap-1 text-primary" href="form_resep.php">
                <span class="material-symbols-outlined">add_circle</span>
                <p class="text-xs font-medium">Tambah Resep</p>
            </a>
           
        </div>
        <div class="fixed bottom-24 left-1/2 -translate-x-1/2 w-full max-w-sm px-4">
             <button type="submit" form="main-form" class="w-full h-12 px-5 rounded-lg bg-primary text-white font-bold tracking-wide">Simpan Resep</button>
        </div>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById("bahan_autocomplete");
    const suggestionsBox = document.getElementById("suggestions");
    const container = document.getElementById('ingredient-container');
    const emptyMsg = document.getElementById('empty-ingredients-msg');

    // Pastikan form memiliki ID untuk di-submit dari tombol di footer
    const mainForm = document.querySelector('form');
    if (mainForm) {
        mainForm.setAttribute('id', 'main-form');
    }

    const checkEmpty = () => {
        const rowCount = container.querySelectorAll('.ingredient-row').length;
        emptyMsg.classList.toggle('hidden', rowCount > 0);
    };

    // Fungsi untuk memformat angka menjadi format Rupiah
    const formatRupiah = (angka) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    };

    // Fungsi untuk menghitung dan memperbarui harga di satu baris
    const updateRowPrice = (inputElement) => {
        const row = inputElement.closest('.ingredient-row');
        if (!row) return;

        const baseQuantity = parseFloat(row.dataset.baseQuantity) || 0;
        const basePrice = parseFloat(row.dataset.basePrice) || 0;
        // Gunakan .replace(',', '.') untuk memastikan parsing float yang benar
        const recipeQuantity = parseFloat(inputElement.value.replace(',', '.')) || 0;
        const priceCell = row.querySelector('.harga-terkalkulasi');

        if (!priceCell || baseQuantity === 0) {
            if(priceCell) priceCell.textContent = 'N/A';
            return;
        }

        const calculatedPrice = (recipeQuantity / baseQuantity) * basePrice;
        priceCell.textContent = formatRupiah(calculatedPrice);
    };

    const addRow = (item) => {
        const existingItems = Array.from(container.querySelectorAll('.nama-bahan')).map(input => input.value.toLowerCase());
        if (existingItems.includes(item.nama.toLowerCase())) {
            alert(`Bahan "${item.nama}" sudah ada di dalam daftar.`);
            return;
        }

        const newRow = document.createElement('tr');
        newRow.className = 'ingredient-row';
        // Simpan data harga dasar di `data-*` attribute pada elemen TR
        newRow.dataset.baseQuantity = item.jumlah; // dari ajax: jumlah_beli
        newRow.dataset.basePrice = item.harga;   // dari ajax: harga_beli

        newRow.innerHTML = `
            <td class="p-1 w-24"><input type="text" name="jumlah_bahan[]" class="jumlah-bahan w-full h-12 p-2 rounded-md bg-white dark:bg-background-dark border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary" placeholder="Jumlah" required></td>
            <td class="p-1 w-24"><input type="text" name="satuan_bahan[]" class="satuan-bahan w-full h-12 p-2 rounded-md bg-white dark:bg-background-dark border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary" value="${item.satuan || ''}" required></td>
            <td class="p-1"><input type="text" name="nama_bahan[]" class="nama-bahan w-full h-12 p-2 rounded-md bg-gray-200 dark:bg-gray-800 border-none" value="${item.nama}" readonly></td>
            <td class="p-1 w-32 text-right harga-terkalkulasi">Rp 0</td>
            <td class="p-1"><button type="button" class="remove-ingredient-btn text-red-500 text-2xl font-bold h-12 w-12 flex items-center justify-center">&times;</button></td>
        `;
        container.appendChild(newRow);
        checkEmpty();
        
        const jumlahInput = newRow.querySelector('.jumlah-bahan');
        jumlahInput.focus();
        jumlahInput.addEventListener('input', function() { // Tambahkan listener saat baris baru dibuat
            updateRowPrice(this);
        });

        newRow.querySelector('.remove-ingredient-btn').addEventListener('click', function() {
            this.closest('.ingredient-row').remove();
            checkEmpty();
        });

        // Hitung harga awal untuk item yang baru ditambahkan
        updateRowPrice(jumlahInput);
    };
    
    // Gunakan event delegation untuk memantau input pada kolom jumlah
    container.addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('jumlah-bahan')) {
            updateRowPrice(e.target);
        }
    });

    // Panggil event listener untuk tombol hapus yang sudah ada (mode edit)
    container.querySelectorAll('.remove-ingredient-btn').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.ingredient-row').remove();
            checkEmpty();
        });
    });

    // Inisialisasi kalkulasi harga untuk bahan yang sudah ada (saat halaman dimuat dalam mode edit)
    container.querySelectorAll('.jumlah-bahan').forEach(input => {
        updateRowPrice(input);
    });

    // Event listener untuk input pencarian (tidak berubah)
    searchInput.addEventListener("keyup", function() {
        const query = this.value.trim();
        if (query.length < 1) {
            suggestionsBox.style.display = 'none';
            return;
        }

        fetch(`ajax/ajax_bahan.php?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) { 
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.length > 0) {
                    suggestionsBox.style.display = 'block';
                    suggestionsBox.innerHTML = data.map(item => {
                        const itemJson = JSON.stringify(item).replace(/'/g, "&apos;");
                        return `<div class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer suggestion-item" data-item='${itemJson}'>
                                    <p class="font-semibold">${item.nama}</p>
                                </div>`;
                    }).join("");

                    // Tambahkan event listener untuk setiap item saran yang baru dibuat
                    suggestionsBox.querySelectorAll('.suggestion-item').forEach(itemDiv => {
                        itemDiv.addEventListener('click', function() {
                            const item = JSON.parse(this.dataset.item);
                            addRow(item);
                            searchInput.value = '';
                            suggestionsBox.style.display = 'none';
                        });
                    });

                } else {
                    suggestionsBox.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
                suggestionsBox.style.display = 'none';
            });
    });

    // Event listener untuk klik di luar suggestion box (tidak berubah)
    document.addEventListener('click', function(event) {
        if (!suggestionsBox.contains(event.target) && event.target !== searchInput) {
            suggestionsBox.style.display = 'none';
        }
    });

    checkEmpty();
});
</script>
</body>
</html>