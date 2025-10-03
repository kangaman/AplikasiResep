-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 03 Okt 2025 pada 09.57
-- Versi server: 10.6.23-MariaDB-cll-lve
-- Versi PHP: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `saefulba_resep`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bahan_olahan`
--

CREATE TABLE `bahan_olahan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_olahan` varchar(255) NOT NULL,
  `total_hasil` decimal(10,2) NOT NULL,
  `satuan_hasil` varchar(50) NOT NULL,
  `total_biaya` decimal(12,2) NOT NULL,
  `base_ingredient_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bahan_olahan`
--

INSERT INTO `bahan_olahan` (`id`, `user_id`, `nama_olahan`, `total_hasil`, `satuan_hasil`, `total_biaya`, `base_ingredient_id`) VALUES
(1, 1, 'isian sosis solo', 67.00, 'pack', 70380.00, 51),
(3, 1, 'isian semar mendem', 75.00, 'pack', 70380.00, 53);

-- --------------------------------------------------------

--
-- Struktur dari tabel `bahan_olahan_komposisi`
--

CREATE TABLE `bahan_olahan_komposisi` (
  `id` int(11) NOT NULL,
  `bahan_olahan_id` int(11) NOT NULL,
  `nama_bahan_dasar` varchar(255) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `satuan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bahan_olahan_komposisi`
--

INSERT INTO `bahan_olahan_komposisi` (`id`, `bahan_olahan_id`, `nama_bahan_dasar`, `jumlah`, `satuan`) VALUES
(41, 1, 'Ayam Fillet', 1000.00, 'gram'),
(42, 1, 'Santan Kara', 1.00, 'sachet'),
(43, 1, 'Royko Ayam', 2.50, 'sachet'),
(44, 1, 'Ladaku', 1.00, 'sachet'),
(45, 1, 'Bawang Merah Bawang Putih', 1.50, 'pack'),
(46, 1, 'Gas', 1.00, 'pack'),
(47, 1, 'Gula Pasir', 20.00, 'gram'),
(48, 1, 'Jasa', 0.50, 'pack'),
(57, 3, 'Ayam Fillet', 1000.00, 'gram'),
(58, 3, 'Santan Kara', 1.00, 'sachet'),
(59, 3, 'Royko Ayam', 2.50, 'sachet'),
(60, 3, 'Ladaku', 1.00, 'sachet'),
(61, 3, 'Bawang Merah Bawang Putih', 1.50, 'pack'),
(62, 3, 'Gas', 1.00, 'pack'),
(63, 3, 'Gula Pasir', 20.00, 'gram'),
(64, 3, 'Jasa', 0.50, 'pack');

-- --------------------------------------------------------

--
-- Struktur dari tabel `base_ingredients`
--

CREATE TABLE `base_ingredients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_bahan` varchar(255) NOT NULL,
  `kategori` varchar(100) DEFAULT 'Lain-lain',
  `jumlah_beli` decimal(10,2) NOT NULL,
  `satuan_beli` varchar(50) NOT NULL,
  `harga_beli` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `base_ingredients`
--

INSERT INTO `base_ingredients` (`id`, `user_id`, `nama_bahan`, `kategori`, `jumlah_beli`, `satuan_beli`, `harga_beli`) VALUES
(1, 1, 'Terigu Tulip', 'Bahan Kering', 1000.00, 'gram', 10000),
(3, 1, 'Telur', 'Bahan Basah', 1.00, 'buah', 1800),
(4, 1, 'Gula Pasir', 'Bahan Kering', 1000.00, 'gram', 19000),
(5, 1, 'Susu Cair', 'Bahan Basah', 1000.00, 'ml', 19000),
(6, 1, 'Mentega', 'Lemak', 200.00, 'gram', 7000),
(7, 1, 'Maizena', 'Bahan Kering', 999.74, 'gram', 18000),
(8, 1, 'Vanila Serbuk', 'Bahan Kering', 99.69, 'gram', 5000),
(9, 1, 'Jasa tenaga', 'Non-Pangan & Kemasan', 1.00, 'pack', 10000),
(10, 1, 'Terigu Segitiga Biru', 'Bahan Kering', 1000.00, 'gram', 13000),
(11, 1, 'Gas', 'Non-Pangan & Kemasan', 1.00, 'pack', 8000),
(12, 1, 'Dus Snackbox 15x11', 'Non-Pangan & Kemasan', 1.00, 'pack', 650),
(13, 1, 'Kentang', 'Pelengkap', 1000.00, 'gram', 18500),
(14, 1, 'margarin palmia', 'Lemak', 200.00, 'gram', 6973),
(15, 1, 'palmia royal', 'Lemak', 200.00, 'gram', 9500),
(16, 1, 'Royko Ayam', 'Bahan Kering', 1.00, 'sachet', 1000),
(17, 1, 'Ladaku', 'Bahan Kering', 1.00, 'sachet', 1000),
(18, 1, 'Pala Bubuk', 'Bahan Kering', 1.00, 'sachet', 2000),
(19, 1, 'Dus Snackbox 16x12', 'Non-Pangan & Kemasan', 1.00, 'pack', 750),
(20, 1, 'Mineral Gelas Vit', 'Lain-lain', 48.00, 'pack', 22000),
(21, 1, 'SKM', 'Bahan Basah', 545.00, 'gram', 16000),
(22, 1, 'Strawberry', 'Pelengkap', 12.00, 'buah', 12000),
(23, 1, 'Kiwi', 'Pelengkap', 1.00, 'buah', 6000),
(24, 1, 'Mika 6X', 'Non-Pangan & Kemasan', 50.00, 'pack', 9000),
(25, 1, 'cup', 'Non-Pangan & Kemasan', 1.00, 'sachet', 70),
(26, 1, 'Dark Coklat Batang DCC', 'Pelengkap', 250.00, 'gram', 17000),
(27, 1, 'minyak goreng sania', 'Lemak', 1000.00, 'ml', 39000),
(28, 1, 'terigu cakra kembar', 'Bahan Kering', 1000.00, 'gram', 14000),
(29, 1, 'coklat bubuk vanhouten', 'Bahan Kering', 80.00, 'gram', 28000),
(30, 1, 'kacang almond slice', 'Pelengkap', 250.00, 'gram', 21000),
(31, 1, 'Ayam Fillet', 'Bahan Basah', 1000.00, 'gram', 43000),
(32, 1, 'Santan Kara', 'Bahan Basah', 1.00, 'sachet', 6000),
(33, 1, 'Daun Pisang', 'Pelengkap', 1.00, 'pack', 2000),
(34, 1, 'Beras Ketan', 'Bahan Kering', 1000.00, 'gram', 15000),
(35, 1, 'Bawang Merah Bawang Putih', 'Pelengkap', 1.00, 'pack', 3000),
(36, 1, 'Tepung Tapioka', 'Bahan Kering', 500.00, 'gram', 8000),
(37, 1, 'Agar-agar Plan', 'Bahan Kering', 1.00, 'sachet', 4500),
(38, 1, 'Santan Kelapa Parut', 'Bahan Basah', 1.00, 'buah', 15000),
(39, 1, 'Tepung Beras', 'Bahan Kering', 500.00, 'gram', 8000),
(40, 1, 'Plastik OPP 13x13', 'Non-Pangan & Kemasan', 100.00, 'sachet', 6000),
(41, 1, 'Plastik OPP 10x14', 'Non-Pangan & Kemasan', 100.00, 'sachet', 5000),
(42, 1, 'Cup Bikang', 'Non-Pangan & Kemasan', 200.00, 'sachet', 12000),
(43, 1, 'Tepung Ketan Putih', 'Bahan Kering', 500.00, 'gram', 11000),
(44, 1, 'Kacang Ijo Kupas', 'Pelengkap', 500.00, 'gram', 19000),
(46, 1, 'Keju Spready', 'Lemak', 160.00, 'gram', 14000),
(51, 1, 'isian sosis solo', 'Bahan Olahan', 67.00, 'pack', 70380),
(53, 1, 'isian semar mendem', 'Bahan Olahan', 75.00, 'pack', 70380),
(54, 1, 'transportasi', 'Non-Pangan & Kemasan', 1.00, 'pack', 10000),
(55, 1, 'Plastik OPP 12x14', 'Non-Pangan & Kemasan', 100.00, 'sacket', 5000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `calculator_drafts`
--

CREATE TABLE `calculator_drafts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `draft_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `calculator_drafts`
--

INSERT INTO `calculator_drafts` (`id`, `user_id`, `draft_name`) VALUES
(2, 1, 'Pie Buah');

-- --------------------------------------------------------

--
-- Struktur dari tabel `calculator_ingredients`
--

CREATE TABLE `calculator_ingredients` (
  `id` int(11) NOT NULL,
  `draft_id` int(11) NOT NULL,
  `nama_bahan` varchar(255) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `satuan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `calculator_ingredients`
