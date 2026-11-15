-- ============================================================
-- PT OSI ELECTRONICS BATAM - LOGBOOK DIGITAL IQC
-- MySQL Dump for InfinityFree (Database: if0_43310880_iqc)
-- Generated based on Proposal ERD (12 Entities, 6 Roles)
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;
SET time_zone = '+07:00';

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `nik` varchar(255) NOT NULL UNIQUE,
  `nip` varchar(255) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'inspector_ee',
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `shift` varchar(10) NOT NULL DEFAULT '1',
  `email` varchar(255) DEFAULT NULL UNIQUE,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_supplier` varchar(50) NOT NULL UNIQUE,
  `nama_supplier` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `kontak` varchar(100) DEFAULT NULL,
  `negara_asal` varchar(100) NOT NULL DEFAULT 'Indonesia',
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `parts`;
CREATE TABLE `parts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_part` varchar(50) NOT NULL UNIQUE,
  `nama_part` varchar(255) NOT NULL,
  `spesifikasi` text DEFAULT NULL,
  `kategori_komoditas` enum('ee_part','automotive','wire_harness') NOT NULL DEFAULT 'ee_part',
  `standar_aql` varchar(100) NOT NULL DEFAULT 'MIL-STD-105E AQL 0.65',
  `level_inspeksi` varchar(100) NOT NULL DEFAULT 'General Level II',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `part_parameters`;
CREATE TABLE `part_parameters` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `part_id` bigint(20) UNSIGNED NOT NULL,
  `nama_parameter` varchar(150) NOT NULL,
  `nominal` decimal(12,4) DEFAULT NULL,
  `batas_min` decimal(12,4) DEFAULT NULL,
  `batas_max` decimal(12,4) DEFAULT NULL,
  `satuan` varchar(30) NOT NULL DEFAULT 'mm',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_part_parameters_part_id` (`part_id`),
  CONSTRAINT `fk_part_params_part` FOREIGN KEY (`part_id`) REFERENCES `parts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `defect_categories`;
CREATE TABLE `defect_categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_defect` varchar(50) NOT NULL UNIQUE,
  `nama_defect` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `receivings`;
CREATE TABLE `receivings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `no_grn_iqa` varchar(50) NOT NULL UNIQUE,
  `tanggal_terima` datetime NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `part_id` bigint(20) UNSIGNED NOT NULL,
  `no_po` varchar(50) NOT NULL,
  `no_lot` varchar(50) NOT NULL,
  `qty_datang` int(11) NOT NULL,
  `status_antrean` enum('menunggu_inspeksi','dalam_inspeksi','selesai') NOT NULL DEFAULT 'menunggu_inspeksi',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_receivings_supplier_id` (`supplier_id`),
  KEY `idx_receivings_part_id` (`part_id`),
  CONSTRAINT `fk_receivings_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_receivings_part` FOREIGN KEY (`part_id`) REFERENCES `parts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `logbooks`;
CREATE TABLE `logbooks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `receiving_id` bigint(20) UNSIGNED NOT NULL,
  `inspector_id` bigint(20) UNSIGNED NOT NULL,
  `qty_sampel` int(11) NOT NULL DEFAULT 5,
  `status` enum('draft','menunggu_penetapan','perlu_revisi','accept','return_to_vendor','scrap','rework') NOT NULL DEFAULT 'draft',
  `tanggal_inspeksi` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_logbooks_receiving_id` (`receiving_id`),
  KEY `idx_logbooks_inspector_id` (`inspector_id`),
  CONSTRAINT `fk_logbooks_receiving` FOREIGN KEY (`receiving_id`) REFERENCES `receivings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_logbooks_inspector` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `measurements`;
CREATE TABLE `measurements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `logbook_id` bigint(20) UNSIGNED NOT NULL,
  `part_parameter_id` bigint(20) UNSIGNED NOT NULL,
  `sample_number` int(11) NOT NULL,
  `nilai_ukur` decimal(12,4) DEFAULT NULL,
  `status_toleransi` enum('pass','fail') NOT NULL DEFAULT 'pass',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_measurements_logbook_id` (`logbook_id`),
  KEY `idx_measurements_param_id` (`part_parameter_id`),
  CONSTRAINT `fk_measurements_logbook` FOREIGN KEY (`logbook_id`) REFERENCES `logbooks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_measurements_param` FOREIGN KEY (`part_parameter_id`) REFERENCES `part_parameters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `defects`;
CREATE TABLE `defects` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `logbook_id` bigint(20) UNSIGNED NOT NULL,
  `defect_category_id` bigint(20) UNSIGNED NOT NULL,
  `qty_defect` int(11) NOT NULL DEFAULT 1,
  `tingkat_keparahan` enum('minor','major','critical') NOT NULL DEFAULT 'major',
  `deskripsi` text DEFAULT NULL,
  `foto_bukti` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_defects_logbook_id` (`logbook_id`),
  KEY `idx_defects_category_id` (`defect_category_id`),
  CONSTRAINT `fk_defects_logbook` FOREIGN KEY (`logbook_id`) REFERENCES `logbooks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_defects_category` FOREIGN KEY (`defect_category_id`) REFERENCES `defect_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `nest_processes`;
CREATE TABLE `nest_processes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `logbook_id` bigint(20) UNSIGNED NOT NULL,
  `inspector_id` bigint(20) UNSIGNED NOT NULL,
  `kronologi_masalah` text NOT NULL,
  `bukti_pendukung` varchar(255) DEFAULT NULL,
  `diajukan_pada` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_nest_logbook_id` (`logbook_id`),
  KEY `idx_nest_inspector_id` (`inspector_id`),
  CONSTRAINT `fk_nest_logbook` FOREIGN KEY (`logbook_id`) REFERENCES `logbooks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_nest_inspector` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `mrb_dispositions`;
CREATE TABLE `mrb_dispositions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `logbook_id` bigint(20) UNSIGNED NOT NULL,
  `supervisor_id` bigint(20) UNSIGNED NOT NULL,
  `status_keputusan` enum('accept','return_to_vendor','scrap','rework','perlu_revisi') NOT NULL,
  `catatan` text NOT NULL,
  `diputuskan_pada` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mrb_logbook_id` (`logbook_id`),
  KEY `idx_mrb_supervisor_id` (`supervisor_id`),
  CONSTRAINT `fk_mrb_logbook` FOREIGN KEY (`logbook_id`) REFERENCES `logbooks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mrb_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `audit_trails`;
