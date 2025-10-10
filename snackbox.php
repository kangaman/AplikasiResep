<?php
include __DIR__ . '/config/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Snack Box</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "foreground-light": "#181411", "foreground-dark": "#f8f7f6", "subtle-light": "#f4f2f0", "subtle-dark": "#3a2e22", "input-light": "#f4f2f0", "input-dark": "#3a2d21" },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .detail-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-foreground-light dark:text-foreground-dark">
<?php include __DIR__ . '/includes/navigation.php'; ?>
<main class="container mx-auto p-4 space-y-8">
    <div class="bg-white dark:bg-subtle-dark p-6 rounded-lg shadow-lg max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold mb-4">Daftar Paket Snack Box</h2>
        <div id="daftar-paket-container" class="space-y-2">
            <p class="text-gray-500">Memuat...</p>
        </div>
    </div>

    <div class="bg-white dark:bg-subtle-dark p-6 rounded-lg shadow-lg max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 id="kalkulator-title" class="text-2xl font-bold">Buat Paket Baru</h2>
            <button id="btn-reset" class="text-sm font-semibold text-gray-600 hover:text-primary">Reset/Buat Baru</button>
        </div>
        
        <input type="hidden" id="paket-id">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="nama-paket" class="block text-sm font-medium mb-2">Nama Paket</label>
                <input type="text" id="nama-paket" placeholder="Contoh: Paket Hemat Pagi" class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                 <label for="harga-jual" class="block text-sm font-medium mb-2">Harga Jual per Box</label>
                <div class="relative"><span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">Rp</span>
                    <input type="number" id="harga-jual" value="11500" class="w-full h-12 pl-10 pr-2 text-right font-bold rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>
            </div>
        </div>

        <div class="relative mb-4">
            <label for="search-item" class="block text-sm font-medium mb-2">Cari & Tambah Item</label>
            <input type="text" id="search-item" placeholder="Ketik nama kue atau bahan..." class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
            <div id="suggestions-box" class="absolute z-10 w-full bg-white dark:bg-input-dark shadow-lg rounded-b-lg max-h-60 overflow-y-auto hidden"></div>
        </div>

        <div class="overflow-x-auto mb-6">
            <table class="w-full" id="tabel-item">
                <thead class="bg-subtle-light dark:bg-gray-800">
                    <tr>
                        <th class="p-3 text-left text-sm font-bold uppercase">Nama Item</th>
                        <th class="p-3 text-right text-sm font-bold uppercase">Modal (HPP)</th>
                        <th class="p-3 text-center text-sm font-bold uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <hr class="my-6 border-gray-300 dark:border-gray-600">

        <div>
            <h3 class="text-xl font-bold mb-4">Ringkasan per Box</h3>
            <div class="space-y-3 p-4 bg-background-light dark:bg-background-dark rounded-lg">
                <div class="flex justify-between items-center"><span class="font-medium text-gray-600 dark:text-gray-400">Total Modal (HPP)</span><span id="total-hpp" class="font-bold text-lg text-primary">Rp 0</span></div>
                <div class="flex justify-between items-center text-green-600"><span class="font-medium">Keuntungan</span><span id="keuntungan" class="font-bold text-lg">Rp 0</span></div>
                <div class="flex justify-between items-center text-sm text-gray-500"><span class="font-medium">Margin Keuntungan</span><span id="margin" class="font-bold">0%</span></div>
            </div>
        </div>
        
        <div class="mt-6">
            <button id="simpan-paket" class="w-full h-12 bg-green-600 text-white font-bold rounded-full shadow-lg hover:bg-green-700">Simpan Paket Snack Box</button>
        </div>

        <hr class="my-10 border-gray-300 dark:border-gray-600">
        <div>
            <h3 class="text-2xl font-bold mb-4">Simulasi Pesanan Total</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                <div>
                    <label for="jumlah-pesanan" class="block text-sm font-medium mb-2">Jumlah Pesanan (box)</label>
                    <input type="number" id="jumlah-pesanan" value="50" class="w-full h-12 px-4 text-lg font-bold rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input id="checkbox-transportasi" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <label for="checkbox-transportasi" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Tambahkan Biaya Transportasi?</label>
                    </div>
                     <div class="relative"><span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">Rp</span>
                        <input type="number" id="biaya-transportasi" value="25000" class="w-full h-10 pl-10 pr-2 text-right font-bold rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>
            <div class="mt-6 space-y-3 p-4 bg-blue-50 dark:bg-blue-900/50 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex justify-between items-center"><span class="font-medium text-gray-700 dark:text-gray-300">Total Modal Keseluruhan</span><span id="sim-total-modal" class="font-bold text-lg text-blue-600 dark:text-blue-400">Rp 0</span></div>
                <div class="flex justify-between items-center"><span class="font-medium text-gray-700 dark:text-gray-300">Total Pemasukan</span><span id="sim-total-pemasukan" class="font-bold text-lg text-blue-600 dark:text-blue-400">Rp 0</span></div>
                <div class="flex justify-between items-center text-green-600 dark:text-green-400"><span class="font-bold text-xl">TOTAL KEUNTUNGAN BERSIH</span><span id="sim-total-keuntungan" class="font-bold text-2xl">Rp 0</span></div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let state = { paketId: null, hppPerBox: 0 };
    
    const searchInput = document.getElementById('search-item');
    const suggestionsBox = document.getElementById('suggestions-box');
    const tabelBody = document.querySelector('#tabel-item tbody');
    const hargaJualInput = document.getElementById('harga-jual');
    const namaPaketInput = document.getElementById('nama-paket');
    const daftarPaketContainer = document.getElementById('daftar-paket-container');
    const kalkulatorTitle = document.getElementById('kalkulator-title');
    const jumlahPesananInput = document.getElementById('jumlah-pesanan');
    const checkboxTransportasi = document.getElementById('checkbox-transportasi');
    const biayaTransportasiInput = document.getElementById('biaya-transportasi');

    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

    function updateSimulasi() {
        const jumlahPesanan = parseInt(jumlahPesananInput.value) || 0;
        const hargaJual = parseFloat(hargaJualInput.value) || 0;
        
        if (jumlahPesanan > 40) {
            checkboxTransportasi.checked = true;
        }

        const biayaTransportasi = checkboxTransportasi.checked ? (parseFloat(biayaTransportasiInput.value) || 0) : 0;
        
        // --- LOGIKA PERHITUNGAN YANG DIPERBAIKI ---
        const totalModalPerBox = state.hppPerBox;
        const totalModalPesanan = (totalModalPerBox * jumlahPesanan) + biayaTransportasi;
        const totalPemasukan = hargaJual * jumlahPesanan;
        const totalKeuntungan = totalPemasukan - totalModalPesanan; // Ini adalah keuntungan bersih yang benar

        document.getElementById('sim-total-modal').textContent = formatRupiah(totalModalPesanan);
        document.getElementById('sim-total-pemasukan').textContent = formatRupiah(totalPemasukan);
        document.getElementById('sim-total-keuntungan').textContent = formatRupiah(totalKeuntungan);
    }

    function updateSummary() {
        state.hppPerBox = Array.from(tabelBody.querySelectorAll('tr')).reduce((acc, row) => acc + (parseFloat(row.dataset.hpp) || 0), 0);
        const hargaJual = parseFloat(hargaJualInput.value) || 0;
        const keuntungan = hargaJual - state.hppPerBox;
        const margin = state.hppPerBox > 0 ? (keuntungan / state.hppPerBox) * 100 : 0;

        document.getElementById('total-hpp').textContent = formatRupiah(state.hppPerBox);
        document.getElementById('keuntungan').textContent = formatRupiah(keuntungan);
        document.getElementById('margin').textContent = `${margin.toFixed(1)}%`;
        
        updateSimulasi();
        return state.hppPerBox;
    }

    // ... (Sisa kode JavaScript dari respons sebelumnya tidak berubah dan tetap valid)
    // Salin semua fungsi dan event listener lain dari kode sebelumnya ke sini
    function addItemToTable(item) {
        const row = document.createElement('tr');
        row.className = 'border-b border-gray-200 dark:border-gray-700';
        row.dataset.item = JSON.stringify(item);
        row.dataset.hpp = item.hpp;
        row.innerHTML = `<td class="p-3">${item.nama} <span class="text-xs ${item.tipe === 'resep' ? 'text-blue-500' : 'text-gray-500'}">(${item.tipe})</span></td><td class="p-3 text-right">${formatRupiah(item.hpp)}</td><td class="p-3 text-center"><button class="hapus-item text-red-500 font-bold text-lg">&times;</button></td>`;
        tabelBody.appendChild(row);
        row.querySelector('.hapus-item').addEventListener('click', () => { row.remove(); updateSummary(); });
        updateSummary();
    }
    
    function resetForm() {
        state.paketId = null;
        kalkulatorTitle.textContent = 'Buat Paket Baru';
        tabelBody.innerHTML = '';
        ['paket-id', 'nama-paket'].forEach(id => document.getElementById(id).value = '');
        hargaJualInput.value = '11500';
        updateSummary();
    }

    async function fetchDaftarPaket() {
        try {
            const res = await fetch('ajax/ajax_snackbox.php?action=get_list');
            const result = await res.json();
            if (result.status === 'success') {
                const list = result.data;
                daftarPaketContainer.innerHTML = list.length === 0 ? '<p class="text-gray-500">Belum ada paket yang disimpan.</p>' : list.map(item => `<div class="paket-item-container border border-transparent rounded-lg"><div class="flex justify-between items-center p-3 bg-subtle-light dark:bg-gray-800 rounded-lg cursor-pointer paket-header" data-id="${item.id}" data-nama-paket="${item.nama_paket}"><div class="flex-grow"><p class="font-semibold pointer-events-none">${item.nama_paket}</p><p class="text-xs text-gray-500 dark:text-gray-400 pointer-events-none">HPP: ${formatRupiah(item.total_hpp)} | Jual: ${formatRupiah(item.harga_jual)}</p></div><div class="flex-shrink-0"><button class="btn-edit text-sm text-primary font-bold mr-2" data-id="${item.id}">Edit</button><button class="btn-hapus text-sm text-red-500 font-bold" data-id="${item.id}">Hapus</button></div></div><div class="detail-content bg-background-light dark:bg-gray-800/50 rounded-b-lg"></div></div>`).join('');
            }
        } catch(e) { console.error(e); }
    }

    searchInput.addEventListener('keyup', async function() {
        const query = this.value.trim();
        if (query.length < 2) { suggestionsBox.classList.add('hidden'); return; }
        try {
            const res = await fetch(`ajax/ajax_snackbox.php?action=search_items&q=${encodeURIComponent(query)}`);
            const result = await res.json();
            if (result.status === 'success' && result.items.length > 0) {
                suggestionsBox.innerHTML = result.items.map(item => `<div class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer suggestion-item" data-item='${JSON.stringify(item)}'><p class="font-semibold">${item.nama}</p><small class="text-primary">${formatRupiah(item.hpp)} / pcs</small></div>`).join('');
                suggestionsBox.classList.remove('hidden');
            } else { suggestionsBox.classList.add('hidden'); }
        } catch (e) { console.error(e); suggestionsBox.classList.add('hidden'); }
    });

    suggestionsBox.addEventListener('click', function(e) {
        const itemDiv = e.target.closest('.suggestion-item');
        if (itemDiv) {
            addItemToTable(JSON.parse(itemDiv.dataset.item));
            searchInput.value = '';
            suggestionsBox.classList.add('hidden');
        }
    });

    daftarPaketContainer.addEventListener('click', async e => {
        const target = e.target;
        const header = target.closest('.paket-header');
        const id = target.dataset.id || (header ? header.dataset.id : null);
        if (!id) return;
        const container = target.closest('.paket-item-container');
        const nama = container.querySelector('.paket-header').dataset.namaPaket;

        if (target.classList.contains('btn-edit')) {
            const res = await fetch(`ajax/ajax_snackbox.php?action=get_detail&id=${id}`);
            const result = await res.json();
            if (result.status === 'success') {
                const data = result.data;
                resetForm();
                state.paketId = id;
                kalkulatorTitle.textContent = `Edit: ${data.nama_paket}`;
                namaPaketInput.value = data.nama_paket;
                hargaJualInput.value = data.harga_jual;
                tabelBody.innerHTML = ''; // Pastikan tabel dikosongkan
                data.isi.forEach(item => addItemToTable({nama: item.nama_item, tipe: item.tipe_item, hpp: item.hpp_per_item}));
            }
        } else if (target.classList.contains('btn-hapus')) {
            if (confirm(`Yakin ingin menghapus paket "${nama}"?`)) {
                const res = await fetch('ajax/ajax_snackbox.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'delete_paket', id: id}) });
                const result = await res.json();
                alert(result.message);
                if (result.status === 'success') {
                    fetchDaftarPaket();
                    if (state.paketId == id) resetForm();
                }
            }
        } else if(header) {
             const detailContent = header.parentElement.nextElementSibling;
            if (detailContent.style.maxHeight) {
                detailContent.style.maxHeight = null;
                detailContent.innerHTML = '';
            } else {
                document.querySelectorAll('.detail-content').forEach(el => { el.style.maxHeight = null; el.innerHTML = ''; });
                detailContent.innerHTML = '<p class="p-4 text-sm text-gray-500">Memuat rincian...</p>';
                const res = await fetch(`ajax/ajax_snackbox.php?action=get_detail&id=${id}`);
                const result = await res.json();
                if (result.status === 'success') {
                    const isi = result.data.isi;
                    if (isi.length > 0) {
                        detailContent.innerHTML = isi.map(item => `<div class="flex justify-between items-center text-sm px-4 py-2 border-t border-gray-200 dark:border-gray-700"><span>${item.nama_item}</span><span class="font-medium">${formatRupiah(item.hpp_per_item)}</span></div>`).join('');
                    } else {
                        detailContent.innerHTML = '<p class="p-4 text-sm text-gray-500">Paket ini tidak memiliki isi.</p>';
                    }
                    detailContent.style.maxHeight = detailContent.scrollHeight + "px";
                }
            }
        }
    });

    document.getElementById('simpan-paket').addEventListener('click', async () => {
        const isi = Array.from(tabelBody.querySelectorAll('tr')).map(row => JSON.parse(row.dataset.item));
        const payload = { action: 'save_paket', id: state.paketId, nama_paket: namaPaketInput.value, harga_jual: parseFloat(hargaJualInput.value), total_hpp: updateSummary(), isi: isi };
        if (!payload.nama_paket || !payload.harga_jual || payload.isi.length === 0) {
            alert('Harap isi nama paket, harga jual, dan tambahkan minimal satu item.');
            return;
        }
        const res = await fetch('ajax/ajax_snackbox.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
        const result = await res.json();
        alert(result.message);
        if (result.status === 'success') {
            resetForm();
            fetchDaftarPaket();
        }
    });
    
    [hargaJualInput, jumlahPesananInput, checkboxTransportasi, biayaTransportasiInput].forEach(el => el.addEventListener('input', updateSimulasi));
    
    fetchDaftarPaket();
    updateSummary();
});
</script>
</body>
</html>