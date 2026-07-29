-- ==================================================================
-- MIGRASI KKA: Fitur "Berbagi Sesi Audit" (per-sesi sharing)
-- Jalankan SATU KALI di phpMyAdmin (pilih database KKA -> tab SQL).
-- Aman: hanya menambah tabel baru, tidak mengubah/menghapus data lama.
-- ==================================================================

CREATE TABLE IF NOT EXISTS `kka_sesi_share` (
  `sesi_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sesi_id`, `user_id`),
  INDEX (`user_id`),
  CONSTRAINT `fk_share_sesi` FOREIGN KEY (`sesi_id`) REFERENCES `kka_sesi`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_share_user` FOREIGN KEY (`user_id`) REFERENCES `kka_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
