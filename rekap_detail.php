<?php
require 'auth.php';
include 'koneksi.php';

$nisn = trim($_GET['nisn'] ?? '');
$bulan = $_GET['bulan'] ?? date('Y-m');
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
    $bulan = date('Y-m');
}

if ($nisn === '') {
    die("NISN tidak ditemukan. <a href='rekap.php'>Kembali</a>");
}

$stmt = $koneksi->prepare("SELECT nisn, nama, kelas FROM siswa WHERE nisn = ? LIMIT 1");
$stmt->bind_param("s", $nisn);
$stmt->execute();
$siswa = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$siswa) {
    die("Siswa tidak ditemukan. <a href='rekap.php?bulan=" . urlencode($bulan) . "'>Kembali</a>");
}

$stmt = $koneksi->prepare("
    SELECT tanggal, jam_masuk, jam_pulang, status_masuk
    FROM absensi
    WHERE nisn = ? AND DATE_FORMAT(tanggal, '%Y-%m') = ?
");
$stmt->bind_param("ss", $nisn, $bulan);
$stmt->execute();
$hasil = $stmt->get_result();
$data = [];
while ($row = $hasil->fetch_assoc()) {
    $data[$row['tanggal']] = $row;
}
$stmt->close();

$namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$namaBulanIndo = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                  '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
[$tahun, $bln] = explode('-', $bulan);
$labelBulan = $namaBulanIndo[$bln] . ' ' . $tahun;
$jumlahHari = (int) date('t', strtotime($bulan . '-01'));
$hariIni = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Rekap Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h1>DETAIL REKAP</h1>
        <p>
            <strong><?= htmlspecialchars($siswa['nama']) ?></strong>
            (<?= htmlspecialchars($siswa['nisn']) ?>) &middot; <?= htmlspecialchars($siswa['kelas']) ?><br>
            Bulan: <strong><?= htmlspecialchars($labelBulan) ?></strong>
        </p>

        <a href="rekap.php?bulan=<?= urlencode($bulan) ?>" class="btn">Kembali ke Rekap</a>

        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Keterangan</th>
            </tr>

            <?php for ($tgl = 1; $tgl <= $jumlahHari; $tgl++):
                $tanggalLengkap = $bulan . '-' . str_pad($tgl, 2, '0', STR_PAD_LEFT);
                if ($tanggalLengkap > $hariIni) {
                    break;
                }
                $w = (int) date('w', strtotime($tanggalLengkap));
                $libur = in_array($w, [0, 6]);
                $r = $data[$tanggalLengkap] ?? null;

                if ($r) {
                    $ket = $r['status_masuk'] ?? '-';
                } elseif ($libur) {
                    $ket = 'Libur';
                } else {
                    $ket = 'Tidak masuk';
                }
            ?>
                <tr<?= ($libur && !$r) ? ' style="color:gray;"' : '' ?>>
                    <td><?= date('d-m-Y', strtotime($tanggalLengkap)) ?></td>
                    <td><?= $namaHari[$w] ?></td>
                    <td><?= ($r && !empty($r['jam_masuk'])) ? htmlspecialchars(substr($r['jam_masuk'], 0, 5)) : '-' ?></td>
                    <td><?= ($r && !empty($r['jam_pulang'])) ? htmlspecialchars(substr($r['jam_pulang'], 0, 5)) : '-' ?></td>
                    <td><?= htmlspecialchars($ket) ?></td>
                </tr>
            <?php endfor; ?>
        </table>
    </div>

</body>
</html>
