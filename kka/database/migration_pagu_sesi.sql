-- =====================================================================
-- MIGRATION: Pindah kolom Pagu Anggaran dari kka_rincian ke kka_sesi
-- Jalankan SEKALI di phpMyAdmin (SQL tab) SETELAH upload file baru.
-- Aman dijalankan berkali-kali (idempotent).
-- =====================================================================

-- 1) Tambah kolom pagu_anggaran di kka_sesi (jika belum ada)
SET @exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'kka_sesi'
    AND COLUMN_NAME  = 'pagu_anggaran'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `kka_sesi` ADD COLUMN `pagu_anggaran` DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER `kegiatan`',
  'SELECT "Kolom pagu_anggaran sudah ada di kka_sesi, dilewati." AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Isi pagu_anggaran sesi berdasarkan MAX pagu rincian yang ada (data lama)
--    Hanya diupdate untuk sesi yang pagu-nya masih 0.
UPDATE `kka_sesi` s
LEFT JOIN (
  SELECT sesi_id, MAX(pagu_anggaran) AS pagu_maks
  FROM `kka_rincian`
  GROUP BY sesi_id
) r ON r.sesi_id = s.id
SET s.pagu_anggaran = COALESCE(r.pagu_maks, 0)
WHERE s.pagu_anggaran = 0;

-- Selesai. Aplikasi sudah siap.
