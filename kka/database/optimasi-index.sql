-- ==================================================================
-- OPTIMASI INDEX KKA — mempercepat query daftar & rekap.
-- Jalankan SATU KALI di phpMyAdmin (pilih database KKA -> tab SQL).
-- AMAN: hanya MENAMBAH index, tidak mengubah/menghapus data.
--
-- CATATAN: bila muncul pesan error "Duplicate key name" pada salah satu
-- baris, ABAIKAN saja — artinya index tersebut sudah ada sebelumnya.
-- ==================================================================

-- Daftar sesi & dashboard selalu diurutkan berdasarkan waktu dibuat
-- (ORDER BY created_at DESC). Index ini menghilangkan proses "filesort".
ALTER TABLE `kka_sesi`     ADD INDEX `idx_sesi_created_at` (`created_at`);

-- Rincian belanja diambil per-sesi lalu diurutkan (WHERE sesi_id ORDER BY urutan, id).
ALTER TABLE `kka_rincian`  ADD INDEX `idx_rincian_sesi_urutan` (`sesi_id`, `urutan`);

-- Lampiran ditampilkan per-sesi diurutkan waktu unggah (WHERE sesi_id ORDER BY created_at DESC).
ALTER TABLE `kka_lampiran` ADD INDEX `idx_lampiran_sesi_created` (`sesi_id`, `created_at`);