--

INSERT INTO `calculator_ingredients` (`id`, `draft_id`, `nama_bahan`, `jumlah`, `satuan`) VALUES
(4, 2, 'Terigu Tulip', 1000.00, 'gram');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `resep_id` int(11) NOT NULL,
  `nama_bahan` varchar(255) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `harga_takaran` decimal(12,2) DEFAULT 0.00
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data untuk tabel `ingredients`
--

INSERT INTO `ingredients` (`id`, `resep_id`, `nama_bahan`, `jumlah`, `satuan`, `harga_takaran`) VALUES
(3, 4, 'tepung terigu tulip', 200.00, 'gram', 0.00),
(4, 4, 'kentang', 1000.00, 'gram', 0.00),
(15, 5, 'kentang', 1000.00, 'gram', 0.00),
(14, 5, 'terigu tulip', 200.00, 'gram', 0.00),
(13, 5, 'margarin palmia', 75.00, 'gram', 0.00),
(16, 5, 'susu cair', 200.00, 'ml', 0.00),
(17, 6, 'Terigu Tulip', 200.00, 'gram', 0.00),
(18, 6, 'Kentang', 1000.00, 'gram', 0.00),
(19, 6, 'margarin palmia', 200.00, 'gram', 0.00),
(20, 6, 'Susu Cair', 200.00, 'ml', 0.00),
(59, 7, 'Maizena', 65.00, 'gram', 0.00),
(58, 7, 'margarin palmia', 70.00, 'gram', 0.00),
(57, 7, 'Susu Cair', 500.00, 'ml', 0.00),
(56, 7, 'Jasa', 1.00, 'pack', 0.00),
(55, 7, 'Terigu Tulip', 1000.00, 'gram', 0.00),
(54, 7, 'margarin palmia', 200.00, 'gram', 0.00),
(53, 7, 'palmia royal', 200.00, 'gram', 0.00),
(51, 7, 'Gula Pasir', 150.00, 'gram', 0.00),
(52, 7, 'Telur', 4.00, 'buah', 0.00),
(50, 7, 'SKM', 300.00, 'gram', 0.00),
(49, 7, 'Strawberry', 6.00, 'buah', 0.00),
(48, 7, 'Kiwi', 0.10, 'buah', 0.00),
(47, 7, 'Gas', 1.00, 'pack', 0.00),
(60, 7, 'cup', 40.00, 'sachet', 0.00),
(61, 7, 'Mika 6X', 40.00, 'pack', 0.00),
(62, 2, 'Dark Coklat Batang DCC', 225.00, 'gram', 0.00),
(63, 2, 'margarin palmia', 75.00, 'gram', 0.00),
(64, 2, 'minyak goreng sania', 60.00, 'ml', 0.00),
(65, 2, 'Telur', 3.00, 'buah', 0.00),
(66, 2, 'Gula Pasir', 210.00, 'gram', 0.00),
(67, 2, '1/2 garam (opsional)', 0.00, '', 0.00),
(68, 2, 'pasta vanila', 1.00, 'sdt', 0.00),
(69, 2, 'terigu cakra kembar', 150.00, 'gram', 0.00),
(70, 2, 'coklat bubuk vanhouten', 53.00, 'gram', 0.00),
(71, 2, 'kacang almond slice', 9.00, 'gram', 0.00),
(72, 2, 'Gas', 1.00, 'pack', 0.00),
(73, 2, 'Jasa', 1.00, 'pack', 0.00),
(283, 13, 'transportasi', 1.00, 'pack', 0.00),
(282, 13, 'isian sosis solo', 40.00, 'pack', 0.00),
(281, 13, 'Gas', 1.00, 'pack', 0.00),
(280, 13, 'minyak goreng sania', 200.00, 'ml', 0.00),
(279, 13, 'Santan Kara', 1.00, 'sachet', 0.00),
(226, 8, 'Beras Ketan', 1000.00, 'gram', 0.00),
(225, 8, 'Gas', 1.00, 'pack', 0.00),
(224, 8, 'Daun Pisang', 3.00, 'pack', 0.00),
(222, 8, 'Santan Kara', 1.00, 'sachet', 0.00),
(96, 9, 's', 500.00, 'gram Tepung Bera', 0.00),
(97, 9, 'p', 350.00, 'gram Terigu Tuli', 0.00),
(98, 9, 'a', 50.00, 'gram Maizen', 0.00),
(99, 9, 'a', 50.00, 'gram Tepung Tapiok', 0.00),
(100, 9, 'n', 1.00, 'sachet Agar Pla', 0.00),
(101, 9, 'r', 450.00, 'gram Gula Pasi', 0.00),
(102, 9, 't', 2.00, 'buah Kelapa Paru', 0.00),
(103, 9, 'k', 2.00, 'gram Vanila Serbu', 0.00),
(104, 10, 's', 500.00, 'gram Tepung Bera', 0.00),
(105, 10, 'p', 350.00, 'gram Terigu Tuli', 0.00),
(106, 10, 'a', 50.00, 'gram Maizen', 0.00),
(107, 10, 'a', 50.00, 'gram Tepung Tapiok', 0.00),
(108, 10, 'n', 1.00, 'sachet Agar Pla', 0.00),
(109, 10, 'r', 450.00, 'gram Gula Pasi', 0.00),
(110, 10, 't', 2.00, 'buah Santan Kelapa Paru', 0.00),
(111, 10, 'k', 2.00, 'gram Vanila Serbu', 0.00),
(112, 10, 'a', 1.00, 'pack Jas', 0.00),
(113, 10, 's', 1.00, 'pack Ga', 0.00),
(156, 11, 'Gas', 1.00, 'pack', 0.00),
(155, 11, 'Santan Kelapa Parut', 2.00, 'buah', 0.00),
(154, 11, 'Agar Plan', 1.00, 'sachet', 0.00),
(153, 11, 'Tepung Tapioka', 50.00, 'gram', 0.00),
(152, 11, 'Maizena', 50.00, 'gram', 0.00),
(150, 11, 'Tepung Beras', 500.00, 'gram', 0.00),
(151, 11, 'Terigu Tulip', 350.00, 'gram', 0.00),
(149, 11, 'Gula Pasir', 450.00, 'gram', 0.00),
(148, 11, 'Vanila Serbuk', 2.00, 'gram', 0.00),
(147, 11, 'Jasa', 1.00, 'pack', 0.00),
(157, 11, 'Plastik OPP 13x13', 30.00, 'sachet', 0.00),
(158, 11, 'Cup Bikang', 30.00, 'sachet', 0.00),
(181, 12, 'Gas', 1.00, 'pack', 0.00),
(180, 12, 'Vanila Serbuk', 2.00, 'gram', 0.00),
(179, 12, 'isian kacang ijo', 30.00, 'sachet', 0.00),
(178, 12, 'Gula Pasir', 75.00, 'gram', 0.00),
(177, 12, 'Kentang', 80.00, 'gram', 0.00),
(176, 12, 'Tepung Ketan Putih', 225.00, 'gram', 0.00),
(182, 12, 'cup', 15.00, 'sachet', 0.00),
(183, 12, 'minyak goreng sania', 200.00, 'ml', 0.00),
(185, 1, 'Keju Spready', 160.00, 'gram', 0.00),
(223, 8, 'Jasa', 1.00, 'pack', 0.00),
(221, 8, 'isian lemper', 30.00, 'pack', 0.00),
(278, 13, 'Telur', 3.00, 'buah', 0.00),
(274, 13, 'Jasa', 1.00, 'pack', 0.00),
(275, 13, 'Terigu Tulip', 1000.00, 'gram', 0.00),
(276, 13, 'Telur', 2.00, 'buah', 0.00),
(277, 13, 'Royko Ayam', 2.00, 'sachet', 0.00),
(273, 13, 'Plastik OPP 10x14', 40.00, 'sachet', 0.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `resep`
--

CREATE TABLE `resep` (
  `id` int(11) NOT NULL,
  `nama_kue` varchar(255) NOT NULL,
  `bahan` text NOT NULL,
  `langkah` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `porsi_default` int(11) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data untuk tabel `resep`
--

INSERT INTO `resep` (`id`, `nama_kue`, `bahan`, `langkah`, `user_id`, `gambar`, `youtube_url`, `porsi_default`) VALUES
(1, 'Bolu Ketan Keju Karamel', 'asdadsadasda', 'dasdasdasd', 1, 'resep_68c25f9e0d2282.48874403.jpg', 'https://www.youtube.com/watch?v=moYOfToa5RA', 15),
(2, 'Brownies Coklat', 'Bahan-bahan \r\n225 gram DCC/dark cooking chocolate\r\n75 gram margarin/butter\r\n60 ml minyak goreng \r\n3 butir telur\r\n210 gram gula pasir diblender halus\r\n½ sdt garam (optional)\r\n1 sdt pasta vanilla (optional)\r\n150 gram tepung terigu protein sedang\r\n53 gram coklat bubuk \r\nTopping sesuai selera ', 'Bahan-bahan \r\n225 gram DCC/dark cooking chocolate\r\n75 gram margarin/butter\r\n60 ml minyak goreng \r\n3 butir telur\r\n210 gram gula pasir diblender halus\r\n½ sdt garam (optional)\r\n1 sdt pasta vanilla (optional)\r\n150 gram tepung terigu protein sedang\r\n53 gram coklat bubuk \r\nTopping sesuai selera', 1, '', 'https://www.youtube.com/watch?v=KeKoEJTLpj0', 16),
(7, 'Pie Buah', '', 'campur telur, gula halus, margarin dan bahan lain', 1, '', '', 40),
(6, 'Kroket Kentang', '', '200 gram Terigu Tulip\r\n1000 gram Kentang\r\n200 gram margarin palmia\r\n200 ml Susu Cair', 1, '', '', 33),
(8, 'Lemper Ayam', '', 'Isian\r\n1000 gram Ayam Fillet\r\n1 sachet Ladaku\r\n2 sachet Royko Ayam\r\n1 pack Bawang Merah Bawang Putih\r\n1 sachet Santan Kara\r\n\r\nKulit\r\n1000 gram Beras Ketan\r\n1 sachet Santan Kara\r\n3 pack Daun Pisang', 1, '', '', 30),
(12, 'Onde-onde', '', '225 gram Tepung Ketan Putih\r\n80 gram Kentang\r\n75 gram Gula Pasir\r\n15 sachet isian kacang ijo', 1, '', '', 15),
(13, 'Sosis Solo', '', '40 sachet Plastik OPP 10x14\r\n1 pack Jasa\r\n1000 gram Terigu Tulip\r\n2 buah Telur\r\n2 sachet Royko Ayam\r\n3 buah Telur\r\n1 sachet Santan Kara\r\n200 ml minyak goreng sania\r\n1 pack Gas\r\n40 pack isian sosis solo\r\n1 pack transportasi', 1, '', '', 40),
(11, 'Bikang Mawar', '', '350 gram Terigu Tulip\r\n500 gram Tepung Beras\r\n50 gram Maizena\r\n50 gram Tepung Tapioka\r\n1 sachet Agar Plan\r\n2 buah Santan Kelapa Parut\r\n450 gram Gula Pasir\r\n2 gram Vanila Serbuk', 1, '', '', 30);

-- --------------------------------------------------------

--
-- Struktur dari tabel `shopping_list`
--

CREATE TABLE `shopping_list` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_bahan` varchar(255) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `satuan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `snackbox_paket`
--

CREATE TABLE `snackbox_paket` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_paket` varchar(255) NOT NULL,
  `total_hpp` decimal(12,2) NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `snackbox_paket`
--

INSERT INTO `snackbox_paket` (`id`, `user_id`, `nama_paket`, `total_hpp`, `harga_jual`) VALUES
(1, 1, 'Snackbox Hemat', 6647.49, 11500.00),
(2, 1, 'paket murah', 6160.47, 12000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `snackbox_paket_isi`
--

CREATE TABLE `snackbox_paket_isi` (
  `id` int(11) NOT NULL,
  `paket_id` int(11) NOT NULL,
  `tipe_item` enum('resep','bahan') NOT NULL,
  `nama_item` varchar(255) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `hpp_per_item` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `snackbox_paket_isi`
--

INSERT INTO `snackbox_paket_isi` (`id`, `paket_id`, `tipe_item`, `nama_item`, `jumlah`, `hpp_per_item`) VALUES
(9, 1, 'resep', 'Onde-onde', 1, 1653.69),
(10, 1, 'resep', 'Bikang Mawar', 1, 2115.02),
(11, 1, 'bahan', 'Mineral Gelas Vit', 1, 458.33),
(12, 1, 'resep', 'Sosis Solo', 1, 2420.45),
(17, 2, 'resep', 'Sosis Solo', 1, 2420.45),
(18, 2, 'resep', 'Bikang Mawar', 1, 2115.02),
(19, 2, 'bahan', 'Mineral Gelas Vit', 1, 458.33),
(20, 2, 'resep', 'Lemper Ayam', 1, 1166.67);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `reset_token`, `token_expiry`) VALUES
(1, 'andan', '$2y$10$SR3fUA42rZzojBIAWDlndO2M.whOVCtgzHzXjEgOmnA4.ZMTNh.OK', 'c3278f788cd509fb842a05d44a2dd5dc1349c59ecf4f589281f2933e2f76c1c27530535b99548728af7172871f91d50428ac', '2025-09-16 16:34:33'),
(2, 'admin', '$2y$10$SjTLFuhlUtnj3sWcD38IxuM0lkLY/jRkOSIUOatde.FWdnhIFmYKu', NULL, NULL),
(3, 'percobaan', '$2y$10$v0F3P9nf5KuK8TF5tOC5H.EO1VDO6T/nWKu5DMnRP5jWMvSGSV23i', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bahan_olahan`
--
ALTER TABLE `bahan_olahan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `bahan_olahan_komposisi`
--
ALTER TABLE `bahan_olahan_komposisi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bahan_olahan_id` (`bahan_olahan_id`);

--
-- Indeks untuk tabel `base_ingredients`
--
ALTER TABLE `base_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `calculator_drafts`
--
ALTER TABLE `calculator_drafts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `calculator_ingredients`
--
ALTER TABLE `calculator_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `draft_id` (`draft_id`);

--
-- Indeks untuk tabel `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resep_id` (`resep_id`);

--
-- Indeks untuk tabel `resep`
--
ALTER TABLE `resep`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `shopping_list`
--
ALTER TABLE `shopping_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `snackbox_paket`
--
ALTER TABLE `snackbox_paket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `snackbox_paket_isi`
--
ALTER TABLE `snackbox_paket_isi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paket_id` (`paket_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bahan_olahan`
--
ALTER TABLE `bahan_olahan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `bahan_olahan_komposisi`
--
ALTER TABLE `bahan_olahan_komposisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT untuk tabel `base_ingredients`
--
ALTER TABLE `base_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT untuk tabel `calculator_drafts`
--
ALTER TABLE `calculator_drafts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `calculator_ingredients`
--
ALTER TABLE `calculator_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=284;

--
-- AUTO_INCREMENT untuk tabel `resep`
--
ALTER TABLE `resep`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `shopping_list`
--
ALTER TABLE `shopping_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `snackbox_paket`
--
ALTER TABLE `snackbox_paket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `snackbox_paket_isi`
--
ALTER TABLE `snackbox_paket_isi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bahan_olahan_komposisi`
--
ALTER TABLE `bahan_olahan_komposisi`
  ADD CONSTRAINT `fk_bahan_olahan` FOREIGN KEY (`bahan_olahan_id`) REFERENCES `bahan_olahan` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `calculator_ingredients`
--
ALTER TABLE `calculator_ingredients`
  ADD CONSTRAINT `calculator_ingredients_ibfk_1` FOREIGN KEY (`draft_id`) REFERENCES `calculator_drafts` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `snackbox_paket_isi`
--
ALTER TABLE `snackbox_paket_isi`
  ADD CONSTRAINT `fk_snackbox_paket` FOREIGN KEY (`paket_id`) REFERENCES `snackbox_paket` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
