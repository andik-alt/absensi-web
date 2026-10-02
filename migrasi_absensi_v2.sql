-- MIGRASI ABSENSI WEB
-- Jalankan setelah memilih database `absensi_sekolah`.
-- Backup database terlebih dahulu sebelum menjalankan.

USE absensi_sekolah;

-- 1. Tambahkan data kontak orang tua ke siswa
ALTER TABLE siswa
    ADD COLUMN no_ortu VARCHAR(30) NULL AFTER foto;

-- 2. Tambahkan jam/status masuk dan pulang ke absensi
ALTER TABLE absensi
    ADD COLUMN jam_masuk TIME NULL AFTER tanggal,
    ADD COLUMN jam_pulang TIME NULL AFTER jam_masuk,
    ADD COLUMN status_masuk VARCHAR(30) NULL AFTER jam_pulang,
    ADD COLUMN status_pulang VARCHAR(30) NULL AFTER status_masuk;

-- 3. Pindahkan data lama:
-- kolom `jam` lama dianggap sebagai jam masuk,
-- kolom `status` lama dianggap sebagai status masuk.
UPDATE absensi
SET jam_masuk = jam,
    status_masuk = status
WHERE jam_masuk IS NULL;

-- 4. Buat tabel pengaturan sistem.
CREATE TABLE IF NOT EXISTS pengaturan_absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jam_masuk TIME NOT NULL DEFAULT '07:00:00',
    batas_terlambat TIME NOT NULL DEFAULT '07:15:00',
    jam_pulang TIME NOT NULL DEFAULT '15:30:00',
    jam_pulang_khusus TIME NULL,
    tanggal_pulang_khusus DATE NULL,
    aktif_pulang_khusus TINYINT(1) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 5. Buat satu pengaturan awal jika belum ada.
INSERT INTO pengaturan_absensi
    (jam_masuk, batas_terlambat, jam_pulang)
SELECT '07:00:00', '07:15:00', '15:30:00'
WHERE NOT EXISTS (SELECT 1 FROM pengaturan_absensi);

-- 6. Setelah kode baru sudah diuji dan semua halaman lama sudah
-- disesuaikan, kolom `jam` dan `status` lama bisa dihapus.
-- JANGAN jalankan dua baris berikut sekarang.
--
-- ALTER TABLE absensi DROP COLUMN jam;
-- ALTER TABLE absensi DROP COLUMN status;
