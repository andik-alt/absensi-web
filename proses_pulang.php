<?php
include 'koneksi.php';

if (!isset($_POST['nisn']) || trim($_POST['nisn']) === '') {
    die("NISN tidak ditemukan. <a href='scan.php?mode=pulang'>Kembali</a>");
}

$nisn = trim($_POST['nisn']);
$tanggal = date('Y-m-d');
$jam = date('H:i:s');

/* Cari siswa */
$stmt = $koneksi->prepare("SELECT * FROM siswa WHERE nisn = ?");
$stmt->bind_param("s", $nisn);
$stmt->execute();
$siswa = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$siswa) {
    $status = "gagal";
    $pesan = "NISN TIDAK TERDAFTAR";
} else {
    /* Ambil data absensi hari ini */
    $cek = $koneksi->prepare(
        "SELECT id, jam_masuk, jam_pulang, status_masuk
         FROM absensi
         WHERE nisn = ? AND tanggal = ?
         LIMIT 1"
    );
    $cek->bind_param("ss", $nisn, $tanggal);
    $cek->execute();
    $absen = $cek->get_result()->fetch_assoc();
    $cek->close();

    if (!$absen || empty($absen['jam_masuk'])) {
        $status = "belum_masuk";
        $pesan = "SISWA BELUM ABSEN MASUK HARI INI";
    } elseif (!empty($absen['jam_pulang'])) {
        $status = "sudah";
        $pesan = "ANDA SUDAH ABSEN PULANG HARI INI";
        $jamTercatat = $absen['jam_pulang'];
    } else {
        /* Jam pulang normal atau jam pulang khusus */
        $setting = $koneksi->query(
            "SELECT jam_pulang, jam_pulang_khusus, tanggal_pulang_khusus,
                    aktif_pulang_khusus
             FROM pengaturan_absensi
             ORDER BY id DESC LIMIT 1"
        )->fetch_assoc();

        $jamPulang = $setting['jam_pulang'] ?? '15:30:00';

        if (
            !empty($setting['aktif_pulang_khusus']) &&
            $setting['tanggal_pulang_khusus'] === $tanggal &&
            !empty($setting['jam_pulang_khusus'])
        ) {
            $jamPulang = $setting['jam_pulang_khusus'];
        }

        /*
         * Jangan izinkan pulang sebelum jam yang telah ditetapkan.
         * Admin dapat mengubah jam khusus bila sekolah pulang lebih awal.
         */
        if ($jam < $jamPulang) {
            $status = "terlalu_awal";
            $pesan = "BELUM MASUK JAM PULANG";
            $jamTersedia = $jamPulang;
        } else {
            $update = $koneksi->prepare(
                "UPDATE absensi
                 SET jam_pulang = ?, status_pulang = 'Pulang'
                 WHERE id = ?"
            );
            $update->bind_param("si", $jam, $absen['id']);

            if ($update->execute()) {
                $status = "berhasil";
                $pesan = "ABSEN PULANG BERHASIL";
                $jamTercatat = $jam;
            } else {
                $status = "gagal";
                $pesan = "GAGAL MENYIMPAN ABSEN PULANG";
            }

            $update->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Hasil Absen Pulang</title>
<link rel="stylesheet" href="style.css">
<meta http-equiv="refresh" content="3;url=scan.php?mode=pulang">
</head>
<body>
<div class="container" style="text-align:center;">
<?php if ($status === "berhasil"): ?>
    <h1 style="color:green;">✓ <?= htmlspecialchars($pesan) ?></h1>
    <p><strong>Nama</strong> : <?= htmlspecialchars($siswa['nama']) ?></p>
    <p><strong>NISN</strong> : <?= htmlspecialchars($siswa['nisn']) ?></p>
    <p><strong>Kelas</strong> : <?= htmlspecialchars($siswa['kelas']) ?></p>
    <p><strong>Jam pulang</strong> : <?= htmlspecialchars($jamTercatat) ?></p>
<?php elseif ($status === "sudah"): ?>
    <h1 style="color:orange;">ANDA SUDAH ABSEN PULANG</h1>
    <p><strong>Nama</strong> : <?= htmlspecialchars($siswa['nama']) ?></p>
    <p><strong>Jam pulang</strong> : <?= htmlspecialchars($jamTercatat) ?></p>
<?php elseif ($status === "belum_masuk"): ?>
    <h1 style="color:red;">BELUM ABSEN MASUK</h1>
<?php elseif ($status === "terlalu_awal"): ?>
    <h1 style="color:orange;">BELUM MASUK JAM PULANG</h1>
    <p>Jam pulang yang ditetapkan: <strong><?= htmlspecialchars($jamTersedia) ?></strong></p>
<?php else: ?>
    <h1 style="color:red;"><?= htmlspecialchars($pesan) ?></h1>
<?php endif; ?>

<p>Kembali ke halaman scan dalam 3 detik...</p>
<a href="scan.php?mode=pulang">Kembali sekarang</a>
</div>
</body>
</html>
