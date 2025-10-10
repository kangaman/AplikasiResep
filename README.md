# CatatRasa - Aplikasi Manajemen Resep & HPP untuk Usaha Kuliner

![Cover CatatRasa](assets/og-cover-catatanrasa.jpg)

**CatatRasa** adalah aplikasi web komprehensif yang dirancang khusus untuk para pelaku usaha kuliner (rumahan, katering, UMKM) dan hobiis masak yang serius. Aplikasi ini berfungsi sebagai asisten dapur digital, membantu Anda mencatat resep secara detail, menghitung Harga Pokok Penjualan (HPP) secara akurat, hingga mengelola pesanan dengan efisien.

Dibangun dengan **PHP Native** dan **MySQL**, aplikasi ini mengutamakan kemudahan penggunaan, kecepatan, dan keamanan data tanpa bergantung pada *framework* yang berat.

[🔗 **Lihat Demo Langsung**](#) *(Ganti `(#)` dengan link demo Anda jika ada)*

---

## ✨ Filosofi & Fitur Unggulan

Tujuan utama CatatRasa adalah memberikan kontrol penuh atas aspek finansial dan operasional dapur Anda.

#### 📈 **Fokus pada Profitabilitas**
* **Kalkulator HPP Real-time**: Secara otomatis menghitung modal (HPP) untuk setiap resep berdasarkan harga bahan baku terkini. Anda tidak akan pernah salah menentukan harga jual lagi.
* **Alokasi Biaya Overhead**: Masukkan biaya tak terduga (listrik, gas, air, dll.) dalam bentuk persentase (%) untuk mendapatkan perhitungan HPP yang sesungguhnya.
* **Analisis Keuntungan**: Tentukan margin keuntungan dalam **Persen (%)** atau **Rupiah (Rp)**. Lihat langsung berapa keuntungan per porsi dan total keuntungan dari sebuah resep.

#### 🗂️ **Manajemen yang Terorganisir**
* **Database Resep & Bahan**: Simpan semua resep dan bahan baku Anda di satu tempat yang aman dan mudah diakses.
* **Manajemen Pesanan**: Catat pesanan dari pelanggan, kelola statusnya (Baru, Diproses, Selesai, Dibatalkan), dan lihat riwayat pesanan dengan mudah.
* **Kalkulator Snack Box**: Rancang paket menu (seperti snack box atau hampers) dan hitung total modalnya secara otomatis.
* **Daftar Belanja Cerdas**: Bahan-bahan dari resep yang akan dibuat bisa langsung ditambahkan ke daftar belanja, membantu Anda menyetok ulang bahan dengan efisien.

#### 🔐 **Keamanan Data**
* **Multi-User & Terisolasi**: Setiap pengguna memiliki data resep, bahan, dan pesanannya sendiri yang tidak bisa diakses oleh pengguna lain.
* **Praktik Keamanan Modern**: Dibangun dengan *Prepared Statements* untuk mencegah SQL Injection dan sanitasi output untuk mencegah XSS. Folder-folder sensitif juga dilindungi.

---

## 🛠️ Tumpukan Teknologi (Tech Stack)

* **Backend**: PHP 8+ (Native, Prosedural & OOP)
* **Database**: MySQL / MariaDB
* **Frontend**:
    * HTML5
    * Tailwind CSS (via CDN) untuk desain yang responsif dan modern.
    * JavaScript (Vanilla JS) untuk interaktivitas dinamis.
* **Server-Side**:
    * AJAX (Fetch API) untuk komunikasi *asynchronous* (misalnya, pencarian bahan tanpa *reload*).
    * GD Library (PHP Extension) untuk memproses dan mengubah ukuran gambar resep saat diunggah.

---

## 🚀 Panduan Instalasi (Lokal)

Untuk menjalankan aplikasi ini di komputer Anda menggunakan XAMPP:

1.  **Clone Repositori**
    ```bash
    git clone [https://github.com/nama-anda/catatrasa.git](https://github.com/nama-anda/catatrasa.git)
    cd catatrasa
    ```

2.  **Setup Database**
    * Buka phpMyAdmin (`http://localhost/phpmyadmin`).
    * Buat database baru, misalnya dengan nama `catatrasa_db`.
    * Impor file `database.sql` (jika Anda menyediakannya) ke dalam database yang baru dibuat.

3.  **Konfigurasi Koneksi**
    * Buka file `config/config.php`.
    * Sesuaikan detail koneksi database Anda:
    ```php
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = ''; // Biasanya kosong di XAMPP
    $db_name = 'catatrasa_db';
    ```

4.  **Jalankan Aplikasi**
    * Pindahkan folder proyek `catatrasa` ke dalam direktori `htdocs` di dalam folder instalasi XAMPP Anda.
    * Buka browser dan akses alamat `http://localhost/catatrasa/`.

---

## 📂 Struktur Folder Proyek

Berikut adalah penjelasan singkat mengenai struktur direktori utama aplikasi:

```
/
├── ajax/             # Berisi semua file PHP untuk menangani permintaan AJAX.
├── config/           # File konfigurasi koneksi database.
├── includes/         # Komponen PHP yang digunakan berulang (header, footer, navigasi).
├── uploads/          # Tempat menyimpan gambar resep yang diunggah pengguna.
├── assets/           # Menyimpan aset statis seperti gambar, ikon, dan CSS/JS kustom.
├── index.php         # Halaman utama atau halaman login.
├── dashboard.php     # Halaman utama setelah pengguna login.
└── ...               # File-file halaman utama lainnya.
```

---

## 🖼️ Tampilan Aplikasi

| Halaman Dashboard | Detail Resep & Kalkulator |
| :---: | :---: |
| _\[Screenshot Dashboard Anda]_ | _\[Screenshot Halaman Detail Resep Anda]_ |

| Manajemen Bahan | Form Resep dengan Pencarian Bahan |
| :---: | :---: |
| _\[Screenshot Manajemen Bahan]_ | _\[Screenshot Form Resep]_ |

*(**Tips**: Ganti placeholder di atas dengan gambar nyata dari aplikasi Anda untuk membuatnya lebih menarik!)*

---

## 🤝 Berkontribusi

Kontribusi untuk pengembangan CatatRasa sangat diterima! Jika Anda menemukan bug atau memiliki ide untuk fitur baru, silakan buka **Issue**. Jika Anda ingin berkontribusi langsung pada kode, silakan buat **Pull Request**.

1.  Fork repositori ini.
2.  Buat branch fitur baru (`git checkout -b fitur/fitur-keren-baru`).
3.  Commit perubahan Anda (`git commit -m 'Menambahkan fitur keren baru'`).
4.  Push ke branch tersebut (`git push origin fitur/fitur-keren-baru`).
5.  Buka Pull Request.

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah Lisensi MIT. Lihat file `LICENSE` untuk detail lebih lanjut.
