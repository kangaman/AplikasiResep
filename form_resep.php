<?php
session_start(); // KRUSIAL: Harus ada di baris paling atas

include __DIR__ . '/config/config.php';

// Fungsi resize & simpan gambar
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
$resep = ['id' => '', 'nama_kue' => '', 'langkah' => '', 'gambar' => '', 'youtube_url' => '', 'porsi_default' => 1, 'kategori' => 'Lain-lain'];
$ingredients = [];
$title = "Tambah Resep Baru";
$action_url = "form_resep.php";
$pesan = '';

// Definisikan daftar kategori resep
$kategori_resep_list = [
    'Kue Kering', 'Jajanan Pasar', 'Roti & Donat', 'Cake & Bolu', 
    'Pudding & Dessert', 'Minuman', 'Lain-lain'
];

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
    $kategori_resep = $_POST['kategori_resep'] ?? 'Lain-lain';
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
                if (empty($resep_id_post)) {
                    $stmt = $conn->prepare("INSERT INTO resep (nama_kue, kategori, langkah, gambar, youtube_url, porsi_default, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssii", $nama_kue, $kategori_resep, $langkah, $gambar_file_name, $youtube_url, $porsi_default, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE resep SET nama_kue=?, kategori=?, langkah=?, gambar=?, youtube_url=?, porsi_default=? WHERE id=? AND user_id=?");
                    $stmt->bind_param("sssssiii", $nama_kue, $kategori_resep, $langkah, $gambar_file_name, $youtube_url, $porsi_default, $resep_id_post, $user_id);
                }
                $stmt->execute();
                
                $new_resep_id = empty($resep_id_post) ? $stmt->insert_id : $resep_id_post;
                
                if (!empty($resep_id_post)) {
                    $conn->query("DELETE FROM ingredients WHERE resep_id=".(int)$resep_id_post);
                }
                
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
<div class="flex flex-col min-h-screen pb-40">
<?php include __DIR__ . '/includes/navigation.php'; ?>
    
    <main class="p-4 space-y-4 flex-grow">
        <?php echo $pesan; ?>
        <form method="POST" action="<?php echo htmlspecialchars($action_url); ?>" enctype="multipart/form-data" id="main-form">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($resep['id']); ?>">
            <input type="hidden" name="current_gambar" value="<?php echo htmlspecialchars($resep['gambar']); ?>">
            
            <div class="space-y-2 mb-4">
                <label class="font-medium" for="nama_kue">Nama Resep</label>
                <input class="w-full h-14 p-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" id="nama_kue" name="nama_kue" placeholder="Contoh: Kue Nastar Keju" type="text" value="<?php echo htmlspecialchars($resep['nama_kue']); ?>" required/>
            </div>

            <div class="space-y-2 mb-4">
                <label class="font-medium" for="kategori_resep">Kategori Resep</label>
                <select id="kategori_resep" name="kategori_resep" class="w-full h-14 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                    <option value="Lain-lain">Pilih Kategori</option>
                    <?php foreach ($kategori_resep_list as $kat): ?>
                        <option value="<?php echo htmlspecialchars($kat); ?>" <?php if (($resep['kategori'] ?? 'Lain-lain') == $kat) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($kat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                                <th class="p-2 text-right">Harga</th>
                                <th class="p-2"></th>
                             </tr>
                        </thead>
                        <tbody id="ingredient-container">
                            <?php if (!empty($ingredients)): ?>
                                <?php foreach ($ingredients as $ing): ?>
                                <?php
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
                                    <td class="p-1 w-32 text-right harga-terkalkulasi">Rp <?php echo number_format($harga_awal, 0, ',', '.'); ?></td>
                                    <td class="p-1"><button type="button" class="remove-ingredient-btn text-red-500 text-2xl font-bold h-12 w-12 flex items-center justify-center">&times;</button></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                  <p id="empty-ingredients-msg" class="text-center text-gray-500 p-4 <?php if (empty($ingredients)) echo ''; else echo 'hidden'; ?>">Belum ada bahan yang ditambahkan.</p>
            </div>
            
            <div class="space-y-2 mb-4">
                <label class="font-medium" for="langkah">Langkah-langkah</label>
                <textarea class="w-full min-h-[150px] p-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" id="langkah" name="langkah" placeholder="1. Campurkan..." required><?php echo htmlspecialchars($resep['langkah']); ?></textarea>
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

<footer class="fixed bottom-0 left-0 w-full bg-background-light dark:bg-background-dark border-t border-gray-200 dark:border-gray-700 z-20 p-4">
    <div class="max-w-3xl mx-auto">
         <button type="submit" form="main-form" class="w-full h-12 px-5 rounded-lg bg-primary text-white font-bold tracking-wide shadow-lg hover:bg-opacity-90">Simpan Resep</button>
    </div>
</footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById("bahan_autocomplete");
    const suggestionsBox = document.getElementById("suggestions");
    const container = document.getElementById('ingredient-container');
    const emptyMsg = document.getElementById('empty-ingredients-msg');

    const mainForm = document.querySelector('form');
    if (mainForm) {
        mainForm.setAttribute('id', 'main-form');
    }

    const checkEmpty = () => {
        const rowCount = container.querySelectorAll('.ingredient-row').length;
        emptyMsg.classList.toggle('hidden', rowCount > 0);
    };

    const formatRupiah = (angka) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    };

    const updateRowPrice = (inputElement) => {
        const row = inputElement.closest('.ingredient-row');
        if (!row) return;
        const baseQuantity = parseFloat(row.dataset.baseQuantity) || 0;
        const basePrice = parseFloat(row.dataset.basePrice) || 0;
        const recipeQuantity = parseFloat(String(inputElement.value).replace(/\./g, '').replace(',', '.')) || 0;
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
        newRow.dataset.baseQuantity = item.jumlah;
        newRow.dataset.basePrice = item.harga;

        newRow.innerHTML = `
            <td class="p-1 w-24"><input type="text" name="jumlah_bahan[]" class="jumlah-bahan w-full h-12 p-2 rounded-md bg-white dark:bg-background-dark border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary" placeholder="Jumlah" required></td>
            <td class="p-1 w-24"><input type="text" name="satuan_bahan[]" class="satuan-bahan w-full h-12 p-2 rounded-md bg-white dark:bg-background-dark border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary" value="${item.satuan || ''}" required></td>
            <td class="p-1"><input type="text" name="nama_bahan[]" class="nama-bahan w-full h-12 p-2 rounded-md bg-gray-200 dark:bg-gray-800 border-none" value="${item.nama}" readonly></td>
            <td class="p-1 w-32 text-right harga-terkalkulasi">Rp 0</td>
            <td class="p-1"><button type="button" class="remove-ingredient-btn text-red-500 text-2xl font-bold h-12 w-12 flex items-center justify-center">&times;</button></td>
        `;
        container.appendChild(newRow);
        checkEmpty();
        
        newRow.querySelector('.jumlah-bahan').focus();
        
        newRow.querySelector('.remove-ingredient-btn').addEventListener('click', function() {
            this.closest('.ingredient-row').remove();
            checkEmpty();
        });
    };
    
    container.addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('jumlah-bahan')) {
            updateRowPrice(e.target);
        }
    });

    container.querySelectorAll('.remove-ingredient-btn').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.ingredient-row').remove();
            checkEmpty();
        });
    });

    container.querySelectorAll('.jumlah-bahan').forEach(input => {
        updateRowPrice(input);
    });

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
                } else {
                    suggestionsBox.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
                suggestionsBox.style.display = 'none';
            });
    });

    suggestionsBox.addEventListener('click', function(e) {
        const itemDiv = e.target.closest('.suggestion-item');
        if (itemDiv) {
            const item = JSON.parse(itemDiv.dataset.item);
            addRow(item);
            searchInput.value = '';
            suggestionsBox.style.display = 'none';
        }
    });

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