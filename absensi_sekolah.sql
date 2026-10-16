-- Database awal Web Absensi (schema terbaru)
-- Gunakan file ini hanya saat membuat database BARU/kosong.
-- Untuk database yang sudah berisi data, JANGAN import file ini ulang.
-- Backup database sebelum melakukan perubahan apa pun.

CREATE DATABASE IF NOT EXISTS absensi_sekolah
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE absensi_sekolah;

CREATE TABLE IF NOT EXISTS admin (
  id INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) DEFAULT NULL,
  password VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS siswa (
  id INT NOT NULL AUTO_INCREMENT,
  nisn VARCHAR(20) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  kelas VARCHAR(20) NOT NULL,
  jurusan VARCHAR(50) NOT NULL,
  foto VARCHAR(255) DEFAULT NULL,
  no_ortu VARCHAR(30) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_siswa_nisn (nisn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS absensi (
  id INT NOT NULL AUTO_INCREMENT,
  nisn VARCHAR(20) NOT NULL,
  tanggal DATE NOT NULL,
  jam_masuk TIME DEFAULT NULL,
  jam_pulang TIME DEFAULT NULL,
  status_masuk VARCHAR(100) DEFAULT NULL,
  status_pulang VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY unik_nisn_tanggal (nisn, tanggal),
  KEY idx_absensi_tanggal (tanggal),
  KEY idx_absensi_nisn (nisn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS pengaturan_absensi (
  id INT NOT NULL AUTO_INCREMENT,
  jam_masuk TIME NOT NULL DEFAULT '07:00:00',
  batas_terlambat TIME NOT NULL DEFAULT '07:15:00',
  jam_pulang TIME NOT NULL DEFAULT '15:30:00',
  jam_pulang_khusus TIME DEFAULT NULL,
  tanggal_pulang_khusus DATE DEFAULT NULL,
  aktif_pulang_khusus TINYINT(1) NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO pengaturan_absensi (jam_masuk, batas_terlambat, jam_pulang)
SELECT '07:00:00', '07:15:00', '15:30:00'
WHERE NOT EXISTS (SELECT 1 FROM pengaturan_absensi);

-- Akun admin dan data siswa dapat ditambahkan melalui aplikasi.
-- Jangan menaruh password asli atau token API ke dalam file SQL yang dipublikasikan.
