<?php
include 'koneksi.php';

if (!isset($_POST['nisn']) || trim($_POST['nisn']) === '') {
    die("NISN tidak ditemukan. <a href='scan.php?mode=masuk'>Kembali</a>");
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
    /* Cek apakah sudah punya data absensi hari ini */
    $cek = $koneksi->prepare("SELECT id, jam_masuk, status_masuk FROM absensi WHERE nisn = ? AND tanggal = ? LIMIT 1");
    $cek->bind_param("ss", $nisn, $tanggal);
    $cek->execute();
    $dataAbsen = $cek->get_result()->fetch_assoc();
    $cek->close();

    $sudahMasuk = $dataAbsen
        && !empty($dataAbsen['jam_masuk'])
        && in_array($dataAbsen['status_masuk'], ['Hadir', 'Terlambat'], true);

    if ($sudahMasuk) {
        $status = "sudah";
        $pesan = "ANDA SUDAH ABSEN MASUK HARI INI";
        $jamTercatat = $dataAbsen['jam_masuk'];
    } else {
        /* Tentukan status berdasarkan pengaturan */
        $setting = $koneksi->query(
            "SELECT jam_masuk, batas_terlambat
             FROM pengaturan_absensi
             ORDER BY id DESC LIMIT 1"
        )->fetch_assoc();

        $batasTerlambat = $setting['batas_terlambat'] ?? '07:15:00';
        $statusMasuk = ($jam <= $batasTerlambat) ? 'Hadir' : 'Terlambat';

        if ($dataAbsen) {
            $insert = $koneksi->prepare(
                "UPDATE absensi
                 SET jam_masuk = ?, status_masuk = ?
                 WHERE id = ?"
            );
            $insert->bind_param("ssi", $jam, $statusMasuk, $dataAbsen['id']);
        } else {
            $insert = $koneksi->prepare(
                "INSERT INTO absensi (nisn, tanggal, jam_masuk, status_masuk)
                 VALUES (?, ?, ?, ?)"
            );
            $insert->bind_param("ssss", $nisn, $tanggal, $jam, $statusMasuk);
        }

        if ($insert->execute()) {
            $status = "berhasil";
            $pesan = "ABSEN MASUK BERHASIL";
        } else {
            $status = "gagal";
            $pesan = "GAGAL MENYIMPAN ABSENSI";
        }

        $insert->close();
        $jamTercatat = $jam;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Hasil Absen Masuk</title>
<link rel="stylesheet" href="style.css">
<meta http-equiv="refresh" content="3;url=scan.php?mode=masuk">
</head>
<body>
<div class="container" style="text-align:center;">
<?php if ($status === "berhasil"): ?>
    <h1 style="color:green;">✓ <?= htmlspecialchars($pesan) ?></h1>
    <p><strong>Nama</strong> : <?= htmlspecialchars($siswa['nama']) ?></p>
    <p><strong>NISN</strong> : <?= htmlspecialchars($siswa['nisn']) ?></p>
    <p><strong>Kelas</strong> : <?= htmlspecialchars($siswa['kelas']) ?></p>
    <p><strong>Jam</strong> : <?= htmlspecialchars($jamTercatat) ?></p>
<?php elseif ($status === "sudah"): ?>
    <h1 style="color:orange;">ANDA SUDAH ABSEN MASUK</h1>
    <p><strong>Nama</strong> : <?= htmlspecialchars($siswa['nama']) ?></p>
    <p><strong>Jam masuk</strong> : <?= htmlspecialchars($jamTercatat) ?></p>
<?php else: ?>
    <h1 style="color:red;"><?= htmlspecialchars($pesan) ?></h1>
<?php endif; ?>

<p>Kembali ke halaman scan dalam 3 detik...</p>
<a href="scan.php?mode=masuk">Kembali sekarang</a>
</div>
</body>
</html>
