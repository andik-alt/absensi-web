<?php
require 'auth.php';
include 'koneksi.php';

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$cekTanggal = DateTime::createFromFormat('Y-m-d', $tanggal);
if (!$cekTanggal || $cekTanggal->format('Y-m-d') !== $tanggal) {
    $tanggal = date('Y-m-d');
}

$stmt = $koneksi->prepare("
    SELECT a.tanggal, a.jam_masuk, a.jam_pulang, a.status_masuk,
           s.nisn, s.nama, s.kelas
    FROM absensi a
    JOIN siswa s ON a.nisn = s.nisn
    WHERE a.tanggal = ?
    ORDER BY COALESCE(a.jam_masuk, '00:00:00') ASC, s.nama ASC
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
        <a href="dashboard.php" class="btn">Kembali</a>

        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>Tanggal</th>
                <th>NISN</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Status</th>
            </tr>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($row['nisn']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td><?= !empty($row['jam_masuk']) ? htmlspecialchars($row['jam_masuk']) : '-' ?></td>
                        <td><?= !empty($row['jam_pulang']) ? htmlspecialchars($row['jam_pulang']) : '-' ?></td>
                        <td><?= htmlspecialchars($row['status_masuk'] ?? '-') ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Belum ada data absensi untuk tanggal ini.</td>
                </tr>
            <?php endif; ?>
        </table>

        <?php $stmt->close(); ?>
    </div>

</body>
</html>
