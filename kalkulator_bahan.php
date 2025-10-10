<?php
include __DIR__ . '/config/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$data_harga_dasar = $conn->query("SELECT nama_bahan as nama, jumlah_beli as jumlah, satuan_beli as satuan, harga_beli as harga FROM base_ingredients WHERE user_id = $user_id ORDER BY nama_bahan ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Bahan Olahan</title>
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
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-foreground-light dark:text-foreground-dark">
<?php include __DIR__ . '/includes/navigation.php'; ?>

<main class="container mx-auto p-4 space-y-8">
    <div class="bg-white dark:bg-subtle-dark p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-4">Daftar Bahan Olahan Tersimpan</h2>
        <div id="daftar-olahan-container" class="space-y-2">
            <p class="text-gray-500">Memuat...</p>
        </div>
    </div>

    <div class="bg-white dark:bg-subtle-dark p-6 rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-4">
            <h2 id="kalkulator-title" class="text-2xl font-bold">Buat Bahan Olahan Baru</h2>
            <button id="btn-reset" class="text-sm font-semibold text-gray-600 hover:text-primary">Reset/Buat Baru</button>
        </div>
        <p class="text-sm text-gray-500 mb-6">Gunakan menu ini untuk menghitung biaya modal dari sebuah komponen resep. Hasilnya bisa disimpan sebagai bahan baru.</p>
        
        <input type="hidden" id="olahan-id" value="">
        
        <div class="overflow-x-auto">
            <table class="w-full" id="tabel-bahan">
                <thead class="bg-subtle-light dark:bg-gray-800">
                    <tr>
                        <th class="p-3 text-left text-sm font-bold uppercase">Nama Bahan</th>
                        <th class="p-3 text-left text-sm font-bold uppercase">Jumlah</th>
                        <th class="p-3 text-left text-sm font-bold uppercase">Satuan</th>
                        <th class="p-3 text-left text-sm font-bold uppercase">Biaya</th>
                        <th class="p-3 text-center text-sm font-bold uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <button id="tambah-baris" class="mt-4 bg-primary text-white font-bold px-4 py-2 rounded-full text-sm">+ Tambah Bahan</button>
        
        <hr class="my-6 border-gray-300 dark:border-gray-600">

        <div>
            <h3 class="text-xl font-bold mb-4">Hasil Perhitungan</h3>
            <div class="bg-subtle-light dark:bg-gray-800 p-4 rounded-lg flex justify-between items-center">
                <span class="font-bold text-lg">Total Biaya Modal</span>
                <span id="total-biaya" class="font-bold text-2xl text-primary">Rp 0</span>
            </div>
        </div>

        <hr class="my-6 border-gray-300 dark:border-gray-600">

        <div>
            <h3 class="text-xl font-bold mb-4">Simpan Hasil</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="nama-olahan" class="block text-sm font-medium mb-2">Nama Bahan Olahan</label>
                    <input type="text" id="nama-olahan" placeholder="Contoh: Isian Ayam" class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label for="hasil-jadi" class="block text-sm font-medium mb-2">Hasil Jadi (Jumlah)</label>
                    <input type="number" id="hasil-jadi" placeholder="Contoh: 500" class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label for="satuan-hasil" class="block text-sm font-medium mb-2">Satuan Hasil</label>
                    <input type="text" id="satuan-hasil" placeholder="Contoh: gram" class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <button id="simpan-bahan-olahan" class="w-full mt-4 h-12 bg-green-600 text-white font-bold rounded-full shadow-lg hover:bg-green-700">Simpan Bahan Olahan</button>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dataBahanDasar = <?php echo json_encode($data_harga_dasar); ?>;
    let state = { olahanId: null };

    const tabelBody = document.querySelector('#tabel-bahan tbody');
    const totalBiayaEl = document.getElementById('total-biaya');
    const daftarOlahanContainer = document.getElementById('daftar-olahan-container');
    const kalkulatorTitle = document.getElementById('kalkulator-title');

    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    
    // Fungsi untuk memformat angka biasa (non-Rupiah)
    const formatNumber = (angka) => new Intl.NumberFormat('id-ID').format(angka);

    function hitungTotalBiaya() {
        let total = Array.from(tabelBody.querySelectorAll('tr')).reduce((acc, row) => {
            const biayaText = row.querySelector('.biaya-bahan').textContent;
            const biaya = parseFloat(biayaText.replace(/[^0-9,-]+/g, "").replace(",", ".")) || 0;
            return acc + biaya;
        }, 0);
        totalBiayaEl.textContent = formatRupiah(total);
        return total;
    }

    function hitungBiayaBaris(row) {
        const selectBahan = row.querySelector('.nama-bahan');
        const inputJumlah = row.querySelector('.jumlah-bahan');
        const biayaEl = row.querySelector('.biaya-bahan');
        const namaBahan = selectBahan.value;
        const jumlah = parseFloat(inputJumlah.value) || 0;
        const bahanTerpilih = dataBahanDasar.find(b => b.nama === namaBahan);
        if (bahanTerpilih && bahanTerpilih.jumlah > 0) {
            const hargaPerSatuan = bahanTerpilih.harga / bahanTerpilih.jumlah;
            biayaEl.textContent = formatRupiah(hargaPerSatuan * jumlah);
        } else {
            biayaEl.textContent = formatRupiah(0);
        }
        hitungTotalBiaya();
    }

    function tambahBaris(data = {}) {
        const row = document.createElement('tr');
        row.className = 'border-b border-gray-200 dark:border-gray-700';
        let optionsHtml = '<option value="">Pilih Bahan</option>';
        dataBahanDasar.forEach(bahan => {
            const isSelected = data.nama_bahan_dasar === bahan.nama ? 'selected' : '';
            optionsHtml += `<option value="${bahan.nama}" ${isSelected}>${bahan.nama}</option>`;
        });
        row.innerHTML = `
            <td class="p-2"><select class="nama-bahan w-full bg-transparent p-2 rounded">${optionsHtml}</select></td>
            <td class="p-2"><input type="number" class="jumlah-bahan w-24 bg-transparent p-2 rounded" placeholder="Jumlah" value="${data.jumlah || ''}"></td>
            <td class="p-2"><span class="satuan-bahan text-sm text-gray-500">${data.satuan || '-'}</span></td>
            <td class="p-2 biaya-bahan font-bold">${formatRupiah(0)}</td>
            <td class="p-2 text-center"><button class="hapus-baris text-red-500 font-bold">X</button></td>
        `;
        tabelBody.appendChild(row);

        const selectBahan = row.querySelector('.nama-bahan');
        selectBahan.addEventListener('change', function() {
            const bahanTerpilih = dataBahanDasar.find(b => b.nama === this.value);
            row.querySelector('.satuan-bahan').textContent = bahanTerpilih ? bahanTerpilih.satuan : '-';
            hitungBiayaBaris(row);
        });
        row.querySelector('.jumlah-bahan').addEventListener('input', () => hitungBiayaBaris(row));
        row.querySelector('.hapus-baris').addEventListener('click', () => { row.remove(); hitungTotalBiaya(); });
        
        if (data.nama_bahan_dasar) {
            selectBahan.dispatchEvent(new Event('change'));
        }
    }

    function resetForm() {
        state.olahanId = null;
        kalkulatorTitle.textContent = 'Buat Bahan Olahan Baru';
        document.getElementById('olahan-id').value = '';
        document.getElementById('nama-olahan').value = '';
        document.getElementById('hasil-jadi').value = '';
        document.getElementById('satuan-hasil').value = '';
        tabelBody.innerHTML = '';
        tambahBaris();
        hitungTotalBiaya();
    }

    async function fetchDaftarOlahan() {
        try {
            const res = await fetch('ajax/ajax_olahan.php?action=get_list');
            const result = await res.json();
            if (result.status === 'success') {
                const list = result.data;
                // --- BAGIAN YANG DIPERBARUI ---
                daftarOlahanContainer.innerHTML = list.length === 0
                    ? '<p class="text-gray-500">Belum ada bahan olahan yang disimpan.</p>'
                    : list.map(item => `
                        <div class="flex justify-between items-center p-3 bg-subtle-light dark:bg-gray-800 rounded" data-nama-olahan="${item.nama_olahan}">
                            <div class="flex-grow">
                                <p class="font-semibold">${item.nama_olahan}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">${formatRupiah(item.total_biaya)} / ${formatNumber(item.total_hasil)} ${item.satuan_hasil}</p>
                            </div>
                            <div class="flex-shrink-0">
                                <button class="btn-edit text-sm text-primary font-bold mr-2" data-id="${item.id}">Edit</button>
                                <button class="btn-duplikat text-sm text-green-600 font-bold mr-2" data-id="${item.id}">Duplikat</button>
                                <button class="btn-hapus text-sm text-red-500 font-bold" data-id="${item.id}">Hapus</button>
                            </div>
                        </div>`).join('');
                // --- AKHIR BAGIAN YANG DIPERBARUI ---
            }
        } catch (e) { console.error("Gagal memuat daftar olahan:", e); }
    }

    daftarOlahanContainer.addEventListener('click', async e => {
        const target = e.target;
        if (!target.matches('button')) return;

        const id = target.dataset.id;
        const parentDiv = target.closest('[data-nama-olahan]');
        const nama = parentDiv ? parentDiv.dataset.namaOlahan : '';

        if (target.classList.contains('btn-edit')) {
            const res = await fetch(`ajax/ajax_olahan.php?action=get_detail&id=${id}`);
            const result = await res.json();
            if(result.status === 'success') {
                const data = result.data;
                resetForm();
                state.olahanId = id;
                kalkulatorTitle.textContent = `Edit: ${data.nama_olahan}`;
                document.getElementById('nama-olahan').value = data.nama_olahan;
                document.getElementById('hasil-jadi').value = data.total_hasil;
                document.getElementById('satuan-hasil').value = data.satuan_hasil;
                tabelBody.innerHTML = '';
                (data.komposisi.length > 0 ? data.komposisi : [{}]).forEach(item => tambahBaris(item));
            }
        } else if (target.classList.contains('btn-hapus')) {
            if (confirm(`Yakin ingin menghapus "${nama}"?`)) {
                const res = await fetch('ajax_olahan.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'delete_olahan', id: id }) });
                const result = await res.json();
                alert(result.message);
                if (result.status === 'success') {
                    fetchDaftarOlahan();
                    if(state.olahanId == id) resetForm();
                }
            }
        } else if (target.classList.contains('btn-duplikat')) {
            if (confirm(`Yakin ingin menduplikasi "${nama}"?`)) {
                 const res = await fetch('ajax/ajax_olahan.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'duplicate_olahan', id: id }) });
                const result = await res.json();
                alert(result.message);
                if (result.status === 'success') {
                    fetchDaftarOlahan();
                }
            }
        }
    });

    document.getElementById('tambah-baris').addEventListener('click', () => tambahBaris());
    document.getElementById('btn-reset').addEventListener('click', resetForm);
    document.getElementById('simpan-bahan-olahan').addEventListener('click', async () => {
        const komposisi = Array.from(tabelBody.querySelectorAll('tr')).map(row => ({
            nama: row.querySelector('.nama-bahan').value,
            jumlah: row.querySelector('.jumlah-bahan').value,
            satuan: row.querySelector('.satuan-bahan').textContent
        })).filter(item => item.nama && item.jumlah > 0);

        const payload = {
            action: 'save_olahan',
            id: state.olahanId,
            nama_olahan: document.getElementById('nama-olahan').value,
            total_hasil: document.getElementById('hasil-jadi').value,
            satuan_hasil: document.getElementById('satuan-hasil').value,
            total_biaya: hitungTotalBiaya(),
            komposisi: komposisi
        };

        if (!payload.nama_olahan || !payload.total_hasil || !payload.satuan_hasil || payload.komposisi.length === 0) {
            alert('Harap isi semua field dan tambahkan minimal satu bahan komposisi.');
            return;
        }

        const res = await fetch('ajax/ajax_olahan.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const result = await res.json();
        alert(result.message);
        if (result.status === 'success') {
            resetForm();
            fetchDaftarOlahan();
        }
    });

    fetchDaftarOlahan();
    tambahBaris();
});
</script>
</body>
</html>