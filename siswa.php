<?php
include 'koneksi.php';

$query = "SELECT * FROM siswa ORDER BY nama ASC";
$result = $koneksi->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h1>DATA SISWA</h1>

        <a href="tambah_siswa.php" class="btn">+ Tambah Siswa</a> <br>
        <a href="index.php" class="btn">Kembali</a>

        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>NISN</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Jurusan</th>
                <th>QR Code</th>
                <th>Aksi</th>
            </tr>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nisn']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td><?= htmlspecialchars($row['jurusan']) ?></td>
                        <td>
                            <a href="generate_qr.php?nisn=<?= urlencode($row['nisn']) ?>">Lihat QR</a>
                        </td>
                        <td>
                            <a href="edit_siswa.php?id=<?= $row['id'] ?>">Edit</a> |
                            <a href="hapus_siswa.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus siswa ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Belum ada data siswa.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

</body>
</html>