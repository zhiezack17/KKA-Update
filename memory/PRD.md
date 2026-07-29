# KKA - Kertas Kerja Audit
Inspektorat Kabupaten Rokan Hilir · PHP 8.2 + MySQL/MariaDB (aaPanel/VPS)

## Original Problem Statement (Juli 2026)
User (admin@inspektorat-rohil) live di `kka.arsipdigital-inspektorat.com`
meminta 5 perubahan pada aplikasi PHP yang sudah dipakai:
1. Preview Sesi Audit: layout tabel diperbarui + kolom Pagu Anggaran
2. Tombol Back di halaman detail Sesi Audit
3. Rekap per Desa: format tabel baru sesuai template Excel Inspektorat
4. Menu baru "Master KKA" di bawah Rekap per Desa (3 tipe: Standar/Fisik/Sketsa)
5. Fix bug HTTP 500 saat upload lampiran

## Users
- **Admin** — CRUD penuh + kelola pengguna
- **Auditor** — CRUD sesi/rincian/lampiran hanya untuk data yang dibuat/dishare padanya

## Core Requirements
- Sesi Audit dengan identitas KKA lengkap + rincian belanja + lampiran + kesimpulan
- Rekap agregat per (Sub Bidang, Kecamatan, Tahun) — sinkron dengan input Sesi
- Cetak preview A4 + Export Excel
- Master KKA sebagai dokumen tambahan (narasi/pengukuran fisik/foto lapangan)
- Data ownership isolation antar auditor (share via kka_sesi_share)

## What's Been Implemented (Juli 2026, versi 13)
✅ **Preview Sesi Audit (print/sesi.php)**
   - Ref. KKA → Ref. PKA (label saja, kolom DB tetap ref_kka)
   - Kolom baru: No | Uraian | Pagu Anggaran | Realisasi | Dikwitansi | Selisih | Penerima | Keterangan
   - Rumus selisih baru: Realisasi − Dikwitansi

✅ **Sesi/Show + Sesi/Edit + Sesi/Create**
   - Tombol "← Kembali" ke daftar sesi
   - Kolom Pagu Anggaran per rincian di tabel & form input
   - Edit modal (Bootstrap-less) diperbarui dengan field Pagu
   - Label Ref. PKA di semua tempat

✅ **Rincian Controller**
   - store & update sekarang menerima field `pagu_anggaran` per baris

✅ **Rekap per Desa (rekap/index.php + RekapController)**
   - Judul: "REKAP KERTAS KERJA AUDIT — PER DESA"
   - Filter: Bidang → Sub Bidang → Kecamatan → Tahun (kaskade)
   - Grouping: Sub Bidang · Kecamatan · Tahun (bukan lagi per Desa)
   - Kolom: No | Sub Bidang | Kecamatan | Tahun | Jumlah Sesi | Pagu | Realisasi | Dikwitansi | Selisih | Keterangan
   - Info card menampilkan filter aktif
   - Chart update pakai label "Sub Bidang (Kecamatan Tahun)"

✅ **Master KKA (BARU)**
   - 3 tabel: kka_master, kka_master_fisik, kka_master_foto
   - Controller: MasterKkaController (create/read/update/delete/preview/export/foto/download-template)
   - Views: index, create, edit (form beda per tipe), preview (A4 print-ready)
   - Sidebar menu di bawah "Rekap per Desa"
   - Fitur foto: upload multi (JPG/PNG/WEBP/GIF), preview grid, hapus per foto
   - Fitur fisik: tabel dinamis (add/remove baris), auto-hitung Volume = Jarak × ((Lebar I + Lebar II) / 2) × Tebal
   - Download template KKP_MASTER.xls (file real dari user)
   - Export Excel per dokumen

✅ **Upload Lampiran (Fix HTTP 500)**
   - Wrap upload dalam try/catch — tidak ada lagi 500 blank
   - Detail logging via error_log() dengan tag [LampiranUpload]
   - Cek folder writable, auto-create bila belum ada
   - Fallback MIME detection (finfo → mime_content_type → mapping ekstensi)
   - Pesan error UPLOAD_ERR_INI_SIZE menyarankan naikkan config PHP di aaPanel

## Deferred / Backlog
- P2: Import batch Master KKA dari Excel (upload xlsx → parse jadi kka_master*)
- P2: Editor rich text untuk narasi (saat ini plain textarea)
- P2: Digital signature / QR code di dokumen cetak
- P3: Dashboard chart untuk Master KKA (jumlah per tipe, per bulan)
- P3: Notifikasi email ke Ketua Tim saat auditor selesai isi Master

## Deployment Notes
- Codebase adalah PHP native (bukan React/FastAPI) → tidak ada supervisor
- User deploy manual ke VPS aaPanel (dulu cPanel)
- Panduan lengkap di `/app/kka/PANDUAN-v13.txt`
- Migration wajib dijalankan: `database/migration_master_kka.sql`
- Folder `public/uploads` dan `public/uploads/master` harus writable (755/775)

## Files Changed/Added
Modified: SesiController, RincianController, PrintController, RekapController, LampiranController,
          views/sesi/show.php, sesi/create.php, sesi/edit.php, print/sesi.php, rekap/index.php,
          partials/sidebar.php, public/index.php
Added: MasterKkaController, views/master/{index,create,edit,preview}.php,
       database/migration_master_kka.sql, public/uploads/master/KKP_MASTER.xls, PANDUAN-v13.txt

## Test Credentials (local dev only)
Local test DB (MariaDB kka_test) - dibuat sementara untuk smoke test:
- Email: admin@test.local
- Password: Admin@2026
(User production tetap pakai kredensial existing mereka)
