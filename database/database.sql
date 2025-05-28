-- --------------------------------------------------------
-- Host:                         194.233.66.46
-- Server version:               10.6.20-MariaDB - MariaDB Server
-- Server OS:                    linux-systemd
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table mikhaelf_db_mp.tbl_akt_kas
DROP TABLE IF EXISTS `tbl_akt_kas`;
CREATE TABLE IF NOT EXISTS `tbl_akt_kas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tgl_masuk` date NOT NULL,
  `jenis` varchar(160) NOT NULL,
  `keterangan` text NOT NULL,
  `status_kas` enum('1','2') NOT NULL COMMENT '1=kas masuk, 2=kas keluar',
  `tipe` int(5) NOT NULL,
  `nominal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_akt_kas: ~2 rows (approximately)
DELETE FROM `tbl_akt_kas`;
INSERT INTO `tbl_akt_kas` (`id`, `tgl_masuk`, `jenis`, `keterangan`, `status_kas`, `tipe`, `nominal`, `created_at`, `updated_at`) VALUES
	(3, '2024-12-20', 'Pembayaran Air Agus Saputra Periode Desember 2024', 'Pembayaran Air Agus Saputra Periode Desember 2024', '1', 1, 321500.00, '2024-12-20 05:30:03', '2024-12-20 05:30:03'),
	(4, '2024-12-20', 'Pengeluaran Air', 'Beli pipa', '2', 1, 100000.00, '2024-12-20 06:24:34', '2024-12-20 06:24:34');

-- Dumping structure for table mikhaelf_db_mp.tbl_log_delete
DROP TABLE IF EXISTS `tbl_log_delete`;
CREATE TABLE IF NOT EXISTS `tbl_log_delete` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `deleted_by` int(11) NOT NULL,
  `deleted_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_by` (`deleted_by`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_log_delete: ~0 rows (approximately)
DELETE FROM `tbl_log_delete`;
INSERT INTO `tbl_log_delete` (`id`, `table_name`, `record_id`, `reason`, `deleted_by`, `deleted_at`) VALUES
	(1, 'tbl_trx_air', 6, 'HAPUS', 1, '2024-12-22 08:31:14');

-- Dumping structure for table mikhaelf_db_mp.tbl_m_air_tarif
DROP TABLE IF EXISTS `tbl_m_air_tarif`;
CREATE TABLE IF NOT EXISTS `tbl_m_air_tarif` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `range_pemakaian` varchar(50) NOT NULL,
  `biaya_m3` decimal(10,2) NOT NULL,
  `biaya_mtc` decimal(10,2) NOT NULL,
  `biaya_adm` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_m_air_tarif: ~0 rows (approximately)
DELETE FROM `tbl_m_air_tarif`;
INSERT INTO `tbl_m_air_tarif` (`id`, `range_pemakaian`, `biaya_m3`, `biaya_mtc`, `biaya_adm`, `created_at`, `updated_at`) VALUES
	(1, '0-10 m³', 3000.00, 5000.00, 0.00, '2024-12-19 02:13:33', '2025-03-03 04:25:15');

-- Dumping structure for table mikhaelf_db_mp.tbl_m_platform
DROP TABLE IF EXISTS `tbl_m_platform`;
CREATE TABLE IF NOT EXISTS `tbl_m_platform` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_m_platform: ~4 rows (approximately)
DELETE FROM `tbl_m_platform`;
INSERT INTO `tbl_m_platform` (`id`, `platform`, `keterangan`, `status`) VALUES
	(1, 'Cash', 'Pembayaran tunai langsung', 1),
	(2, 'Transfer Bank', 'Transfer melalui rekening bank', 1),
	(3, 'QRIS', 'Pembayaran menggunakan QRIS', 1),
	(4, 'E-Wallet', 'Pembayaran menggunakan dompet digital', 1);

-- Dumping structure for table mikhaelf_db_mp.tbl_m_warga
DROP TABLE IF EXISTS `tbl_m_warga`;
CREATE TABLE IF NOT EXISTS `tbl_m_warga` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tgl_masuk` date DEFAULT NULL,
  `tgl_keluar` date DEFAULT NULL,
  `kk` varchar(50) DEFAULT NULL,
  `nik` varchar(50) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `blok` varchar(10) DEFAULT NULL,
  `status_rumah` enum('1','2') DEFAULT NULL COMMENT '1=Sendiri, 2=Kontrak',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `no_hp` varchar(15) DEFAULT NULL,
  `status_warga` tinyint(1) DEFAULT 1 COMMENT '1=Aktif, 0=Tidak Aktif',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=latin2 COLLATE=latin2_general_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_m_warga: ~43 rows (approximately)
