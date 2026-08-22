<?php
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

$stmt = $koneksi->prepare("
    SELECT a.tanggal, a.jam, a.status, s.nisn, s.nama, s.kelas
    FROM absensi a
    JOIN siswa s ON a.nisn = s.nisn
    WHERE a.tanggal = ?
    ORDER BY a.jam ASC
");
$stmt->bind_param("s", $tanggal);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h1>RIWAYAT ABSENSI</h1>

        <form method="GET" action="riwayat.php">
            <label>Pilih Tanggal:</label>
            <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
            <button type="submit">Tampilkan</button>
        </form>

        <br>
        <a href="index.php" class="btn">Kembali</a>

        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>Tanggal</th>
                <th>NISN</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Jam</th>
                <th>Status</th>
            </tr>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($row['nisn']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td><?= htmlspecialchars($row['jam']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Belum ada data absensi untuk tanggal ini.</td>
                </tr>
            <?php endif; ?>
        </table>

        <?php $stmt->close(); ?>
    </div>

</body>
</html>