CREATE TABLE `audit_trails` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `aksi` varchar(100) NOT NULL,
  `objek_data` varchar(255) NOT NULL,
  `nilai_sebelum` text DEFAULT NULL,
  `nilai_sesudah` text DEFAULT NULL,
  `alamat_ip` varchar(45) DEFAULT NULL,
  `waktu` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_audit_trails_user_id` (`user_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEEDED DATA FOR PT OSI ELECTRONICS BATAM
-- ============================================================

-- Data for table `users`
LOCK TABLES `users` WRITE;
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (1, 'Aditya Ryan Wardana', 'ADM-001', 'ADM-001', 'admin', 1, '1', 'admin@iqc.local', NULL, '$2y$12$KnTbpRYTTYfjXYxB2fIK5uCgX.CuufWIsucOKdj0jmNCWc5ltqyhe', NULL, '2026-11-08 09:02:28', '2026-11-08 09:02:28');
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (2, 'Bambang Sudarsono', 'LOG-001', 'LOG-001', 'master_data', 1, '1', 'logistik@iqc.local', NULL, '$2y$12$X.NWjhZT6xFrUmXwnm4Jm.qS/NKGES9QQqqsS2kGDZ6XKk5fOpDtG', NULL, '2026-11-08 09:02:29', '2026-11-08 09:02:29');
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (3, 'Kislistiyono', 'SPV-001', 'SPV-001', 'supervisor', 1, '1', 'supervisor@iqc.local', NULL, '$2y$12$kiUNYwpbikO7u8FyI00msu3UYY.xp7e.qQSz5PhuGhYipSP0dKqpW', NULL, '2026-11-08 09:02:29', '2026-11-08 09:02:29');
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (4, 'Serlin Yolanda', 'INS-EE-001', '3312411105', 'inspector_ee', 1, '1', 'serlin.yolanda@iqc.local', NULL, '$2y$12$y0TErG5VjNiBxswXEefuv.SiWoJXA3SnGdu38e4K9SGAb9gwk7sve', NULL, '2026-11-08 09:02:29', '2026-11-08 09:02:29');
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (5, 'Rudi Hartono', 'INS-AUTO-001', '3312411106', 'inspector_automotive', 1, '2', 'rudi.hartono@iqc.local', NULL, '$2y$12$DltFblUSKVL8zN9KKfnYqO/n9O1XJRShr93dMSkRva5cxR/N.cxpm', NULL, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (6, 'Ahmad Fauzi', 'INS-WH-001', '3312411107', 'inspector_wire_harness', 1, '1', 'ahmad.fauzi@iqc.local', NULL, '$2y$12$zUp.aJ53E5AinchczIEzPumaLboz74eaKMPoAl31FnGhU8uNLdhUG', NULL, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
UNLOCK TABLES;

-- Data for table `suppliers`
LOCK TABLES `suppliers` WRITE;
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (1, 'SUP-STM-01', 'PT STMicroelectronics Indonesia', 'Jl. Beringin Mukakuning, Batamindo Industrial Park, Batam', '+62 778 611200', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (2, 'SUP-TDK-02', 'PT TDK Electronics Indonesia', 'Kawasan Industri Panbil Lot 1-4, Mukakuning, Batam', '+62 778 404100', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (3, 'SUP-OMR-03', 'PT Omron Manufacturing Indonesia', 'EJIP Industrial Park Plot 5C, Cikarang Selatan, Bekasi', '+62 21 8970111', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (4, 'SUP-TXI-04', 'PT Texas Instruments Batam', 'Batamindo Industrial Park Blok 12, Batam', '+62 778 611500', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (5, 'SUP-HRS-05', 'PT Hirose Electric Indonesia', 'EJIP Industrial Park Plot 3B-1, Cikarang, Jawa Barat', '+62 21 8970222', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (6, 'SUP-YAZ-06', 'PT Yazaki Wiring Technologies', 'Kota Bukit Indah Industrial Estate, Purwakarta', '+62 264 351888', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (7, 'SUP-DEN-07', 'PT Denso Indonesia', 'Kawasan Industri MM2100 Blok KK, Cikarang Barat', '+62 21 8980333', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
UNLOCK TABLES;

-- Data for table `parts`
LOCK TABLES `parts` WRITE;
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (1, 'EE-MCU-3204-STM', 'Microcontroller 32-Bit ARM Cortex-M4', 'LQFP-64 Package, 168MHz, 512KB Flash, RoHS Compliant', 'ee_part', 'MIL-STD-105E AQL 0.65', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (2, 'CAP-CER-0805-10U', 'SMD Ceramic Chip Capacitor 10µF 25V', 'Package 0805 X7R ±10%, Tape & Reel Packaging', 'ee_part', 'MIL-STD-105E AQL 1.0', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (3, 'IC-REG-LM2596-5V', 'Step-Down Voltage Regulator IC 5.0V 3A', 'TO-263-5 Surface Mount, Fixed 5.0V Output', 'ee_part', 'MIL-STD-105E AQL 0.65', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (4, 'AUTO-RLY-12V-40A', 'Automotive Power Relay 12V 40A Sealed', 'ISO Micro 280 Terminal, IP67 Dust/Water Resistant', 'automotive', 'MIL-STD-105E AQL 0.40', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (5, 'AUTO-SNS-SPD-01', 'Wheel Speed Sensor Magnetic Pickup', 'Hall Effect Wheel Speed Sensor, Operating -40C to 125C', 'automotive', 'MIL-STD-105E AQL 0.40', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (6, 'WH-CONN-HDR-20P', 'Wire Harness Header Connector 20-Pin 2.54mm', 'Dual Row Shrouded Header with Positive Locking Mechanism', 'wire_harness', 'MIL-STD-105E AQL 1.0', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (7, 'WH-CBL-UL1007-22', 'Hook-Up Lead Wire UL1007 AWG22 PVC Stranded', '300V 80C tinned copper wire, 1000ft reel', 'wire_harness', 'MIL-STD-105E AQL 1.0', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
UNLOCK TABLES;

-- Data for table `part_parameters`
LOCK TABLES `part_parameters` WRITE;
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (1, 1, 'Panjang Body (mm)', 14, 13.9, 14.1, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (2, 1, 'Lebar Body (mm)', 14, 13.9, 14.1, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (3, 1, 'Pin Pitch (mm)', 0.5, 0.45, 0.55, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (4, 2, 'Panjang (mm)', 2, 1.9, 2.1, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (5, 2, 'Lebar (mm)', 1.25, 1.15, 1.35, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (6, 2, 'Kapasitansi (µF)', 10, 9, 11, 'µF', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (7, 3, 'Output Voltage (V)', 5, 4.85, 5.15, 'V', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (8, 3, 'Quiescent Current (mA)', 5, 4, 6, 'mA', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (9, 4, 'Coil Resistance (Ω)', 85, 80, 90, 'Ω', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (10, 4, 'Pull-in Voltage (V)', 7.5, 7, 8, 'V', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (11, 4, 'Terminal Width (mm)', 6.3, 6.2, 6.4, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (12, 5, 'Internal Resistance (Ω)', 1200, 1100, 1300, 'Ω', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (13, 6, 'Pin Pitch (mm)', 2.54, 2.49, 2.59, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (14, 6, 'Terminal Insertion Force (N)', 25, 20, 30, 'N', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (15, 7, 'Outer Diameter (mm)', 1.6, 1.5, 1.7, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
UNLOCK TABLES;

-- Data for table `defect_categories`
LOCK TABLES `defect_categories` WRITE;
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (1, 'DEF-PIN-01', 'Solder Bridge / Pin Bent', 'Pin bengkok, patah, atau terdapat short solder bridge antar pin IC.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (2, 'DEF-PKG-02', 'Damaged Packaging / Seal Void', 'Kemasan reel atau moisture barrier bag sobek atau HIC (Humidity Indicator) berubah warna.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (3, 'DEF-OX-03', 'Lead Pin Oxidation / Tarnish', 'Oksidasi pada terminal atau kaki komponen yang mengganggu solderability.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (4, 'DEF-CRK-04', 'Ceramic Body Crack / Chipping', 'Retakan mikro atau gumpil pada body keramik kapasitor atau IC mold package.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (5, 'DEF-POL-05', 'Reverse Polarity / Marking Erased', 'Orientasi marking dot pin 1 terbalik atau kode part pudar/terhapus.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (6, 'DEF-INS-06', 'Terminal Crimping Loose / Pull-out', 'Crimping terminal wire harness longgar atau tidak tahan uji tarik.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
UNLOCK TABLES;

-- Data for table `receivings`
LOCK TABLES `receivings` WRITE;
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (1, 'GRN-202611-001', '2026-11-08 05:02:30', 1, 1, 'PO-2026-EE-0891', 'LOT-STM-2610A', 2500, 'menunggu_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (2, 'GRN-202611-002', '2026-11-07 17:02:31', 2, 2, 'PO-2026-EE-0892', 'LOT-TDK-9901X', 12000, 'menunggu_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (3, 'GRN-202611-003', '2026-11-07 01:02:31', 7, 4, 'PO-2026-AUTO-0112', 'LOT-DEN-8812B', 3000, 'menunggu_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (4, 'GRN-202611-004', '2026-11-08 01:02:31', 5, 6, 'PO-2026-WH-0433', 'LOT-HRS-8810A', 5000, 'dalam_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (5, 'GRN-202611-005', '2026-11-06 09:02:31', 4, 3, 'PO-2026-EE-0870', 'LOT-TXI-4480D', 3200, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (6, 'GRN-202611-006', '2026-11-05 09:02:31', 3, 1, 'PO-2026-EE-0865', 'LOT-OMR-7711C', 1500, 'dalam_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (7, 'GRN-202611-007', '2026-11-07 09:02:31', 5, 6, 'PO-2026-WH-0439', 'LOT-HRS-8899Z', 2000, 'dalam_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (8, 'GRN-202610-091', '2026-11-01 09:02:31', 1, 1, 'PO-HIST-001', 'LOT-HIST-901', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (9, 'GRN-202610-092', '2026-11-02 09:02:31', 1, 2, 'PO-HIST-002', 'LOT-HIST-902', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (10, 'GRN-202610-093', '2026-11-03 09:02:31', 1, 4, 'PO-HIST-003', 'LOT-HIST-903', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (11, 'GRN-202610-094', '2026-11-04 09:02:31', 1, 6, 'PO-HIST-004', 'LOT-HIST-904', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (12, 'GRN-202610-095', '2026-11-05 09:02:31', 1, 1, 'PO-HIST-005', 'LOT-HIST-905', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (13, 'GRN-202610-096', '2026-11-06 09:02:31', 1, 2, 'PO-HIST-006', 'LOT-HIST-906', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `logbooks`
LOCK TABLES `logbooks` WRITE;
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (1, 5, 4, 5, 'accept', '2026-11-06 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (2, 6, 4, 5, 'menunggu_penetapan', '2026-11-08 04:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (3, 7, 6, 5, 'perlu_revisi', '2026-11-07 23:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (4, 8, 4, 5, 'rework', '2026-11-02 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (5, 9, 4, 5, 'return_to_vendor', '2026-11-03 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (6, 10, 4, 5, 'accept', '2026-11-04 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (7, 11, 4, 5, 'rework', '2026-11-05 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (8, 12, 4, 5, 'scrap', '2026-11-06 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (9, 13, 4, 5, 'return_to_vendor', '2026-11-07 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `measurements`
LOCK TABLES `measurements` WRITE;
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (1, 1, 7, 1, 5.03, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (2, 1, 7, 2, 4.99, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (3, 1, 7, 3, 4.99, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (4, 1, 7, 4, 5.1, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (5, 1, 7, 5, 5.05, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (6, 1, 8, 1, 5.04, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (7, 1, 8, 2, 4.92, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (8, 1, 8, 3, 4.99, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (9, 1, 8, 4, 5.01, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (10, 1, 8, 5, 5, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (11, 2, 1, 1, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (12, 2, 1, 2, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (13, 2, 1, 3, 14.35, 'fail', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (14, 2, 1, 4, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (15, 2, 1, 5, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (16, 2, 2, 1, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (17, 2, 2, 2, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (18, 2, 2, 3, 14.35, 'fail', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (19, 2, 2, 4, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (20, 2, 2, 5, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (21, 2, 3, 1, 0.5, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (22, 2, 3, 2, 0.5, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (23, 2, 3, 3, 14.35, 'fail', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (24, 2, 3, 4, 0.5, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (25, 2, 3, 5, 0.5, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `defects`
LOCK TABLES `defects` WRITE;
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (1, 2, 1, 4, 'critical', 'Ditemukan pin 12 dan 13 bengkok parah (bent leads) pada 4 chip dalam reel 1.', 'defects/sample_bent_pin.jpg', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (2, 4, 1, 12, 'major', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (3, 5, 4, 8, 'critical', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (4, 6, 2, 5, 'minor', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (5, 7, 6, 7, 'major', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (6, 8, 3, 6, 'major', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (7, 9, 5, 3, 'critical', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `nest_processes`
LOCK TABLES `nest_processes` WRITE;
INSERT INTO `nest_processes` (`id`, `logbook_id`, `inspector_id`, `kronologi_masalah`, `bukti_pendukung`, `diajukan_pada`, `created_at`, `updated_at`) VALUES (1, 2, 4, 'Pada saat inspeksi visual dan pengukuran sample #3, ditemukan anomali dimensi body dan pin pitch melengkung. Lot berpotensi menyebabkan solder bridging pada lini SMT jika langsung diproses.', 'nest/nest_evidence_001.pdf', '2026-11-08 05:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `mrb_dispositions`
LOCK TABLES `mrb_dispositions` WRITE;
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (1, 1, 3, 'accept', 'Semua parameter dimensi dan output voltage dalam rentang toleransi standar. Lolos QC.', '2026-11-06 11:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (2, 3, 3, 'perlu_revisi', 'Mohon lakukan verifikasi ulang pada alat ukur Caliper No. CAL-04 dan ukur 5 sampel tambahan pada reel ke-2.', '2026-11-08 03:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (3, 4, 3, 'rework', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-02 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (4, 5, 3, 'return_to_vendor', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-03 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (5, 6, 3, 'accept', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-04 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (6, 7, 3, 'rework', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-05 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (7, 8, 3, 'scrap', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-06 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (8, 9, 3, 'return_to_vendor', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-07 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `audit_trails`
LOCK TABLES `audit_trails` WRITE;
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (1, 1, 'SYSTEM_INIT', 'Database Schema v5.0 (Proposal 12 Entities)', NULL, 'Database initialized with 12 entities and 6 roles', '127.0.0.1', '2026-11-03 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (2, 2, 'CREATE_GRN', 'Receiving GRN-202611-001 (Part: EE-MCU-3204-STM)', NULL, 'Lot LOT-STM-2610A Qty 2500 enrolled to incoming queue', '192.168.1.45', '2026-11-08 05:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (3, 4, 'POKA_YOKE_REJECTED', 'Receiving GRN-202611-003 (Part: AUTO-RLY-12V-40A, Komoditas: Automotive)', 'Inspector Role: inspector_ee (Commodity: ee_part)', 'BLOCKED: Komoditas material (automotive) tidak cocok dengan hak akses inspektur!', '192.168.1.88', '2026-11-08 06:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (4, 4, 'NEST_INITIATED', 'Logbook #2 (GRN-202611-006)', 'Status: draft', 'Status: menunggu_penetapan (Kronologi NEST diajukan ke Supervisor)', '192.168.1.88', '2026-11-08 05:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (5, 3, 'MRB_DISPOSITION', 'Logbook #1', 'Status: draft', 'Status: accept (Disposisi: Accept / Lolos)', '192.168.1.12', '2026-11-06 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (6, 4, 'POKA_YOKE_REJECTED', 'Receiving GRN-TEST-5407 (Part: AUTO-RLY-12V-40A)', 'Inspector: inspector_ee', 'BLOCKED by Poka-Yoke Guard', '127.0.0.1', '2026-11-08 09:34:22', '2026-11-08 09:34:22', '2026-11-08 09:34:22');
UNLOCK TABLES;


-- ============================================================
-- SEEDED DATA FOR PT OSI ELECTRONICS BATAM
-- ============================================================

-- Data for table `users`
LOCK TABLES `users` WRITE;
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (1, 'Aditya Ryan Wardana', 'ADM-001', 'ADM-001', 'admin', 1, '1', 'admin@iqc.local', NULL, '$2y$12$KnTbpRYTTYfjXYxB2fIK5uCgX.CuufWIsucOKdj0jmNCWc5ltqyhe', NULL, '2026-11-08 09:02:28', '2026-11-08 09:02:28');
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (2, 'Bambang Sudarsono', 'LOG-001', 'LOG-001', 'master_data', 1, '1', 'logistik@iqc.local', NULL, '$2y$12$X.NWjhZT6xFrUmXwnm4Jm.qS/NKGES9QQqqsS2kGDZ6XKk5fOpDtG', NULL, '2026-11-08 09:02:29', '2026-11-08 09:02:29');
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (3, 'Kislistiyono', 'SPV-001', 'SPV-001', 'supervisor', 1, '1', 'supervisor@iqc.local', NULL, '$2y$12$kiUNYwpbikO7u8FyI00msu3UYY.xp7e.qQSz5PhuGhYipSP0dKqpW', NULL, '2026-11-08 09:02:29', '2026-11-08 09:02:29');
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (4, 'Serlin Yolanda', 'INS-EE-001', '3312411105', 'inspector_ee', 1, '1', 'serlin.yolanda@iqc.local', NULL, '$2y$12$y0TErG5VjNiBxswXEefuv.SiWoJXA3SnGdu38e4K9SGAb9gwk7sve', NULL, '2026-11-08 09:02:29', '2026-11-08 09:02:29');
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (5, 'Rudi Hartono', 'INS-AUTO-001', '3312411106', 'inspector_automotive', 1, '2', 'rudi.hartono@iqc.local', NULL, '$2y$12$DltFblUSKVL8zN9KKfnYqO/n9O1XJRShr93dMSkRva5cxR/N.cxpm', NULL, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `users` (`id`, `name`, `nik`, `nip`, `role`, `status_aktif`, `shift`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (6, 'Ahmad Fauzi', 'INS-WH-001', '3312411107', 'inspector_wire_harness', 1, '1', 'ahmad.fauzi@iqc.local', NULL, '$2y$12$zUp.aJ53E5AinchczIEzPumaLboz74eaKMPoAl31FnGhU8uNLdhUG', NULL, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
UNLOCK TABLES;

-- Data for table `suppliers`
LOCK TABLES `suppliers` WRITE;
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (1, 'SUP-STM-01', 'PT STMicroelectronics Indonesia', 'Jl. Beringin Mukakuning, Batamindo Industrial Park, Batam', '+62 778 611200', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (2, 'SUP-TDK-02', 'PT TDK Electronics Indonesia', 'Kawasan Industri Panbil Lot 1-4, Mukakuning, Batam', '+62 778 404100', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (3, 'SUP-OMR-03', 'PT Omron Manufacturing Indonesia', 'EJIP Industrial Park Plot 5C, Cikarang Selatan, Bekasi', '+62 21 8970111', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (4, 'SUP-TXI-04', 'PT Texas Instruments Batam', 'Batamindo Industrial Park Blok 12, Batam', '+62 778 611500', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (5, 'SUP-HRS-05', 'PT Hirose Electric Indonesia', 'EJIP Industrial Park Plot 3B-1, Cikarang, Jawa Barat', '+62 21 8970222', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (6, 'SUP-YAZ-06', 'PT Yazaki Wiring Technologies', 'Kota Bukit Indah Industrial Estate, Purwakarta', '+62 264 351888', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `kontak`, `negara_asal`, `status_aktif`, `created_at`, `updated_at`) VALUES (7, 'SUP-DEN-07', 'PT Denso Indonesia', 'Kawasan Industri MM2100 Blok KK, Cikarang Barat', '+62 21 8980333', 'Indonesia', 1, '2026-11-08 09:02:30', '2026-11-08 09:02:30');
UNLOCK TABLES;

-- Data for table `parts`
LOCK TABLES `parts` WRITE;
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (1, 'EE-MCU-3204-STM', 'Microcontroller 32-Bit ARM Cortex-M4', 'LQFP-64 Package, 168MHz, 512KB Flash, RoHS Compliant', 'ee_part', 'MIL-STD-105E AQL 0.65', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (2, 'CAP-CER-0805-10U', 'SMD Ceramic Chip Capacitor 10µF 25V', 'Package 0805 X7R ±10%, Tape & Reel Packaging', 'ee_part', 'MIL-STD-105E AQL 1.0', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (3, 'IC-REG-LM2596-5V', 'Step-Down Voltage Regulator IC 5.0V 3A', 'TO-263-5 Surface Mount, Fixed 5.0V Output', 'ee_part', 'MIL-STD-105E AQL 0.65', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (4, 'AUTO-RLY-12V-40A', 'Automotive Power Relay 12V 40A Sealed', 'ISO Micro 280 Terminal, IP67 Dust/Water Resistant', 'automotive', 'MIL-STD-105E AQL 0.40', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (5, 'AUTO-SNS-SPD-01', 'Wheel Speed Sensor Magnetic Pickup', 'Hall Effect Wheel Speed Sensor, Operating -40C to 125C', 'automotive', 'MIL-STD-105E AQL 0.40', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (6, 'WH-CONN-HDR-20P', 'Wire Harness Header Connector 20-Pin 2.54mm', 'Dual Row Shrouded Header with Positive Locking Mechanism', 'wire_harness', 'MIL-STD-105E AQL 1.0', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `parts` (`id`, `kode_part`, `nama_part`, `spesifikasi`, `kategori_komoditas`, `standar_aql`, `level_inspeksi`, `created_at`, `updated_at`) VALUES (7, 'WH-CBL-UL1007-22', 'Hook-Up Lead Wire UL1007 AWG22 PVC Stranded', '300V 80C tinned copper wire, 1000ft reel', 'wire_harness', 'MIL-STD-105E AQL 1.0', 'General Level II', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
UNLOCK TABLES;

-- Data for table `part_parameters`
LOCK TABLES `part_parameters` WRITE;
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (1, 1, 'Panjang Body (mm)', 14, 13.9, 14.1, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (2, 1, 'Lebar Body (mm)', 14, 13.9, 14.1, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (3, 1, 'Pin Pitch (mm)', 0.5, 0.45, 0.55, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (4, 2, 'Panjang (mm)', 2, 1.9, 2.1, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (5, 2, 'Lebar (mm)', 1.25, 1.15, 1.35, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (6, 2, 'Kapasitansi (µF)', 10, 9, 11, 'µF', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (7, 3, 'Output Voltage (V)', 5, 4.85, 5.15, 'V', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (8, 3, 'Quiescent Current (mA)', 5, 4, 6, 'mA', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (9, 4, 'Coil Resistance (Ω)', 85, 80, 90, 'Ω', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (10, 4, 'Pull-in Voltage (V)', 7.5, 7, 8, 'V', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (11, 4, 'Terminal Width (mm)', 6.3, 6.2, 6.4, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (12, 5, 'Internal Resistance (Ω)', 1200, 1100, 1300, 'Ω', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (13, 6, 'Pin Pitch (mm)', 2.54, 2.49, 2.59, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (14, 6, 'Terminal Insertion Force (N)', 25, 20, 30, 'N', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `part_parameters` (`id`, `part_id`, `nama_parameter`, `nominal`, `batas_min`, `batas_max`, `satuan`, `created_at`, `updated_at`) VALUES (15, 7, 'Outer Diameter (mm)', 1.6, 1.5, 1.7, 'mm', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
UNLOCK TABLES;

-- Data for table `defect_categories`
LOCK TABLES `defect_categories` WRITE;
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (1, 'DEF-PIN-01', 'Solder Bridge / Pin Bent', 'Pin bengkok, patah, atau terdapat short solder bridge antar pin IC.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (2, 'DEF-PKG-02', 'Damaged Packaging / Seal Void', 'Kemasan reel atau moisture barrier bag sobek atau HIC (Humidity Indicator) berubah warna.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (3, 'DEF-OX-03', 'Lead Pin Oxidation / Tarnish', 'Oksidasi pada terminal atau kaki komponen yang mengganggu solderability.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (4, 'DEF-CRK-04', 'Ceramic Body Crack / Chipping', 'Retakan mikro atau gumpil pada body keramik kapasitor atau IC mold package.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (5, 'DEF-POL-05', 'Reverse Polarity / Marking Erased', 'Orientasi marking dot pin 1 terbalik atau kode part pudar/terhapus.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
INSERT INTO `defect_categories` (`id`, `kode_defect`, `nama_defect`, `deskripsi`, `created_at`, `updated_at`) VALUES (6, 'DEF-INS-06', 'Terminal Crimping Loose / Pull-out', 'Crimping terminal wire harness longgar atau tidak tahan uji tarik.', '2026-11-08 09:02:30', '2026-11-08 09:02:30');
UNLOCK TABLES;

-- Data for table `receivings`
LOCK TABLES `receivings` WRITE;
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (1, 'GRN-202611-001', '2026-11-08 05:02:30', 1, 1, 'PO-2026-EE-0891', 'LOT-STM-2610A', 2500, 'menunggu_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (2, 'GRN-202611-002', '2026-11-07 17:02:31', 2, 2, 'PO-2026-EE-0892', 'LOT-TDK-9901X', 12000, 'menunggu_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (3, 'GRN-202611-003', '2026-11-07 01:02:31', 7, 4, 'PO-2026-AUTO-0112', 'LOT-DEN-8812B', 3000, 'menunggu_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (4, 'GRN-202611-004', '2026-11-08 01:02:31', 5, 6, 'PO-2026-WH-0433', 'LOT-HRS-8810A', 5000, 'dalam_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (5, 'GRN-202611-005', '2026-11-06 09:02:31', 4, 3, 'PO-2026-EE-0870', 'LOT-TXI-4480D', 3200, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (6, 'GRN-202611-006', '2026-11-05 09:02:31', 3, 1, 'PO-2026-EE-0865', 'LOT-OMR-7711C', 1500, 'dalam_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (7, 'GRN-202611-007', '2026-11-07 09:02:31', 5, 6, 'PO-2026-WH-0439', 'LOT-HRS-8899Z', 2000, 'dalam_inspeksi', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (8, 'GRN-202610-091', '2026-11-01 09:02:31', 1, 1, 'PO-HIST-001', 'LOT-HIST-901', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (9, 'GRN-202610-092', '2026-11-02 09:02:31', 1, 2, 'PO-HIST-002', 'LOT-HIST-902', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (10, 'GRN-202610-093', '2026-11-03 09:02:31', 1, 4, 'PO-HIST-003', 'LOT-HIST-903', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (11, 'GRN-202610-094', '2026-11-04 09:02:31', 1, 6, 'PO-HIST-004', 'LOT-HIST-904', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (12, 'GRN-202610-095', '2026-11-05 09:02:31', 1, 1, 'PO-HIST-005', 'LOT-HIST-905', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `receivings` (`id`, `no_grn_iqa`, `tanggal_terima`, `supplier_id`, `part_id`, `no_po`, `no_lot`, `qty_datang`, `status_antrean`, `created_at`, `updated_at`) VALUES (13, 'GRN-202610-096', '2026-11-06 09:02:31', 1, 2, 'PO-HIST-006', 'LOT-HIST-906', 4000, 'selesai', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `logbooks`
LOCK TABLES `logbooks` WRITE;
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (1, 5, 4, 5, 'accept', '2026-11-06 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (2, 6, 4, 5, 'menunggu_penetapan', '2026-11-08 04:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (3, 7, 6, 5, 'perlu_revisi', '2026-11-07 23:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (4, 8, 4, 5, 'rework', '2026-11-02 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (5, 9, 4, 5, 'return_to_vendor', '2026-11-03 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (6, 10, 4, 5, 'accept', '2026-11-04 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (7, 11, 4, 5, 'rework', '2026-11-05 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (8, 12, 4, 5, 'scrap', '2026-11-06 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `logbooks` (`id`, `receiving_id`, `inspector_id`, `qty_sampel`, `status`, `tanggal_inspeksi`, `created_at`, `updated_at`) VALUES (9, 13, 4, 5, 'return_to_vendor', '2026-11-07 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `measurements`
LOCK TABLES `measurements` WRITE;
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (1, 1, 7, 1, 5.03, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (2, 1, 7, 2, 4.99, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (3, 1, 7, 3, 4.99, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (4, 1, 7, 4, 5.1, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (5, 1, 7, 5, 5.05, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (6, 1, 8, 1, 5.04, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (7, 1, 8, 2, 4.92, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (8, 1, 8, 3, 4.99, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (9, 1, 8, 4, 5.01, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (10, 1, 8, 5, 5, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (11, 2, 1, 1, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (12, 2, 1, 2, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (13, 2, 1, 3, 14.35, 'fail', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (14, 2, 1, 4, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (15, 2, 1, 5, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (16, 2, 2, 1, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (17, 2, 2, 2, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (18, 2, 2, 3, 14.35, 'fail', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (19, 2, 2, 4, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (20, 2, 2, 5, 14, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (21, 2, 3, 1, 0.5, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (22, 2, 3, 2, 0.5, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (23, 2, 3, 3, 14.35, 'fail', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (24, 2, 3, 4, 0.5, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `measurements` (`id`, `logbook_id`, `part_parameter_id`, `sample_number`, `nilai_ukur`, `status_toleransi`, `created_at`, `updated_at`) VALUES (25, 2, 3, 5, 0.5, 'pass', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `defects`
LOCK TABLES `defects` WRITE;
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (1, 2, 1, 4, 'critical', 'Ditemukan pin 12 dan 13 bengkok parah (bent leads) pada 4 chip dalam reel 1.', 'defects/sample_bent_pin.jpg', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (2, 4, 1, 12, 'major', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (3, 5, 4, 8, 'critical', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (4, 6, 2, 5, 'minor', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (5, 7, 6, 7, 'major', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (6, 8, 3, 6, 'major', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `defects` (`id`, `logbook_id`, `defect_category_id`, `qty_defect`, `tingkat_keparahan`, `deskripsi`, `foto_bukti`, `created_at`, `updated_at`) VALUES (7, 9, 5, 3, 'critical', 'Defect historis untuk analisis Pareto IQC.', NULL, '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `nest_processes`
LOCK TABLES `nest_processes` WRITE;
INSERT INTO `nest_processes` (`id`, `logbook_id`, `inspector_id`, `kronologi_masalah`, `bukti_pendukung`, `diajukan_pada`, `created_at`, `updated_at`) VALUES (1, 2, 4, 'Pada saat inspeksi visual dan pengukuran sample #3, ditemukan anomali dimensi body dan pin pitch melengkung. Lot berpotensi menyebabkan solder bridging pada lini SMT jika langsung diproses.', 'nest/nest_evidence_001.pdf', '2026-11-08 05:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `mrb_dispositions`
LOCK TABLES `mrb_dispositions` WRITE;
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (1, 1, 3, 'accept', 'Semua parameter dimensi dan output voltage dalam rentang toleransi standar. Lolos QC.', '2026-11-06 11:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (2, 3, 3, 'perlu_revisi', 'Mohon lakukan verifikasi ulang pada alat ukur Caliper No. CAL-04 dan ukur 5 sampel tambahan pada reel ke-2.', '2026-11-08 03:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (3, 4, 3, 'rework', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-02 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (4, 5, 3, 'return_to_vendor', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-03 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (5, 6, 3, 'accept', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-04 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (6, 7, 3, 'rework', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-05 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (7, 8, 3, 'scrap', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-06 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `mrb_dispositions` (`id`, `logbook_id`, `supervisor_id`, `status_keputusan`, `catatan`, `diputuskan_pada`, `created_at`, `updated_at`) VALUES (8, 9, 3, 'return_to_vendor', 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.', '2026-11-07 12:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
UNLOCK TABLES;

-- Data for table `audit_trails`
LOCK TABLES `audit_trails` WRITE;
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (1, 1, 'SYSTEM_INIT', 'Database Schema v5.0 (Proposal 12 Entities)', NULL, 'Database initialized with 12 entities and 6 roles', '127.0.0.1', '2026-11-03 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (2, 2, 'CREATE_GRN', 'Receiving GRN-202611-001 (Part: EE-MCU-3204-STM)', NULL, 'Lot LOT-STM-2610A Qty 2500 enrolled to incoming queue', '192.168.1.45', '2026-11-08 05:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (3, 4, 'POKA_YOKE_REJECTED', 'Receiving GRN-202611-003 (Part: AUTO-RLY-12V-40A, Komoditas: Automotive)', 'Inspector Role: inspector_ee (Commodity: ee_part)', 'BLOCKED: Komoditas material (automotive) tidak cocok dengan hak akses inspektur!', '192.168.1.88', '2026-11-08 06:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (4, 4, 'NEST_INITIATED', 'Logbook #2 (GRN-202611-006)', 'Status: draft', 'Status: menunggu_penetapan (Kronologi NEST diajukan ke Supervisor)', '192.168.1.88', '2026-11-08 05:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (5, 3, 'MRB_DISPOSITION', 'Logbook #1', 'Status: draft', 'Status: accept (Disposisi: Accept / Lolos)', '192.168.1.12', '2026-11-06 09:02:31', '2026-11-08 09:02:31', '2026-11-08 09:02:31');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (6, 4, 'POKA_YOKE_REJECTED', 'Receiving GRN-TEST-5407 (Part: AUTO-RLY-12V-40A)', 'Inspector: inspector_ee', 'BLOCKED by Poka-Yoke Guard', '127.0.0.1', '2026-11-08 09:34:22', '2026-11-08 09:34:22', '2026-11-08 09:34:22');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (7, 1, 'ROLE_ACCESS_DENIED', 'Route: logbooks', 'User Role: admin', 'Required Roles: inspector, supervisor', '127.0.0.1', '2026-11-08 09:53:38', '2026-11-08 09:53:38', '2026-11-08 09:53:38');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (8, 1, 'ROLE_ACCESS_DENIED', 'Route: reports', 'User Role: admin', 'Required Roles: supervisor', '127.0.0.1', '2026-11-08 09:53:39', '2026-11-08 09:53:39', '2026-11-08 09:53:39');
INSERT INTO `audit_trails` (`id`, `user_id`, `aksi`, `objek_data`, `nilai_sebelum`, `nilai_sesudah`, `alamat_ip`, `waktu`, `created_at`, `updated_at`) VALUES (9, 1, 'ROLE_ACCESS_DENIED', 'Route: supervisor/mrb', 'User Role: admin', 'Required Roles: supervisor', '127.0.0.1', '2026-11-08 09:53:44', '2026-11-08 09:53:44', '2026-11-08 09:53:44');
UNLOCK TABLES;

-- Data for table `instruments`
LOCK TABLES `instruments` WRITE;
INSERT INTO `instruments` (`id`, `instrument_code`, `name`, `brand_model`, `measuring_range`, `calibration_due_date`, `last_verified_at`, `created_at`, `updated_at`) VALUES (1, 'INS-CAL-01', 'Vernier Caliper Digital', 'Mitutoyo 500-196-30', '0 - 150 mm (Resolusi 0.01 mm)', '2027-05-15', '2026-11-08', '2026-11-08 10:06:01', '2026-11-08 10:06:01');
INSERT INTO `instruments` (`id`, `instrument_code`, `name`, `brand_model`, `measuring_range`, `calibration_due_date`, `last_verified_at`, `created_at`, `updated_at`) VALUES (2, 'INS-MIC-01', 'Digital Outside Micrometer', 'Mitutoyo 293-240-30', '0 - 25 mm (Resolusi 0.001 mm)', '2027-06-20', '2026-11-08', '2026-11-08 10:06:01', '2026-11-08 10:06:01');
INSERT INTO `instruments` (`id`, `instrument_code`, `name`, `brand_model`, `measuring_range`, `calibration_due_date`, `last_verified_at`, `created_at`, `updated_at`) VALUES (3, 'INS-DMM-01', 'Precision Digital Multimeter', 'Fluke 87V Industrial', '0 - 1000 V / Resistance / Capacitance', '2027-08-10', '2026-11-08', '2026-11-08 10:06:01', '2026-11-08 10:06:01');
INSERT INTO `instruments` (`id`, `instrument_code`, `name`, `brand_model`, `measuring_range`, `calibration_due_date`, `last_verified_at`, `created_at`, `updated_at`) VALUES (4, 'INS-PIN-01', 'Pin Gauge Set Grade 1', 'EISEN EP Series', '0.200 - 10.000 mm (Toleransi +-0.001 mm)', '2027-11-01', '2026-11-08', '2026-11-08 10:06:01', '2026-11-08 10:06:01');
INSERT INTO `instruments` (`id`, `instrument_code`, `name`, `brand_model`, `measuring_range`, `calibration_due_date`, `last_verified_at`, `created_at`, `updated_at`) VALUES (5, 'INS-OPT-01', 'High-Accuracy Optical Microscope', 'Keyence VHX-7000', 'Perbesaran 20x - 2000x 4K', '2027-09-30', '2026-11-08', '2026-11-08 10:06:01', '2026-11-08 10:06:01');
UNLOCK TABLES;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
