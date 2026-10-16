<?php
require 'auth.php';
require 'koneksi.php';

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$dt = DateTime::createFromFormat('!Y-m-d', $tanggal);

if (!$dt || $dt->format('Y-m-d') !== $tanggal) {
    $tanggal = date('Y-m-d');
}

$stmt = $koneksi->prepare("
    SELECT
        a.tanggal,
        a.jam_masuk,
        a.jam_pulang,
        a.status_masuk,
        a.status_pulang,
        s.nisn,
        s.nama,
        s.kelas
    FROM absensi AS a
    JOIN siswa AS s ON a.nisn = s.nisn
    WHERE a.tanggal = ?
    ORDER BY a.jam_masuk ASC, s.nama ASC
");

if (!$stmt) {
    die("Gagal menyiapkan query riwayat absensi.");
}

$stmt->bind_param("s", $tanggal);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>RIWAYAT ABSENSI</h1>

        <form method="GET" action="riwayat.php">
            <label for="tanggal">Pilih Tanggal:</label>
            <input id="tanggal" type="date" name="tanggal"
                   value="<?= htmlspecialchars($tanggal, ENT_QUOTES, 'UTF-8') ?>" required>
            <button type="submit">Tampilkan</button>
        </form>

        <br>
        <a href="index.php" class="btn">Kembali</a>

        <div style="overflow-x:auto; margin-top:20px;">
            <table border="1" cellpadding="8" cellspacing="0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>NISN</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Jam Masuk</th>
                        <th>Status Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status Pulang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d-m-Y', strtotime($row['tanggal'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['nisn'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['kelas'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= !empty($row['jam_masuk']) ? htmlspecialchars(substr($row['jam_masuk'], 0, 5), ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                <td><?= htmlspecialchars($row['status_masuk'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= !empty($row['jam_pulang']) ? htmlspecialchars(substr($row['jam_pulang'], 0, 5), ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                <td><?= htmlspecialchars($row['status_pulang'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Belum ada data absensi untuk tanggal ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php
$stmt->close();
?>
