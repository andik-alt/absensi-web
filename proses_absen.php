<?php
include 'koneksi.php';

if (!isset($_POST['nisn']) || trim($_POST['nisn']) === "") {
    die("NISN tidak ditemukan. <a href='scan.php'>Kembali</a>");
}

$nisn = trim($_POST['nisn']);
$tanggal = date('Y-m-d');
$jam = date('H:i:s');

$stmt = $koneksi->prepare("SELECT * FROM siswa WHERE nisn = ?");
$stmt->bind_param("s", $nisn);
$stmt->execute();
$result = $stmt->get_result();
$siswa = $result->fetch_assoc();
$stmt->close();

if (!$siswa) {
    $status = "gagal";
    $pesan = "NISN TIDAK TERDAFTAR";
} else {
    $cek = $koneksi->prepare("SELECT * FROM absensi WHERE nisn = ? AND tanggal = ?");
    $cek->bind_param("ss", $nisn, $tanggal);
    $cek->execute();
    $cekResult = $cek->get_result();

    if ($cekResult->num_rows > 0) {
        $status = "sudah";
        $pesan = "ANDA SUDAH ABSEN HARI INI";
    } else {
        $insert = $koneksi->prepare("INSERT INTO absensi (nisn, tanggal, jam, status) VALUES (?, ?, ?, 'Hadir')");
        $insert->bind_param("sss", $nisn, $tanggal, $jam);

        if ($insert->execute()) {
            $status = "berhasil";
            $pesan = "ABSENSI BERHASIL";
        } else {
            $status = "gagal";
            $pesan = "GAGAL MENYIMPAN ABSENSI: " . $koneksi->error;
        }
        $insert->close();
    }
    $cek->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Absensi</title>
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="3;url=scan.php">
</head>
<body>

    <div class="container" style="text-align:center;">

        <?php if ($status === "berhasil"): ?>
            <h1 style="color:green;">✓ ABSENSI BERHASIL</h1>
            <p><strong>Nama</strong> : <?= htmlspecialchars($siswa['nama']) ?></p>
            <p><strong>NISN</strong> : <?= htmlspecialchars($siswa['nisn']) ?></p>
            <p><strong>Kelas</strong> : <?= htmlspecialchars($siswa['kelas']) ?></p>
            <p><strong>Jam</strong> : <?= $jam ?></p>

        <?php elseif ($status === "sudah"): ?>
            <h1 style="color:orange;">ANDA SUDAH ABSEN HARI INI</h1>
            <p><strong>Nama</strong> : <?= htmlspecialchars($siswa['nama']) ?></p>
            <p><strong>NISN</strong> : <?= htmlspecialchars($siswa['nisn']) ?></p>

        <?php else: ?>
            <h1 style="color:red;"><?= htmlspecialchars($pesan) ?></h1>
        <?php endif; ?>

        <p>Kembali otomatis ke halaman scan dalam 3 detik...</p>
        <a href="scan.php">Kembali sekarang</a>9786024886691
        
    </div>

</body>
</html>