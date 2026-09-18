<?php
require 'auth.php';
include 'koneksi.php';

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$jumlahHari = (int) date('t', strtotime($bulan . '-01'));
$hariIni = date('Y-m-d');

$daftarSiswa = [];
$res = $koneksi->query("SELECT id, nisn, nama, kelas FROM siswa ORDER BY nama ASC");
while ($row = $res->fetch_assoc()) {
    $daftarSiswa[] = $row;
}

$stmt = $koneksi->prepare("SELECT nisn, tanggal, status FROM absensi WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?");
$stmt->bind_param("s", $bulan);
$stmt->execute();
$hasil = $stmt->get_result();
$absensiPerSiswa = [];
while ($row = $hasil->fetch_assoc()) {
    $absensiPerSiswa[$row['nisn']][$row['tanggal']] = $row['status'];
}

$rekapAkhir = [];
foreach ($daftarSiswa as $siswa) {
    $nisn = $siswa['nisn'];
    $masuk = 0;       
    $tidakMasuk = 0;  
    $izin = 0;
    $sakit = 0;
    $terlambat = 0;

    for ($tgl = 1; $tgl <= $jumlahHari; $tgl++) {
        $tanggalLengkap = $bulan . '-' . str_pad($tgl, 2, '0', STR_PAD_LEFT);
        $timestamp = strtotime($tanggalLengkap);
        $isWeekend = in_array(date('w', $timestamp), [0, 6]); // Minggu & Sabtu = libur
        $isMasaDepan = $tanggalLengkap > $hariIni;

        if ($isWeekend || $isMasaDepan) {
            continue;
        }

        $status = $absensiPerSiswa[$nisn][$tanggalLengkap] ?? null;

        if ($status === 'Hadir') {
            $masuk++;
        } elseif ($status === 'Terlambat') {
            $masuk++;
            $terlambat++;
        } elseif ($status === 'Izin') {
            $izin++;
        } elseif ($status === 'Sakit') {
            $sakit++;
        } else {
            $tidakMasuk++;
        }
    }

    $rekapAkhir[] = array_merge($siswa, [
        'masuk' => $masuk,
        'tidak_masuk' => $tidakMasuk,
        'terlambat' => $terlambat,
        'izin' => $izin,
        'sakit' => $sakit,
    ]);
}

$namaBulanIndo = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                   '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
$bagianBulan = explode('-', $bulan);
$labelBulan = $namaBulanIndo[$bagianBulan[1]] . ' ' . $bagianBulan[0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi Bulanan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h1>REKAP ABSENSI BULANAN</h1>
        <p>Bulan: <strong><?= $labelBulan ?></strong></p>

        <form method="GET" action="rekap.php">
            <label>Pilih Bulan:</label><br>
            <input type="month" name="bulan" value="<?= htmlspecialchars($bulan) ?>">
            <button type="submit">Tampilkan</button>
        </form>

        <br>
        <a href="index.php" class="btn">Kembali</a>

        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>NISN</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Masuk</th>
                <th>Terlambat</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Tidak Masuk</th>
                <th>Detail</th>
            </tr>

            <?php if (count($rekapAkhir) > 0): ?>
                <?php foreach ($rekapAkhir as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nisn']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td style="color:green; font-weight:bold; font-size:16px;">
                            <?= $row['masuk'] ?>
                        </td>
                        <td style="<?= $row['terlambat'] > 0 ? 'color:orange; font-weight:bold;' : 'color:gray;' ?>">
                            <?= $row['terlambat'] ?>
                        </td>
                        <td style="<?= $row['izin'] > 0 ? 'color:#2563eb; font-weight:bold;' : 'color:gray;' ?>">
                            <?= $row['izin'] ?>
                        </td>
                        <td style="<?= $row['sakit'] > 0 ? 'color:#2563eb; font-weight:bold;' : 'color:gray;' ?>">
                            <?= $row['sakit'] ?>
                        </td>
                        <td style="<?= $row['tidak_masuk'] > 0 ? 'color:red; font-weight:bold; font-size:16px;' : 'color:gray;' ?>">
                            <?= $row['tidak_masuk'] ?>
                        </td>
                        <td>
                            <a href="rekap_detail.php?nisn=<?= urlencode($row['nisn']) ?>&bulan=<?= urlencode($bulan) ?>">Lihat Tanggal</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">Belum ada data siswa.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

</body>
</html>
