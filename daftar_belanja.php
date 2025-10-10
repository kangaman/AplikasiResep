<?php
include __DIR__ . '/includes/header.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

if (isset($_GET['aksi']) && $_GET['aksi'] == 'kosongkan') {
    $conn->query("DELETE FROM shopping_list WHERE user_id = $user_id");
    header("Location: daftar_belanja.php");
    exit();
}

$sql = "
    SELECT 
        sl.id, sl.ingredient_id, sl.nama_bahan, sl.jumlah, sl.satuan, 
        bi.kategori, bi.harga_beli, bi.jumlah_beli
    FROM shopping_list sl 
    LEFT JOIN base_ingredients bi ON sl.ingredient_id = bi.id AND sl.user_id = bi.user_id 
    WHERE sl.user_id = ? 
    ORDER BY bi.kategori, sl.nama_bahan ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$daftar_belanja_grouped = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $kategori = $row['kategori'] ?? 'Lain-lain';
        $daftar_belanja_grouped[$kategori][] = $row;
    }
}
$stmt->close();
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Belanja Interaktif - Catatan Rasa Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: { extend: { colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "content-light": "#181411", "content-dark": "#f8f7f6", "subtle-dark": "#3a2e22", "input-light": "#f4f2f0", "input-dark": "#3a2d21" }, fontFamily: { "display": ["Epilogue", "sans-serif"] } } }
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-content-light dark:text-content-dark">
<div class="flex flex-col min-h-screen pb-20">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="max-w-3xl mx-auto p-4 flex-grow w-full">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Daftar Belanja Interaktif</h2>
            <button id="clear-list-btn" class="bg-red-500 text-white font-bold px-4 py-2 rounded-full text-sm">Kosongkan Semua</button>
        </div>

        <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow mb-6">
            <h3 class="font-bold mb-3 text-lg">Tambah Item Manual</h3>
            <form id="add-item-form" class="space-y-3">
                <div class="relative">
                    <label for="new-item-name" class="text-sm font-medium">Cari Bahan atau Ketik Baru</label>
                    <input type="text" id="new-item-name" placeholder="Contoh: Tepung Terigu" class="w-full h-12 mt-1 p-3 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" autocomplete="off">
                    <div id="bahan-suggestions" class="absolute z-20 w-full bg-white dark:bg-input-dark shadow-lg rounded-b-lg max-h-60 overflow-y-auto hidden"></div>
                    <input type="hidden" id="new-item-id">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                         <label for="new-item-qty" class="text-sm font-medium">Jumlah</label>
                        <input type="number" id="new-item-qty" placeholder="1" class="w-full h-12 mt-1 p-3 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label for="new-item-unit" class="text-sm font-medium">Satuan</label>
                        <input type="text" id="new-item-unit" placeholder="pack" class="w-full h-12 mt-1 p-3 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                <button type="submit" class="w-full h-12 bg-primary text-white font-bold rounded-full">Tambah ke Daftar</button>
            </form>
        </div>

        <div id="shopping-list-container" class="bg-white dark:bg-subtle-dark rounded-lg shadow-lg overflow-hidden">
            <?php if (empty($daftar_belanja_grouped)): ?>
                <p id="empty-list-msg" class="p-8 text-center text-gray-500">Daftar belanja kosong.</p>
            <?php else: ?>
                <div id="list-content" class="space-y-6 p-4">
                    <?php foreach ($daftar_belanja_grouped as $kategori => $items): ?>
                    <div class="kategori-group">
                        <h3 class="text-lg font-bold text-primary mb-2"><?php echo htmlspecialchars($kategori); ?></h3>
                        <div class="space-y-2">
                        <?php foreach ($items as $item): ?>
                            <div class="item-row p-2 rounded-lg border border-gray-200 dark:border-gray-700" data-id="<?php echo $item['id']; ?>" data-ingredient-id="<?php echo $item['ingredient_id']; ?>">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold"><?php echo htmlspecialchars($item['nama_bahan']); ?></span>
                                    <button class="delete-item-btn text-red-500 font-bold text-xl">&times;</button>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mt-2 text-sm items-center">
                                    <div class="col-span-1">
                                        <label class="text-xs text-gray-500">Jumlah</label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" step="0.01" class="item-qty w-full h-9 p-2 text-center rounded-md bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" value="<?php echo rtrim(rtrim(number_format($item['jumlah'], 2, '.', ''), '0'), '.'); ?>">
                                            <span class="text-sm text-gray-500"><?php echo htmlspecialchars($item['satuan']); ?></span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Harga Beli Baru</label>
                                        <input type="number" class="item-price w-full h-9 p-2 text-right rounded-md bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" placeholder="<?php echo $item['harga_beli']; ?>">
                                    </div>
                                    <button class="update-price-btn h-9 bg-green-600 text-white text-xs font-bold rounded-md mt-4">Update Harga</button>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Harga saat ini: Rp <?php echo number_format($item['harga_beli'] ?? 0); ?> / <?php echo ($item['jumlah_beli'] ?? 'N/A') . ' ' . ($item['satuan'] ?? ''); ?></p>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const listContainer = document.getElementById('shopping-list-container');
    const addItemForm = document.getElementById('add-item-form');
    const clearListBtn = document.getElementById('clear-list-btn');
    const newItemNameInput = document.getElementById('new-item-name');
    const newItemIdInput = document.getElementById('new-item-id');
    const newItemUnitInput = document.getElementById('new-item-unit');
    const bahanSuggestionsBox = document.getElementById('bahan-suggestions');

    async function apiCall(action, data) {
        try {
            const response = await fetch('ajax/ajax_shopping_list.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, ...data })
            });
            return await response.json();
        } catch (error) {
            console.error('API Call Error:', error);
            return { status: 'error', message: 'Koneksi bermasalah.' };
        }
    }

    newItemNameInput.addEventListener('keyup', async function() {
        const query = this.value.trim();
        newItemIdInput.value = '';
        if (query.length < 2) {
            bahanSuggestionsBox.classList.add('hidden');
            return;
        }
        try {
            const response = await fetch(`ajax/ajax_search_base_ingredients.php?q=${encodeURIComponent(query)}`);
            const ingredients = await response.json();
            if (ingredients.length > 0) {
                bahanSuggestionsBox.innerHTML = ingredients.map(ing => `<div class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer bahan-suggestion-item" data-ingredient='${JSON.stringify(ing)}'><p class="font-semibold">${ing.nama_bahan}</p></div>`).join('');
                bahanSuggestionsBox.classList.remove('hidden');
            } else {
                bahanSuggestionsBox.classList.add('hidden');
            }
        } catch (error) { console.error('Error fetching ingredients:', error); }
    });
    
    bahanSuggestionsBox.addEventListener('click', function(e) {
        const itemDiv = e.target.closest('.bahan-suggestion-item');
        if (itemDiv) {
            const ingredient = JSON.parse(itemDiv.dataset.ingredient);
            newItemNameInput.value = ingredient.nama_bahan;
            newItemIdInput.value = ingredient.id;
            newItemUnitInput.value = ingredient.satuan_beli;
            bahanSuggestionsBox.classList.add('hidden');
        }
    });

    function addRowEventListeners(rowElement) {
        const shoppingListId = rowElement.dataset.id;
        const ingredientId = rowElement.dataset.ingredientId;
        const qtyInput = rowElement.querySelector('.item-qty');
        const priceInput = rowElement.querySelector('.item-price');
        const updateBtn = rowElement.querySelector('.update-price-btn');
        const deleteBtn = rowElement.querySelector('.delete-item-btn');

        let debounceTimer;
        qtyInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                apiCall('update_qty', { id: shoppingListId, jumlah: qtyInput.value });
            }, 500);
        });

        if(deleteBtn){
            deleteBtn.addEventListener('click', async () => {
                if (confirm('Yakin ingin menghapus item ini?')) {
                    const result = await apiCall('delete', { id: shoppingListId });
                    if (result.status === 'success') rowElement.remove();
                    else alert('Gagal menghapus: ' + result.message);
                }
            });
        }
        
        if(updateBtn){
            updateBtn.addEventListener('click', async () => {
                const newPrice = priceInput.value;
                if (!ingredientId || !newPrice) {
                    alert('Item ini tidak terhubung ke daftar bahan atau harga baru kosong.');
                    return;
                }
                if (confirm('Yakin ingin memperbarui harga bahan ini di seluruh aplikasi?')) {
                    const result = await apiCall('update_price', { ingredient_id: ingredientId, harga_beli: newPrice });
                    if (result.status === 'success') {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert('Gagal update harga: ' + result.message);
                    }
                }
            });
        }
    }

    addItemForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const result = await apiCall('add', {
            ingredient_id: document.getElementById('new-item-id').value,
            nama_bahan: document.getElementById('new-item-name').value,
            jumlah: document.getElementById('new-item-qty').value,
            satuan: document.getElementById('new-item-unit').value
        });
        if (result.status === 'success') location.reload(); 
        else alert('Gagal menambah: ' + result.message);
    });
    
    if(clearListBtn){
        clearListBtn.addEventListener('click', () => {
            if(confirm('Anda yakin ingin mengosongkan seluruh daftar belanja?')) {
                window.location.href = 'daftar_belanja.php?aksi=kosongkan';
            }
        });
    }

    document.querySelectorAll('.item-row').forEach(addRowEventListeners);

    document.addEventListener('click', function(event) {
        if (!bahanSuggestionsBox.contains(event.target) && event.target !== newItemNameInput) {
            bahanSuggestionsBox.classList.add('hidden');
        }
    });
});
</script>
</body>
</html>