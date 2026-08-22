<?php
include 'koneksi.php';

$query = "
    SELECT
        s.nisn,
        s.nama,
        s.kelas,
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
        SUM(CASE WHEN a.status = 'Izin'  THEN 1 ELSE 0 END) AS izin,
        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) AS sakit,
        SUM(CASE WHEN a.status = 'Alpa'  THEN 1 ELSE 0 END) AS alpa
    FROM siswa s
    LEFT JOIN absensi a ON s.nisn = a.nisn
    GROUP BY s.nisn, s.nama, s.kelas
    ORDER BY s.nama ASC
";

$result = $koneksi->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h1>REKAP ABSENSI</h1>

        <a href="index.php" class="btn">Kembali</a>

        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>NISN</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpa</th>
            </tr>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nisn']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td><?= $row['hadir'] ?></td>
                        <td><?= $row['izin'] ?></td>
                        <td><?= $row['sakit'] ?></td>
                        <td><?= $row['alpa'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Belum ada data siswa.</td>
                </tr>
            <?php endif; ?>
        </table>

        <p style="font-size:13px; color:gray;">
            Catatan: kolom Izin/Sakit/Alpa akan terisi jika status tersebut pernah dicatat manual di tabel absensi (saat ini sistem scan otomatis hanya mencatat status "Hadir").
        </p>
    </div>

</body>
</html>