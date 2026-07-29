-- ==========================================================
-- KKA - Kertas Kerja Audit
-- Inspektorat Kabupaten Rokan Hilir
-- Schema: MySQL / MariaDB
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `kka_lampiran`;
DROP TABLE IF EXISTS `kka_rincian`;
DROP TABLE IF EXISTS `kka_sesi`;
DROP TABLE IF EXISTS `kka_sub_bidang`;
DROP TABLE IF EXISTS `kka_bidang`;
DROP TABLE IF EXISTS `kka_desa`;
DROP TABLE IF EXISTS `kka_kecamatan`;
DROP TABLE IF EXISTS `kka_users`;

-- Users (Admin & Auditor)
CREATE TABLE `kka_users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','auditor') NOT NULL DEFAULT 'auditor',
  `nip` VARCHAR(30) DEFAULT NULL,
  `jabatan` VARCHAR(150) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kecamatan
CREATE TABLE `kka_kecamatan` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Desa / Kepenghuluan
CREATE TABLE `kka_desa` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kecamatan_id` INT UNSIGNED NOT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_desa_kec FOREIGN KEY (`kecamatan_id`) REFERENCES `kka_kecamatan`(`id`) ON DELETE CASCADE,
  INDEX (`kecamatan_id`),
  INDEX (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bidang
CREATE TABLE `kka_bidang` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(200) NOT NULL UNIQUE,
  `urutan` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sub Bidang
CREATE TABLE `kka_sub_bidang` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `bidang_id` INT UNSIGNED NOT NULL,
  `nama` VARCHAR(255) NOT NULL,
  CONSTRAINT fk_sub_bidang FOREIGN KEY (`bidang_id`) REFERENCES `kka_bidang`(`id`) ON DELETE CASCADE,
  INDEX (`bidang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sesi Audit (KKA)
CREATE TABLE `kka_sesi` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `desa_id` INT UNSIGNED NOT NULL,
  `bidang_id` INT UNSIGNED NOT NULL,
  `sub_bidang_id` INT UNSIGNED DEFAULT NULL,
  `objek_audit` VARCHAR(255) NOT NULL,
  `kegiatan` VARCHAR(255) DEFAULT NULL,
  `pagu_anggaran` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `semester` TINYINT NOT NULL DEFAULT 1,
  `tahun_anggaran` SMALLINT NOT NULL,
  `no_kka` VARCHAR(50) DEFAULT NULL,
  `ref_kka` VARCHAR(50) DEFAULT NULL,
  `dibuat_oleh` VARCHAR(120) DEFAULT NULL,
  `tanggal_dibuat` DATE DEFAULT NULL,
  `direview_oleh` VARCHAR(120) DEFAULT NULL,
  `tanggal_review` DATE DEFAULT NULL,
  `dievaluasi_oleh` VARCHAR(120) DEFAULT NULL,
  `tanggal_evaluasi` DATE DEFAULT NULL,
  `kesimpulan` TEXT DEFAULT NULL,
  `sumber_data` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_sesi_desa FOREIGN KEY (`desa_id`) REFERENCES `kka_desa`(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_sesi_bidang FOREIGN KEY (`bidang_id`) REFERENCES `kka_bidang`(`id`),
  CONSTRAINT fk_sesi_subbidang FOREIGN KEY (`sub_bidang_id`) REFERENCES `kka_sub_bidang`(`id`) ON DELETE SET NULL,
  CONSTRAINT fk_sesi_user FOREIGN KEY (`created_by`) REFERENCES `kka_users`(`id`) ON DELETE SET NULL,
  INDEX (`desa_id`), INDEX (`tahun_anggaran`), INDEX (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rincian per Sesi
CREATE TABLE `kka_rincian` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sesi_id` INT UNSIGNED NOT NULL,
  `urutan` INT NOT NULL DEFAULT 1,
  `uraian` VARCHAR(500) NOT NULL,
  `pagu_anggaran` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `biaya_dikwitansi` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `realisasi` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `penerima` VARCHAR(255) DEFAULT NULL,
  `keterangan` TEXT DEFAULT NULL,
  CONSTRAINT fk_rincian_sesi FOREIGN KEY (`sesi_id`) REFERENCES `kka_sesi`(`id`) ON DELETE CASCADE,
  INDEX (`sesi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lampiran (Upload file PDF / Excel / Gambar)
CREATE TABLE `kka_lampiran` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sesi_id` INT UNSIGNED NOT NULL,
  `nama_asli` VARCHAR(255) NOT NULL,
  `nama_file` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `ukuran` INT UNSIGNED DEFAULT 0,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `uploaded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_lamp_sesi FOREIGN KEY (`sesi_id`) REFERENCES `kka_sesi`(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_lamp_user FOREIGN KEY (`uploaded_by`) REFERENCES `kka_users`(`id`) ON DELETE SET NULL,
  INDEX (`sesi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
