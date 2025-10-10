<?php
// File ini akan memuat konfigurasi dan melakukan pengecekan sesi.
// Jika sesi tidak valid, pengguna akan langsung dialihkan.

// Memuat file konfigurasi utama (yang juga memulai sesi)
include_once __DIR__ . '/../config/config.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika belum, alihkan ke halaman login dan hentikan eksekusi
    header("Location: login.php");
    exit();
}

// Siapkan variabel user_id untuk digunakan di halaman lain
$user_id = $_SESSION['user_id'];
?>