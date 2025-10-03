# AplikasiResep
Selamat Datang di Revolusi Dapur Anda!
Catatan Rasa Digital bukan sekadar aplikasi resep biasa. Ini adalah ekosistem lengkap yang dirancang untuk mengubah cara Anda mengelola, menghitung, dan mengembangkan bisnis kuliner. Dari resep rumahan hingga pesanan katering skala besar, semua alat yang Anda butuhkan kini berada dalam satu platform yang terintegrasi.

Bab 1: Filosofi Aplikasi & Keunggulan Utama
1.1. Alur Kerja Inti: Dari Bahan Baku Menjadi Profit
Aplikasi ini bekerja dengan satu alur kerja yang logis dan fundamental:
Input Harga Bahan Baku: Anda mendaftarkan semua bahan mentah beserta harga belinya. Ini adalah fondasi dari semua perhitungan.
Buat Resep & Hitung HPP: Anda membuat resep menggunakan bahan-bahan yang sudah terdaftar. Sistem secara otomatis menghitung Harga Pokok Produksi (HPP) per resep dan per porsi.
Rakit & Jual: Anda dapat merakit resep-resep menjadi paket (seperti snack box), menentukan harga jual berdasarkan margin keuntungan, dan merencanakan produksi.

1.2. Keunggulan Kompetitif Anda
Presisi Finansial: Hilangkan tebak-tebakan. Ketahui HPP setiap produk hingga ke detail terkecil, memungkinkan Anda menetapkan harga jual yang kompetitif dan menguntungkan.
Efisiensi Operasional: Percepat proses perencanaan produksi. Buat daftar belanja untuk ratusan porsi hanya dalam hitungan detik.
Konsistensi Produk: Dengan resep digital yang terstandarisasi, siapa pun di tim Anda dapat menghasilkan produk dengan kualitas yang sama setiap saat.
Inovasi Tanpa Batas: Gunakan fitur Kalkulator Olahan dan Snack Box untuk bereksperimen dengan produk dan paket baru tanpa risiko finansial.

Bab 2: Panduan Fitur Mendalam (Langkah demi Langkah)
Mari kita bedah setiap fitur utama dan cara menggunakannya secara maksimal.

2.1. Manajemen Bahan: Fondasi Bisnis Anda
Halaman ini adalah "gudang digital" Anda. Semua perhitungan di aplikasi ini bergantung pada data yang Anda masukkan di sini.

Cara Menggunakan:
Buka menu Bahan (manajemen_bahan.php).
Gunakan formulir "Tambah Bahan Baru" untuk mendaftarkan setiap bahan mentah.
Nama Bahan: Tulis dengan jelas (contoh: "Tepung Terigu Segitiga Biru").
Kategori: Pilih kategori yang sesuai untuk mempermudah pencarian.
Jumlah Beli & Satuan Beli: Masukkan sesuai kemasan yang Anda beli (contoh: Jumlah 1000, Satuan gram untuk terigu 1kg).
Harga Beli: Masukkan harga total untuk kemasan tersebut (contoh: 13000).
Klik Simpan Bahan. Ulangi untuk semua bahan Anda, termasuk bahan non-pangan seperti "Gas", "Dus Snackbox", atau "Plastik OPP".
Edit/Hapus: Anda dapat dengan mudah memperbarui harga atau menghapus bahan melalui daftar di bagian bawah halaman.
Pro Tip: Selalu perbarui harga bahan di halaman ini setiap kali Anda selesai berbelanja agar HPP resep Anda tetap akurat.

2.2. Form Resep: Dapur Digital Anda
Di sinilah ide-ide kuliner Anda diwujudkan dalam angka.

Cara Menggunakan:
Buka menu Tambah Resep (form_resep.php).
Isi informasi dasar: Nama Resep dan Hasil Jadi (Porsi).

Menambahkan Bahan:
Di kolom "Cari & Tambah Bahan", mulailah mengetik nama bahan yang sudah Anda daftarkan di Manajemen Bahan.
Pilih bahan dari daftar saran yang muncul.
Sebuah baris baru akan ditambahkan. Masukkan Jumlah yang dibutuhkan untuk resep ini (contoh: 250 gram).
Perhatikan kolom Harga di sebelah kanan. Angka ini dihitung secara otomatis oleh sistem!
Detail Tambahan: Isi langkah-langkah pembuatan, unggah foto produk, dan tambahkan link video YouTube jika ada.
Klik Simpan Resep.

2.3. Detail Resep: Analisis & Perencanaan
Setelah resep disimpan, Anda akan diarahkan ke halaman detailnya (detail_resep.php). Ini adalah pusat analisis Anda.

Fitur di Halaman Ini:
Hitung Ulang Porsi (Scaling): Ingin membuat 200 porsi untuk pesanan? Cukup masukkan angka 200 di kolom "Hitung Ulang Porsi". Semua takaran bahan di bawahnya akan otomatis dikalikan, dan total modal akan diperbarui.

