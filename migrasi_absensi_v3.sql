-- MIGRASI ABSENSI V3
-- Jalankan SETELAH migrasi_absensi_v2.sql. Backup database terlebih dahulu.
--
-- Setelah migrasi v2, kolom lama `jam` dan `status` masih NOT NULL tanpa nilai
-- bawaan, sehingga INSERT dari proses_absen.php (yang hanya mengisi jam_masuk
-- dan status_masuk) gagal di server MySQL/MariaDB mode strict, atau tersimpan
-- dengan jam 00:00:00 dan status kosong di server mode longgar.
-- Perintah ini membuat kedua kolom lama boleh kosong. Data lama tidak berubah.

USE absensi_sekolah;

ALTER TABLE absensi
    MODIFY jam TIME NULL,
    MODIFY status VARCHAR(100) NULL;

-- Opsional, setelah semua halaman diuji dan tidak ada lagi yang memakai kolom lama:
-- ALTER TABLE absensi DROP COLUMN jam;
-- ALTER TABLE absensi DROP COLUMN status;