DELETE FROM `tbl_m_warga`;
INSERT INTO `tbl_m_warga` (`id`, `tgl_masuk`, `tgl_keluar`, `kk`, `nik`, `nama`, `alamat`, `blok`, `status_rumah`, `created_at`, `updated_at`, `no_hp`, `status_warga`) VALUES
	(1, '2021-03-15', NULL, '3374071502920002', '3374071502920002', 'Mikhael Felian Waskito', 'Perum Mutiara Pandanaran', 'D-11', '1', '2024-12-19 09:53:41', '2024-12-19 09:57:37', '85741220427', 1),
	(2, '2024-03-26', NULL, '3374794676', '3374705851', 'Siti Hidayat', 'Jalan Mutiara Pandanaran No. 71', 'B-01', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80052809252', 1),
	(3, '2024-06-24', '2025-06-24', '3374976699', '3374035391', 'Ani Hidayat', 'Jalan Mutiara Pandanaran No. 73', 'B-02', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80022878115', 1),
	(4, '2023-10-18', '2024-10-18', '3374381950', '3374059346', 'Rudi Pratama', 'Jalan Mutiara Pandanaran No. 64', 'B-11', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80098665792', 1),
	(5, '2023-06-15', NULL, '3374053501', '3374575967', 'Budi Saputra', 'Jalan Mutiara Pandanaran No. 8', 'C-18', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80016606705', 1),
	(6, '2023-11-27', NULL, '3374375778', '3374795467', 'Joko Saputra', 'Jalan Mutiara Pandanaran No. 65', 'B-12', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80027504455', 1),
	(7, '2023-04-20', '2024-04-20', '3374450939', '3374668127', 'Agus Susanto', 'Jalan Mutiara Pandanaran No. 100', 'A-16', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80056557459', 1),
	(8, '2023-12-11', NULL, '3374365874', '3374443408', 'Rudi Santoso', 'Jalan Mutiara Pandanaran No. 81', 'A-11', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80017305182', 1),
	(9, '2023-11-30', NULL, '3374778333', '3374666864', 'Budi Kusuma', 'Jalan Mutiara Pandanaran No. 9', 'D-02', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80033262475', 1),
	(17, '2023-08-04', '2024-08-04', '3374460053', '3374118156', 'Agus Saputra', 'Jalan Mutiara Pandanaran No. 40', 'A-12', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80019520972', 1),
	(18, '2023-02-25', '2024-02-25', '3374748547', '3374025818', 'Rina Hidayat', 'Jalan Mutiara Pandanaran No. 99', 'B-08', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80054399817', 1),
	(19, '2024-09-16', NULL, '3374226017', '3374193502', 'Joko Susanto', 'Jalan Mutiara Pandanaran No. 14', 'A-17', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80017312394', 1),
	(20, '2023-09-24', NULL, '3374104174', '3374235636', 'Rina Pratama', 'Jalan Mutiara Pandanaran No. 84', 'C-22', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80063073673', 1),
	(21, '2023-03-02', '2024-03-02', '3374120085', '3374491085', 'Sri Susanto', 'Jalan Mutiara Pandanaran No. 38', 'C-22', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80094918668', 1),
	(22, '2023-07-12', NULL, '3374551303', '3374315309', 'Rudi Wibowo', 'Jalan Mutiara Pandanaran No. 82', 'D-16', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80019881043', 1),
	(23, '2023-09-06', NULL, '3374278956', '3374598440', 'Sri Pratama', 'Jalan Mutiara Pandanaran No. 38', 'B-09', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80041601248', 1),
	(24, '2024-03-12', '2025-03-12', '3374001796', '3374781445', 'Sri Nugroho', 'Jalan Mutiara Pandanaran No. 9', 'A-22', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80091106033', 1),
	(25, '2023-12-31', '2024-12-31', '3374493748', '3374616096', 'Ani Kusuma', 'Jalan Mutiara Pandanaran No. 97', 'D-10', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80080473187', 1),
	(26, '2023-07-09', '2024-07-09', '3374350093', '3374805630', 'Agus Wibowo', 'Jalan Mutiara Pandanaran No. 72', 'D-22', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80087349179', 1),
	(27, '2024-09-30', NULL, '3374469472', '3374725654', 'Rudi Susanto', 'Jalan Mutiara Pandanaran No. 5', 'B-02', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80051628602', 1),
	(28, '2024-02-08', NULL, '3374065633', '3374313547', 'Agus Hidayat', 'Jalan Mutiara Pandanaran No. 31', 'C-22', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80001061398', 1),
	(29, '2024-08-06', NULL, '3374904793', '3374646071', 'Rudi Kusuma', 'Jalan Mutiara Pandanaran No. 79', 'A-16', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80083522692', 1),
	(30, '2024-02-03', NULL, '3374392285', '3374285446', 'Rina Wibowo', 'Jalan Mutiara Pandanaran No. 60', 'C-18', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80037238026', 1),
	(31, '2024-05-13', NULL, '3374265821', '3374897395', 'Rudi Susanto', 'Jalan Mutiara Pandanaran No. 49', 'C-08', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80002154615', 1),
	(32, '2024-03-28', '2025-03-28', '3374388642', '3374625108', 'Dewi Nugroho', 'Jalan Mutiara Pandanaran No. 22', 'B-21', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80023628338', 1),
	(33, '2023-01-14', '2024-01-14', '3374539428', '3374292871', 'Rudi Pratama', 'Jalan Mutiara Pandanaran No. 67', 'D-13', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80054835960', 1),
	(34, '2023-11-09', NULL, '3374477735', '3374812644', 'Siti Kusuma', 'Jalan Mutiara Pandanaran No. 46', 'B-23', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80011712378', 1),
	(35, '2023-08-26', NULL, '3374448904', '3374748861', 'Ahmad Saputra', 'Jalan Mutiara Pandanaran No. 88', 'C-02', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80018383040', 1),
	(36, '2023-12-16', '2024-12-16', '3374857028', '3374141816', 'Joko Purnama', 'Jalan Mutiara Pandanaran No. 78', 'D-10', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80075470634', 1),
	(37, '2024-04-15', '2025-04-15', '3374675116', '3374440903', 'Joko Purnama', 'Jalan Mutiara Pandanaran No. 35', 'A-18', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80068476583', 1),
	(38, '2023-05-30', '2024-05-30', '3374615920', '3374916742', 'Joko Saputra', 'Jalan Mutiara Pandanaran No. 93', 'A-11', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80096771293', 1),
	(39, '2023-02-02', '2024-02-02', '3374292996', '3374437861', 'Budi Saputra', 'Jalan Mutiara Pandanaran No. 27', 'A-03', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80058068799', 1),
	(40, '2023-03-05', '2024-03-05', '3374037343', '3374147886', 'Budi Pratama', 'Jalan Mutiara Pandanaran No. 92', 'D-24', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80088336257', 1),
	(41, '2023-05-23', '2024-05-23', '3374691529', '3374812531', 'Rudi Purnama', 'Jalan Mutiara Pandanaran No. 32', 'C-20', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80042560267', 1),
	(42, '2024-02-16', NULL, '3374443310', '3374484563', 'Rina Purnama', 'Jalan Mutiara Pandanaran No. 96', 'B-18', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80069663740', 1),
	(43, '2024-08-09', '2025-08-09', '3374818829', '3374119303', 'Ani Nugroho', 'Jalan Mutiara Pandanaran No. 44', 'C-13', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80061879829', 1),
	(44, '2024-08-03', '2025-08-03', '3374175144', '3374798795', 'Ani Pratama', 'Jalan Mutiara Pandanaran No. 6', 'B-21', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80019693842', 1),
	(45, '2024-11-10', NULL, '3374922577', '3374812272', 'Budi Santoso', 'Jalan Mutiara Pandanaran No. 87', 'B-16', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80022814289', 1),
	(46, '2024-03-30', '2025-03-30', '3374248731', '3374308517', 'Siti Pratama', 'Jalan Mutiara Pandanaran No. 68', 'D-23', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80052483012', 1),
	(47, '2024-08-03', '2025-08-03', '3374010382', '3374799614', 'Sri Wijaya', 'Jalan Mutiara Pandanaran No. 12', 'D-03', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80000246072', 1),
	(48, '2023-08-12', NULL, '3374423579', '3374787519', 'Rina Nugroho', 'Jalan Mutiara Pandanaran No. 96', 'B-09', '1', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80045118005', 1),
	(49, '2023-04-10', '2024-04-10', '3374600846', '3374503396', 'Rina Pratama', 'Jalan Mutiara Pandanaran No. 26', 'C-23', '2', '2024-12-19 09:53:41', '2024-12-19 09:53:41', '80020071884', 1);

-- Dumping structure for table mikhaelf_db_mp.tbl_m_warga_file
DROP TABLE IF EXISTS `tbl_m_warga_file`;
CREATE TABLE IF NOT EXISTS `tbl_m_warga_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_warga` int(11) NOT NULL,
  `tgl_masuk` date NOT NULL,
  `nama` varchar(100) NOT NULL,
  `file` varchar(255) NOT NULL,
  `tipe` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_warga` (`id_warga`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_m_warga_file: ~10 rows (approximately)
DELETE FROM `tbl_m_warga_file`;
INSERT INTO `tbl_m_warga_file` (`id`, `id_warga`, `tgl_masuk`, `nama`, `file`, `tipe`) VALUES
	(1, 53, '2024-12-20', 'KK', '3374072807190003/67658de9b0399_1734708713.jpg', 1),
	(2, 53, '2024-12-20', 'KTP', '3374072807190003/676594e520510_1734710501.png', 2),
	(4, 54, '2024-12-21', 'KK', '3374072807190003/6766323b18c17_1734750779.png', 1),
	(5, 54, '2024-12-21', 'KTP', '3374072807190003/6766325f4e4c7_1734750815.jpg', 2),
	(7, 55, '2024-12-22', 'KK', '3374072807190003/67677b3a93845_1734835002.jpg', 1),
	(8, 55, '2024-12-22', 'KTP', '3374072807190003/67677b49bd9b2_1734835017.jpg', 2),
	(10, 0, '2024-12-22', 'KK', '3374072807190003/67678c8b3a5be_1734839435.jpg', 1),
	(11, 0, '2024-12-22', 'KTP', '3374072807190003/67678c9a7d1de_1734839450.jpg', 2),
	(12, 51, '2024-12-22', 'KK', '3374072807190003/67678c8b3a5be_1734839435.jpg', 1),
	(13, 51, '2024-12-22', 'KTP', '3374072807190003/67678c9a7d1de_1734839450.jpg', 2);

-- Dumping structure for table mikhaelf_db_mp.tbl_pengaturan
DROP TABLE IF EXISTS `tbl_pengaturan`;
CREATE TABLE IF NOT EXISTS `tbl_pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `judul_app` varchar(100) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `theme` varchar(50) DEFAULT NULL,
  `pagination_limit` int(11) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_pengaturan: ~0 rows (approximately)
DELETE FROM `tbl_pengaturan`;
INSERT INTO `tbl_pengaturan` (`id`, `judul`, `alamat`, `kota`, `judul_app`, `url`, `theme`, `pagination_limit`, `favicon`, `logo`) VALUES
	(1, 'PERUM MUTIARA PANDANARAN', 'MANGUNHARJO, TEMBALANG', 'KOTA SEMARANG', 'SONGGO WARGO', 'http://localhost/pam/', 'default', 10, 'favicon_1734890393_6eacffddd3644071.png', 'logo_1734890393_6e86e260b0a347e0.png');

-- Dumping structure for table mikhaelf_db_mp.tbl_rate_limit
DROP TABLE IF EXISTS `tbl_rate_limit`;
CREATE TABLE IF NOT EXISTS `tbl_rate_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `request_time` (`request_time`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_rate_limit: ~10 rows (approximately)
DELETE FROM `tbl_rate_limit`;
INSERT INTO `tbl_rate_limit` (`id`, `user_id`, `request_time`) VALUES
	(1, 1, '2024-12-22 01:36:53'),
	(2, 1, '2024-12-22 01:38:17'),
	(3, 1, '2024-12-22 01:41:38'),
	(4, 1, '2024-12-22 01:42:05'),
	(5, 1, '2024-12-22 02:32:52'),
	(6, 1, '2024-12-22 02:33:22'),
	(7, 1, '2024-12-22 02:33:24'),
	(8, 1, '2024-12-22 02:33:26'),
	(9, 1, '2024-12-22 02:33:27'),
	(10, 1, '2024-12-22 02:33:28');

-- Dumping structure for table mikhaelf_db_mp.tbl_trx_air
DROP TABLE IF EXISTS `tbl_trx_air`;
CREATE TABLE IF NOT EXISTS `tbl_trx_air` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_warga` int(11) NOT NULL,
  `meter_awal` decimal(10,2) NOT NULL,
  `meter_akhir` decimal(10,2) NOT NULL,
  `pemakaian` decimal(10,2) NOT NULL,
  `bulan` varchar(2) NOT NULL,
  `tahun` varchar(4) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status_bayar` tinyint(1) NOT NULL DEFAULT 0,
  `total_tagihan` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_warga` (`id_warga`),
  CONSTRAINT `tbl_trx_air_ibfk_1` FOREIGN KEY (`id_warga`) REFERENCES `tbl_m_warga` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_trx_air: ~2 rows (approximately)
DELETE FROM `tbl_trx_air`;
INSERT INTO `tbl_trx_air` (`id`, `id_warga`, `meter_awal`, `meter_akhir`, `pemakaian`, `bulan`, `tahun`, `created_by`, `created_at`, `updated_at`, `status_bayar`, `total_tagihan`) VALUES
	(3, 1, 2244.00, 2334.00, 90.00, '12', '2024', 1, '2024-12-19 12:34:26', '2024-12-20 04:55:37', 0, 268500),
	(4, 17, 1224.00, 1334.00, 110.00, '12', '2024', 1, '2024-12-19 14:16:30', '2024-12-20 05:30:03', 1, 321500);

-- Dumping structure for table mikhaelf_db_mp.tbl_trx_air_pembayaran
DROP TABLE IF EXISTS `tbl_trx_air_pembayaran`;
CREATE TABLE IF NOT EXISTS `tbl_trx_air_pembayaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trx_air` int(11) NOT NULL,
  `id_platform` int(11) NOT NULL,
  `jumlah_bayar` int(11) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `kembalian` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_trx_air` (`id_trx_air`),
  KEY `id_platform` (`id_platform`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `tbl_trx_air_pembayaran_ibfk_1` FOREIGN KEY (`id_trx_air`) REFERENCES `tbl_trx_air` (`id`),
  CONSTRAINT `tbl_trx_air_pembayaran_ibfk_2` FOREIGN KEY (`id_platform`) REFERENCES `tbl_m_platform` (`id`),
  CONSTRAINT `tbl_trx_air_pembayaran_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_trx_air_pembayaran: ~0 rows (approximately)
DELETE FROM `tbl_trx_air_pembayaran`;
INSERT INTO `tbl_trx_air_pembayaran` (`id`, `id_trx_air`, `id_platform`, `jumlah_bayar`, `platform`, `kembalian`, `created_by`, `created_at`) VALUES
	(6, 4, 1, 400000, 'Cash', 0, 1, '2024-12-20 05:30:03');

-- Dumping structure for table mikhaelf_db_mp.tbl_trx_mutasi_warga
DROP TABLE IF EXISTS `tbl_trx_mutasi_warga`;
CREATE TABLE IF NOT EXISTS `tbl_trx_mutasi_warga` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tgl_masuk` date NOT NULL,
  `no_kk` varchar(16) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `blok` varchar(16) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat_asal` text DEFAULT NULL,
  `alamat_tujuan` text DEFAULT NULL,
  `status_rumah` enum('1','2') NOT NULL COMMENT '1=Rumah Sendiri, 2=Kontrak',
  `jenis_mutasi` enum('1','2') NOT NULL COMMENT '1=Masuk, 2=Keluar',
  `keterangan` text DEFAULT NULL,
  `status_berkas` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '0=Belum, 1=Komplet, 2=Kurang',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_trx_mutasi_warga: ~0 rows (approximately)
DELETE FROM `tbl_trx_mutasi_warga`;
INSERT INTO `tbl_trx_mutasi_warga` (`id`, `created_at`, `updated_at`, `tgl_masuk`, `no_kk`, `nik`, `blok`, `nama`, `alamat_asal`, `alamat_tujuan`, `status_rumah`, `jenis_mutasi`, `keterangan`, `status_berkas`) VALUES
	(5, '2024-12-22 03:18:09', '2024-12-22 04:01:14', '2024-12-22', '3374072807190003', '3374072807190003', 'Z-11', 'COSMOS BIN SULEMAN', 'SSSSS', 'rr', '1', '1', '', '1');

-- Dumping structure for table mikhaelf_db_mp.tbl_trx_mutasi_warga_file
DROP TABLE IF EXISTS `tbl_trx_mutasi_warga_file`;
CREATE TABLE IF NOT EXISTS `tbl_trx_mutasi_warga_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mutasi` int(11) NOT NULL,
  `tgl_masuk` date NOT NULL DEFAULT current_timestamp(),
  `jenis_berkas` tinyint(4) NOT NULL COMMENT '1=KK,2=KTP Suami,3=KTP Istri,4=KIA,5=Surat Nikah,6=Surat Pindah,7=Surat Pengantar RT,8=Surat Pengantar RW,9=Surat Keterangan Kerja,10=Surat Pernyataan,11=Akta Kelahiran,12=Ijazah,13=SKCK,14=Sertifikat Vaksin,15=Lainnya',
  `nama` varchar(100) NOT NULL,
  `file` varchar(255) NOT NULL,
  `tipe` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Belum Diverifikasi,1=Valid,2=Tidak Valid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_mutasi` (`id_mutasi`),
  CONSTRAINT `tbl_trx_mutasi_warga_file_ibfk_1` FOREIGN KEY (`id_mutasi`) REFERENCES `tbl_trx_mutasi_warga` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_trx_mutasi_warga_file: ~2 rows (approximately)
DELETE FROM `tbl_trx_mutasi_warga_file`;
INSERT INTO `tbl_trx_mutasi_warga_file` (`id`, `id_mutasi`, `tgl_masuk`, `jenis_berkas`, `nama`, `file`, `tipe`, `status`, `created_at`) VALUES
	(11, 5, '2024-12-22', 1, 'KK', '3374072807190003/67678c8b3a5be_1734839435.jpg', 1, 1, '2024-12-22 03:50:35'),
	(12, 5, '2024-12-22', 2, 'KTP', '3374072807190003/67678c9a7d1de_1734839450.jpg', 2, 1, '2024-12-22 03:50:50');

-- Dumping structure for table mikhaelf_db_mp.tbl_users
DROP TABLE IF EXISTS `tbl_users`;
CREATE TABLE IF NOT EXISTS `tbl_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','pengurus','warga') NOT NULL,
  `id_warga` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `id_warga` (`id_warga`),
  CONSTRAINT `tbl_users_ibfk_1` FOREIGN KEY (`id_warga`) REFERENCES `tbl_m_warga` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table mikhaelf_db_mp.tbl_users: ~12 rows (approximately)
DELETE FROM `tbl_users`;
INSERT INTO `tbl_users` (`id`, `username`, `password`, `role`, `id_warga`, `created_at`, `updated_at`, `profile_picture`) VALUES
	(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 1, '2024-12-18 17:50:36', '2024-12-20 10:36:43', 'profile_1_2f205fa45c99bb9c5339e4c57e8d2da3.jpg'),
	(2, '3374071502920002', '$2y$10$U/f7lzqJtVAGL3XakYWVLujj7zE422sBrm.XK0/Bl9DUWlo7yMpo2', 'warga', 24, '2024-12-19 09:54:41', '2024-12-20 10:36:52', NULL),
	(3, '3374705851', '$2y$10$XW.33W/3n0tPz8lBO4wE4uJw6jecsJkjf8Pj0MiSX6b9IFm05hL/W', 'warga', 2, '2024-12-19 09:54:44', '2024-12-19 09:54:44', NULL),
	(4, '3374035391', '$2y$10$P/1tgVg18HhMwN2xq2JeiudoImOtqXGjrM1a1ax8JxqCgUX1rYF/m', 'warga', 3, '2024-12-19 09:54:46', '2024-12-19 09:54:46', NULL),
	(5, '3374059346', '$2y$10$mq3BJcMDGhnqhRTyvm1iIeEQEvlQ0D7tir2iTUzORf.tWE5IFC9iO', 'warga', 4, '2024-12-19 09:54:48', '2024-12-19 09:54:48', NULL),
	(6, '3374575967', '$2y$10$6xKvK9bu6IV/Ze8H6rmYEO2TIhRlaFZSTH2GL9I7NHrB18LIWGmFK', 'warga', 5, '2024-12-19 09:54:51', '2024-12-19 09:54:51', NULL),
	(7, '3374795467', '$2y$10$oSN93LlrEsjmaAbIPQllzOhbthYNGfydCqBSxCXanhv8jEiAyn.dO', 'warga', 6, '2024-12-19 09:54:53', '2024-12-19 09:54:53', NULL),
	(8, '3374668127', '$2y$10$ChSeXJgfzbPpNhqZ/4MNke09KApDGc9pMf8QL8JXiFOc78J.XLS4O', 'warga', 7, '2024-12-19 09:54:56', '2024-12-19 09:54:56', NULL),
	(9, '3374443408', '$2y$10$5O8Il0cNgffqNel7CjiZO.y.wc4XlBMHwXxCY9/oUB9hA4/.g0aA.', 'warga', 8, '2024-12-19 09:54:58', '2024-12-19 09:54:58', NULL),
	(10, '3374666864', '$2y$10$DIeAMvKsmqIB8ZTXMYcN6uGBG7K3uHi9odCSFUFBZvG5.2.z3t1Wu', 'warga', 9, '2024-12-19 09:55:01', '2024-12-19 09:55:01', NULL),
	(12, '3374313547', '$2y$10$igJbc6dM9txZ3uSqsMO/..j0qzDV7jnn4pdj0NtJmQl71/tXYwFSC', 'pengurus', 28, '2024-12-19 14:11:16', '2024-12-19 14:11:16', NULL),
	(14, '3374118156', '$2y$10$klGsTeJNLtEdtrd1453VBu/BnhnmTCHX0EKnemD43JCXx.cNsx0my', 'warga', 17, '2024-12-21 05:33:25', '2024-12-21 05:33:25', NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