Kalkulator Harga Jual:
Masukkan Margin Keuntungan yang Anda inginkan.
Pilih tipe margin: % (persentase) atau Rp (nominal Rupiah).
Sistem akan langsung menampilkan Modal/Porsi, Untung/Porsi, dan Harga Jual yang direkomendasikan.
Daftar Belanja Cerdas: Setelah mengatur jumlah porsi yang akan diproduksi, klik tombol "Tambahkan ke Daftar Belanja". Semua kebutuhan bahan akan otomatis ditambahkan ke halaman Daftar Belanja.

2.4. Kalkulator Bahan Olahan: Resep di Dalam Resep
Fitur canggih ini memungkinkan Anda membuat komponen resep yang biayanya sudah dihitung, lalu menggunakannya sebagai satu bahan tunggal di resep lain.
Studi Kasus: Membuat "Isian Sosis Solo"
Buka menu Kalkulator Olahan (kalkulator_bahan.php).
Di tabel komposisi, tambahkan semua bahan untuk membuat isian: Ayam Fillet, Santan, Royko, Gas, Jasa, dll. Sistem akan menghitung total biaya modalnya.
Di bagian "Simpan Hasil":
Nama Bahan Olahan: Isian Sosis Solo
Hasil Jadi: 67 (misalnya, adonan isian ini bisa untuk 67 buah sosis solo)
Satuan Hasil: pack (atau porsi)
Klik Simpan Bahan Olahan.
Sekarang, "Isian Sosis Solo" akan muncul sebagai bahan baru di Manajemen Bahan dan siap digunakan di resep "Sosis Solo" Anda.
2.5. Kalkulator Snack Box: Merakit Paket Penjualan
Gabungkan beberapa produk jadi ke dalam satu paket dan hitung total HPP-nya.

Cara Menggunakan:
Buka menu Kalkulator SnackBox (snackbox.php).
Beri Nama Paket (contoh: "Paket Arisan Ceria") dan tentukan Harga Jual yang Anda inginkan.
Di kolom "Cari & Tambah Item", Anda bisa mencari resep (seperti "Onde-onde") atau bahan baku (seperti "Air Mineral Gelas") yang sudah Anda daftarkan.
Pilih item yang ingin dimasukkan ke dalam paket. Item tersebut akan ditambahkan ke tabel beserta HPP per satuannya.
Sistem akan otomatis menjumlahkan Total HPP untuk paket tersebut dan menampilkan Keuntungan serta Margin berdasarkan harga jual yang Anda tentukan.
Klik Simpan Paket Snack Box.

2.6. Manajemen Akun & Keamanan
Ganti Password (ganti_password.php): Jaga keamanan akun Anda dengan mengubah password secara berkala.
Lupa Password (lupa_password.php): Jika Anda lupa password, gunakan fitur ini untuk mendapatkan link reset melalui simulasi email.

Bab 5: Studi Kasus Lengkap - Dari Nol Hingga Pesanan Pertama
Mari kita simulasikan alur kerja untuk pesanan 50 "Paket Murah".
Persiapan Awal (Manajemen Bahan): Pastikan semua bahan seperti "Beras Ketan", "Ayam Fillet", "Daun Pisang", "Mineral Gelas Vit", dan "Dus Snackbox" sudah terdaftar dengan harga terbaru.
Buat Resep Komponen (Kalkulator Olahan): Buat bahan olahan bernama "Isian Lemper Ayam" yang terdiri dari ayam, bumbu, santan, dll. Simpan hasilnya.

Buat Resep Utama:
Buat resep "Lemper Ayam" menggunakan bahan "Beras Ketan" dan "Isian Lemper Ayam". Simpan.
Buat resep lain yang akan dimasukkan ke paket, misalnya "Bikang Mawar". Simpan.
Rakit Paket (Kalkulator Snack Box):
Buat paket baru bernama "Paket Murah".
Tambahkan item: 1x "Lemper Ayam", 1x "Bikang Mawar", dan 1x "Mineral Gelas Vit".
Sistem akan menunjukkan total HPP. Tentukan harga jual, misalnya Rp 12.000.
Simpan paket.
Eksekusi Pesanan (Detail Resep & Daftar Belanja):
Ada pesanan 50 box "Paket Murah".
Buka detail resep "Lemper Ayam", set porsi ke 50, lalu klik "Tambahkan ke Daftar Belanja".
Buka detail resep "Bikang Mawar", set porsi ke 50, lalu klik "Tambahkan ke Daftar Belanja".
Buka menu Daftar Belanja. Anda akan melihat akumulasi total semua bahan yang dibutuhkan untuk memproduksi 50 Lemper dan 50 Bikang. Anda juga butuh 50 Air Mineral, yang bisa ditambahkan manual atau dihitung terpisah.

Dengan alur ini, perencanaan produksi menjadi sangat cepat, akurat, dan bebas dari kesalahan manusia.
