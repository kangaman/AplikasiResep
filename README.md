# CatatRasa - Aplikasi Manajemen Resep & HPP

![Cover CatatRasa](assets/og-cover-catatanrasa.jpg)

**CatatRasa** adalah aplikasi web yang dirancang untuk membantu para pelaku usaha kuliner rumahan, katering, atau siapa saja yang hobi memasak, untuk mengelola resep, menghitung Harga Pokok Penjualan (HPP) secara akurat, dan mengatur pesanan dengan lebih efisien.

Aplikasi ini dibangun menggunakan **PHP** asli (native) dengan database **MySQL**, dan antarmuka pengguna yang modern menggunakan **Tailwind CSS**.

---

## ✨ Fitur Utama

Aplikasi ini dilengkapi dengan berbagai fitur untuk menyederhanakan manajemen dapur Anda:

* **Manajemen Resep Digital**:
    * Buat, edit, dan simpan resep tanpa batas.
    * Lengkapi resep dengan langkah-langkah detail, deskripsi, porsi default, kategori, hingga link video YouTube.
    * Unggah foto hasil masakan untuk setiap resep.

* **Manajemen Bahan Baku**:
    * Catat semua bahan dasar yang Anda miliki.
    * Simpan informasi harga beli dan satuan untuk setiap bahan.
    * Fitur pencarian bahan yang cepat saat membuat resep baru.

* **Kalkulator HPP Otomatis**:
    * **Harga Pokok Penjualan (HPP)** dihitung secara *real-time* berdasarkan harga bahan baku terkini.
    * Sesuaikan **Biaya Overhead** (listrik, gas, dll.) dalam persentase (%) melalui halaman pengaturan untuk perhitungan HPP yang lebih akurat.
    * Kalkulator harga jual interaktif untuk menentukan margin keuntungan dalam Persen (%) atau Rupiah (Rp).

* **Manajemen Pesanan & Pelanggan**:
    * Catat pesanan yang masuk dengan detail pelanggan, tanggal pengiriman, dan item yang dipesan.
    * Kelola status pesanan (Baru, Diproses, Selesai, Dibatalkan).
    * Secara otomatis menghitung total penjualan, modal, dan keuntungan untuk setiap pesanan.

* **Fitur Pendukung Bisnis**:
    * **Kalkulator Snack Box**: Buat paket snack box dengan mudah dan hitung HPP totalnya secara otomatis.
    * **Daftar Belanja Interaktif**: Tambahkan bahan dari resep ke daftar belanja secara otomatis, atau tambahkan item secara manual.
    * **Ringkasan Bisnis**: Lihat ringkasan pendapatan, keuntungan, dan jumlah pesanan di halaman Dashboard.

---

## 🛠️ Teknologi yang Digunakan

* **Backend**: PHP 8+ (Native, Prosedural & OOP)
* **Database**: MySQL / MariaDB
* **Frontend**: HTML, Tailwind CSS, JavaScript (Vanilla JS)
* **Fitur Tambahan**:
    * AJAX (Fetch API) untuk interaksi dinamis tanpa *reload* halaman.
    * GD Library untuk *image resizing* saat proses unggah.

---

## 🚀 Instalasi & Konfigurasi

Untuk menjalankan aplikasi ini di lingkungan lokal (misalnya menggunakan XAMPP):

1.  **Clone Repositori**:
    ```bash
    git clone [https://github.com/nama-anda/nama-repositori.git](https://github.com/nama-anda/nama-repositori.git)
    ```

2.  **Database**:
    * Buat sebuah database baru di phpMyAdmin.
    * Impor file `database.sql` (jika tersedia) atau buat tabel secara manual sesuai struktur yang dibutuhkan.

3.  **Konfigurasi Koneksi**:
    * Buka file `config/config.php`.
    * Sesuaikan variabel `$db_host`, `$db_user`, `$db_pass`, dan `$db_name` dengan konfigurasi database Anda.

    ```php
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'catatrasa_db';
    ```

4.  **Jalankan Aplikasi**:
    * Letakkan folder proyek di dalam direktori `htdocs` XAMPP Anda.
    * Buka browser dan akses `http://localhost/nama-folder-proyek/`.

---

## 🖼️ Tampilan Aplikasi

| Halaman Dashboard | Detail Resep & Kalkulator |
| :---: | :---: |
| _\[Screenshot Dashboard Anda]_ | _\[Screenshot Halaman Detail Resep Anda]_ |

| Manajemen Bahan | Form Resep |
| :---: | :---: |
| _\[Screenshot Manajemen Bahan]_ | _\[Screenshot Form Resep]_ |

**Tips**: Ganti `_[Screenshot ... Anda]_` dengan gambar nyata dari aplikasi Anda untuk membuatnya lebih menarik. Anda bisa mengambil screenshot, menambahkannya ke dalam repositori (misalnya di folder `assets/screenshots/`), lalu menampilkannya menggunakan sintaks Markdown: `![Deskripsi Gambar](assets/screenshots/dashboard.png)`
