<?php

// Aktifkan kompresi output (mempercepat loading halaman)
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();

/**
 * PENGATURAN UNTUK LINGKUNGAN PRODUKSI (LIVE)
 * --------------------------------------------------
 * Kode di bawah ini akan menyembunyikan detail error PHP dari layar
 * dan memastikannya tetap tercatat di file log server. Ini penting
 * untuk keamanan aplikasi yang sudah launching.
 */
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// Pastikan path ke error_log sudah benar relatif dari file config.php
ini_set('error_log', __DIR__ . '/../error_log'); 


// ===================================================================
// KODE LAMA ANDA DIMULAI DARI SINI (TIDAK ADA YANG DIUBAH)
// ===================================================================

// Memulai sesi hanya jika belum ada yang memulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- SESUAIKAN DENGAN INFORMASI DATABASE ANDA ---
$db_host = 'localhost';
$db_user = 'sssss'; // Ganti dengan username database Anda
$db_pass = '7f{cB5sssS'; // Ganti dengan password database Anda
$db_name = 'sssss'; // Ganti dengan nama database Anda
// ----------------------------------------------------

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    error_log("Koneksi ke database gagal: " . $conn->connect_error);
    die("Terjadi masalah koneksi ke server. Silakan coba lagi nanti.");
}
?>
