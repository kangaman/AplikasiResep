<?php
include __DIR__ . '/includes/header.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$pesan = '';
$order = [];
$order_items = [];
$order_expenses = [];
$title = "Catat Pesanan Baru";
$is_edit_mode = false;

if (isset($_GET['edit_id'])) {
    $is_edit_mode = true;
    $order_id = (int)$_GET['edit_id'];
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $title = "Edit Pesanan #" . $order['id'];
    } else {
        header("Location: manajemen_pesanan.php");
        exit();
    }
    $stmt->close();

    $stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $order_items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();

    $stmt_expenses = $conn->prepare("SELECT * FROM order_expenses WHERE order_id = ?");
    $stmt_expenses->bind_param("i", $order_id);
    $stmt_expenses->execute();
    $order_expenses = $stmt_expenses->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_expenses->close();
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Catatan Rasa Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "foreground-light": "#181411", "foreground-dark": "#f8f7f6", "input-light": "#f4f2f0", "input-dark": "#3a2d21", "subtle-dark": "#3a2e22" },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-foreground-light dark:text-foreground-dark">
<div class="flex flex-col min-h-screen pb-40">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="p-4 space-y-6 flex-grow">
        <?php if ($pesan) echo $pesan; ?>
        <form action="simpan_pesanan.php" method="POST" id="form-pesanan">
            <input type="hidden" name="order_id" value="<?php echo $order['id'] ?? ''; ?>">
            <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $order['customer_id'] ?? ''; ?>">

            <div class="space-y-6">
                <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow">
                    <h3 class="font-bold mb-4">Detail Pelanggan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1 md:col-span-2 relative">
                            <label for="nama_pelanggan" class="text-sm font-medium">Cari Nama Pelanggan</label>
                            <input type="text" id="nama_pelanggan" name="nama_pelanggan" value="<?php echo htmlspecialchars($order['nama_pelanggan'] ?? ''); ?>" class="w-full h-12 p-3 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" placeholder="Ketik nama atau tambah pelanggan baru..." required autocomplete="off">
                            <div id="customer-suggestions" class="absolute z-20 w-full bg-white dark:bg-input-dark shadow-lg rounded-b-lg max-h-60 overflow-y-auto hidden"></div>
                        </div>
                        <div class="space-y-1">
                            <label for="kontak_pelanggan" class="text-sm font-medium">Kontak (WA/Telp)</label>
                            <input type="text" id="kontak_pelanggan" name="kontak_pelanggan" value="<?php echo htmlspecialchars($order['kontak_pelanggan'] ?? ''); ?>" class="w-full h-12 p-3 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                        </div>
                         <div class="space-y-1">
                            <label for="tanggal_pengiriman" class="text-sm font-medium">Tgl Pengiriman</label>
                            <input type="date" id="tanggal_pengiriman" name="tanggal_pengiriman" value="<?php echo htmlspecialchars($order['tanggal_pengiriman'] ?? ''); ?>" class="w-full h-12 p-3 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label for="alamat_pengiriman" class="text-sm font-medium">Alamat</label>
                            <textarea id="alamat_pengiriman" name="alamat_pengiriman" rows="2" class="w-full p-3 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($order['alamat_pengiriman'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow">
                    <h3 class="font-bold mb-4">Item Pesanan</h3>
                    <div class="relative mb-4">
                        <label for="search-item" class="text-sm font-medium">Cari Resep atau Paket Snack Box</label>
                        <input type="text" id="search-item" placeholder="Ketik nama item..." class="w-full h-12 mt-1 p-3 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" autocomplete="off">
                        <div id="suggestions-box" class="absolute z-10 w-full bg-white dark:bg-input-dark shadow-lg rounded-b-lg max-h-60 overflow-y-auto hidden"></div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="text-left text-sm text-gray-500">
                                <tr>
                                    <th class="p-2">Item</th>
                                    <th class="p-2 w-24">Jumlah</th>
                                    <th class="p-2 text-right">Harga Satuan</th>
                                    <th class="p-2 text-right">Subtotal</th>
                                    <th class="p-2"></th>
                                </tr>
                            </thead>
                            <tbody id="order-items-container">
                                <?php foreach($order_items as $item): 
                                    $subtotal = $item['jumlah'] * $item['harga_jual_per_item'];
                                    $unique_id = $item['item_id'] . '_' . $item['tipe_item'];
                                ?>
                                    <tr class="item-row border-t border-gray-200 dark:border-gray-700" data-subtotal="<?php echo $subtotal; ?>">
                                        <td class="p-2">
                                            <p class="font-semibold"><?php echo htmlspecialchars($item['nama_item']); ?></p>
                                            <p class="text-xs text-gray-500 capitalize"><?php echo htmlspecialchars($item['tipe_item']); ?></p>
                                            <input type="hidden" name="items[<?php echo $unique_id; ?>][tipe_item]" value="<?php echo htmlspecialchars($item['tipe_item']); ?>">
                                            <input type="hidden" name="items[<?php echo $unique_id; ?>][item_id]" value="<?php echo $item['item_id']; ?>">
                                            <input type="hidden" name="items[<?php echo $unique_id; ?>][nama_item]" value="<?php echo htmlspecialchars($item['nama_item']); ?>">
                                            <input type="hidden" name="items[<?php echo $unique_id; ?>][hpp_per_item]" value="<?php echo $item['hpp_per_item']; ?>">
                                            <input type="hidden" name="items[<?php echo $unique_id; ?>][harga_jual_per_item]" value="<?php echo $item['harga_jual_per_item']; ?>">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" name="items[<?php echo $unique_id; ?>][jumlah]" value="<?php echo $item['jumlah']; ?>" min="1" class="item-jumlah w-full h-10 p-2 rounded-md bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary text-center">
                                        </td>
                                        <td class="p-2 text-right text-sm">Rp <?php echo number_format($item['harga_jual_per_item'], 0, ',', '.'); ?></td>
                                        <td class="p-2 text-right font-semibold subtotal-display">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                        <td class="p-2 text-center">
                                            <button type="button" class="remove-item-btn text-red-500 font-bold text-xl">&times;</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p id="empty-item-msg" class="text-center text-gray-500 py-6" style="<?php if(!empty($order_items)) echo 'display:none;'; ?>">Belum ada item ditambahkan.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-subtle-dark p-4 rounded-lg shadow">
                    <h3 class="font-bold mb-4">Biaya Tambahan / Pengeluaran</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="text-left text-sm text-gray-500">
                                <tr>
                                    <th class="p-2">Deskripsi</th>
                                    <th class="p-2 w-40 text-right">Jumlah (Rp)</th>
                                    <th class="p-2"></th>
                                </tr>
                            </thead>
                            <tbody id="expense-items-container">
                                <?php foreach($order_expenses as $expense): ?>
                                    <tr class="expense-row border-t border-gray-200 dark:border-gray-700">
                                        <td class="p-2">
                                            <input type="text" name="expenses[deskripsi][]" class="w-full h-10 p-2 rounded-md bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" value="<?php echo htmlspecialchars($expense['deskripsi']); ?>" placeholder="Contoh: Transportasi">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" name="expenses[jumlah][]" class="expense-jumlah w-full h-10 p-2 rounded-md bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary text-right" value="<?php echo $expense['jumlah']; ?>" placeholder="10000">
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" class="remove-expense-btn text-red-500 font-bold text-xl">&times;</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="add-expense-btn" class="mt-4 text-sm font-semibold text-primary hover:underline">+ Tambah Biaya Lain</button>
                </div>

            </div>
        </form>
    </main>

    <footer class="fixed bottom-0 left-0 w-full bg-background-light dark:bg-background-dark border-t border-gray-200 dark:border-gray-700 z-20">
        <div class="max-w-3xl mx-auto p-4">
            <div class="flex justify-between items-center bg-primary/10 text-primary p-3 rounded-lg">
                <span class="font-bold">Total Penjualan:</span>
                <span id="grand-total" class="font-bold text-xl">Rp 0</span>
            </div>
            <button type="submit" form="form-pesanan" class="w-full mt-3 h-12 px-5 rounded-full bg-primary text-white font-bold tracking-wide shadow-lg hover:bg-opacity-90">
                Simpan Pesanan
            </button>
        </div>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Definisi Variabel DOM
    const customerNameInput = document.getElementById('nama_pelanggan');
    const customerIdInput = document.getElementById('customer_id');
    const customerContactInput = document.getElementById('kontak_pelanggan');
    const customerAddressInput = document.getElementById('alamat_pengiriman');
    const customerSuggestionsBox = document.getElementById('customer-suggestions');
    
    const searchInput = document.getElementById('search-item');
    const suggestionsBox = document.getElementById('suggestions-box');
    const itemsContainer = document.getElementById('order-items-container');
    const emptyMsg = document.getElementById('empty-item-msg');
    const grandTotalEl = document.getElementById('grand-total');
    const expenseContainer = document.getElementById('expense-items-container');
    const addExpenseBtn = document.getElementById('add-expense-btn');

    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

    // --- 1. Logika Pencarian Pelanggan ---
    customerNameInput.addEventListener('keyup', async function() {
        const query = this.value.trim();
        customerIdInput.value = ''; // Reset ID jika user mengetik ulang
        if (query.length < 2) {
            customerSuggestionsBox.classList.add('hidden');
            return;
        }
        try {
            const response = await fetch(`ajax/ajax_customers.php?q=${encodeURIComponent(query)}`);
            const customers = await response.json();
            if (customers.length > 0) {
                customerSuggestionsBox.innerHTML = customers.map(cust => 
                    `<div class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer customer-suggestion-item border-b border-gray-100 dark:border-gray-600" data-customer='${JSON.stringify(cust)}'>
                        <p class="font-semibold text-sm">${cust.nama_pelanggan}</p>
                        <p class="text-xs text-gray-500">${cust.kontak || '-'} | ${cust.alamat || '-'}</p>
                    </div>`
                ).join('');
                customerSuggestionsBox.classList.remove('hidden');
            } else {
                customerSuggestionsBox.classList.add('hidden');
            }
        } catch (error) { console.error('Error fetching customers:', error); }
    });

    customerSuggestionsBox.addEventListener('click', function(e) {
        const itemDiv = e.target.closest('.customer-suggestion-item');
        if (itemDiv) {
            const customer = JSON.parse(itemDiv.dataset.customer);
            customerNameInput.value = customer.nama_pelanggan;
            customerIdInput.value = customer.id;
            customerContactInput.value = customer.kontak;
            customerAddressInput.value = customer.alamat;
            customerSuggestionsBox.classList.add('hidden');
        }
    });

    // --- 2. Logika Pencarian Item (Resep / Paket) ---
    searchInput.addEventListener('keyup', async function() {
        const query = this.value.trim();
        if (query.length < 2) {
            suggestionsBox.classList.add('hidden');
            return;
        }
        try {
            const response = await fetch(`ajax/ajax_order_items.php?q=${encodeURIComponent(query)}`);
            const items = await response.json();
            if (items.length > 0) {
                suggestionsBox.innerHTML = items.map(item => `
                    <div class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer item-suggestion border-b border-gray-100 dark:border-gray-600" data-item='${JSON.stringify(item)}'>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-sm">${item.nama}</p>
                                <span class="text-xs text-gray-500 capitalize bg-gray-200 dark:bg-gray-600 px-1 rounded">${item.tipe}</span>
                            </div>
                            <p class="font-bold text-sm text-primary">${formatRupiah(item.harga_jual)}</p>
                        </div>
                    </div>
                `).join('');
                suggestionsBox.classList.remove('hidden');
            } else {
                suggestionsBox.classList.add('hidden');
            }
        } catch (error) { console.error('Error fetching items:', error); }
    });

    suggestionsBox.addEventListener('click', function(e) {
        const itemDiv = e.target.closest('.item-suggestion');
        if (itemDiv) {
            const itemData = JSON.parse(itemDiv.dataset.item);
            addItemToTable(itemData);
            searchInput.value = '';
            suggestionsBox.classList.add('hidden');
        }
    });

    // --- 3. Fungsi Tambah Item ke Tabel ---
    function addItemToTable(item) {
        emptyMsg.style.display = 'none';
        
        // Generate ID unik untuk array PHP
        const uniqueId = `${item.id}_${item.tipe}_${Date.now()}`; 
        const subtotal = item.harga_jual * 1; 

        const row = document.createElement('tr');
        row.className = 'item-row border-t border-gray-200 dark:border-gray-700';
        row.dataset.subtotal = subtotal;

        row.innerHTML = `
            <td class="p-2">
                <p class="font-semibold text-sm">${item.nama}</p>
                <p class="text-xs text-gray-500 capitalize">${item.tipe}</p>
                <input type="hidden" name="items[${uniqueId}][tipe_item]" value="${item.tipe}">
                <input type="hidden" name="items[${uniqueId}][item_id]" value="${item.id}">
                <input type="hidden" name="items[${uniqueId}][nama_item]" value="${item.nama}">
                <input type="hidden" name="items[${uniqueId}][hpp_per_item]" value="${item.hpp}">
                <input type="hidden" name="items[${uniqueId}][harga_jual_per_item]" value="${item.harga_jual}">
            </td>
            <td class="p-2">
                <input type="number" name="items[${uniqueId}][jumlah]" value="1" min="1" class="item-jumlah w-full h-10 p-2 rounded-md bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary text-center">
            </td>
            <td class="p-2 text-right text-sm">
                ${formatRupiah(item.harga_jual)}
            </td>
            <td class="p-2 text-right font-semibold subtotal-display">
                ${formatRupiah(subtotal)}
            </td>
            <td class="p-2 text-center">
                <button type="button" class="remove-item-btn text-red-500 font-bold text-xl hover:text-red-700">&times;</button>
            </td>
        `;

        itemsContainer.appendChild(row);
        addRowEventListeners(row);
        updateGrandTotal();
    }

    // --- 4. Fungsi Event Listener Row (Update Jumlah & Hapus) ---
    function addRowEventListeners(row) {
        const jumlahInput = row.querySelector('.item-jumlah');
        const removeBtn = row.querySelector('.remove-item-btn');
        const subtotalDisplay = row.querySelector('.subtotal-display');
        const hargaSatuan = parseFloat(row.querySelector('input[name*="[harga_jual_per_item]"]').value);

        jumlahInput.addEventListener('input', function() {
            const jumlah = parseInt(this.value) || 0;
            const newSubtotal = jumlah * hargaSatuan;
            row.dataset.subtotal = newSubtotal;
            subtotalDisplay.textContent = formatRupiah(newSubtotal);
            updateGrandTotal();
        });

        removeBtn.addEventListener('click', function() {
            row.remove();
            updateGrandTotal();
        });
    }

    // --- 5. Fungsi Hitung Total & Utilitas Lain ---
    function checkEmpty(){
        const rowCount = itemsContainer.querySelectorAll('.item-row').length;
        emptyMsg.style.display = rowCount > 0 ? 'none' : 'block';
    }

    function updateGrandTotal() {
        let total = 0;
        
        // Hitung total dari item
        itemsContainer.querySelectorAll('.item-row').forEach(row => {
            total += parseFloat(row.dataset.subtotal) || 0;
        });

        // Hitung total dari biaya tambahan (expenses)
        expenseContainer.querySelectorAll('.expense-jumlah').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        grandTotalEl.textContent = formatRupiah(total);
        checkEmpty();
    }

    // Biaya Tambahan
    addExpenseBtn.addEventListener('click', function() {
        const row = document.createElement('tr');
        row.className = 'expense-row border-t border-gray-200 dark:border-gray-700';
        row.innerHTML = `
            <td class="p-2">
                <input type="text" name="expenses[deskripsi][]" class="w-full h-10 p-2 rounded-md bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary" placeholder="Deskripsi">
            </td>
            <td class="p-2">
                <input type="number" name="expenses[jumlah][]" class="expense-jumlah w-full h-10 p-2 rounded-md bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary text-right" placeholder="0">
            </td>
            <td class="p-2 text-center">
                <button type="button" class="remove-expense-btn text-red-500 font-bold text-xl hover:text-red-700">&times;</button>
            </td>
        `;
        expenseContainer.appendChild(row);
        
        // Listener untuk baris expense baru
        row.querySelector('.expense-jumlah').addEventListener('input', updateGrandTotal);
        row.querySelector('.remove-expense-btn').addEventListener('click', function() {
            row.remove();
            updateGrandTotal();
        });
    });

    // Pasang listener untuk elemen expense yang sudah ada (saat edit)
    document.querySelectorAll('.expense-row').forEach(row => {
        const input = row.querySelector('.expense-jumlah');
        const btn = row.querySelector('.remove-expense-btn');
        if(input) input.addEventListener('input', updateGrandTotal);
        if(btn) btn.addEventListener('click', () => { row.remove(); updateGrandTotal(); });
    });

    // Pasang listener untuk item yang sudah ada (saat edit)
    document.querySelectorAll('.item-row').forEach(row => {
        addRowEventListeners(row);
    });

    // Tutup suggestion box jika klik di luar
    document.addEventListener('click', function(event) {
        if (!customerSuggestionsBox.contains(event.target) && event.target !== customerNameInput) {
            customerSuggestionsBox.classList.add('hidden');
        }
        if (!suggestionsBox.contains(event.target) && event.target !== searchInput) {
            suggestionsBox.classList.add('hidden');
        }
    });
    
    // Inisialisasi awal
    updateGrandTotal();
});
</script>

</body>
</html>
