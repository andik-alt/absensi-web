<?php
require 'auth.php';
include 'koneksi.php';

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn    = trim($_POST['nisn']);
    $nama    = trim($_POST['nama']);
    $kelas   = trim($_POST['kelas']);
    $jurusan = trim($_POST['jurusan']);
    $no_ortu = trim($_POST['no_ortu'] ?? '');

    if ($nisn === "" || $nama === "" || $kelas === "" || $jurusan === "") {
        $pesan = "Semua kolom wajib diisi!";
    } else {
        $cek = $koneksi->prepare("SELECT id FROM siswa WHERE nisn = ?");
        $cek->bind_param("s", $nisn);
        $cek->execute();
        $cekResult = $cek->get_result();

        if ($cekResult->num_rows > 0) {
            $pesan = "NISN sudah terdaftar!";
        } else {
            $stmt = $koneksi->prepare(
                "INSERT INTO siswa (nisn, nama, kelas, jurusan, no_ortu)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssss",
                $nisn,
                $nama,
                $kelas,
                $jurusan,
                $no_ortu
            );

            if ($stmt->execute()) {
                header("Location: siswa.php");
                exit;
            } else {
                $pesan = "Gagal menyimpan data: " . $koneksi->error;
            }

            $stmt->close();
        }

        $cek->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h1>TAMBAH SISWA</h1>

        <?php if ($pesan !== ""): ?>
            <p style="color:red;"><?= htmlspecialchars($pesan) ?></p>
        <?php endif; ?>

        <form method="POST" action="tambah_siswa.php">

            <label>NISN</label><br>
            <input type="text" name="nisn" required><br><br>

            <label>Nama</label><br>
            <input type="text" name="nama" required><br><br>

            <label>Kelas</label><br>
            <input type="text" name="kelas" required><br><br>

            <label>Jurusan</label><br>
            <input type="text" name="jurusan" required><br><br>

            <label>Nomor WhatsApp Orang Tua</label><br>
            <input
                type="text"
                name="no_ortu"
                placeholder="Contoh: 081234567890"
            ><br><br>

            <button type="submit">SIMPAN</button>
        </form>

        <br>
        <a href="siswa.php">Kembali</a>
    </div>

</body>
</html>
