<?php
require 'auth.php';
include 'koneksi.php';

$tanggal = date('Y-m-d');

$nama_hari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$nama_bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tanggal_indo = $nama_hari[(int) date('w')] . ', ' . date('d') . ' ' .
                $nama_bulan[(int) date('n')] . ' ' . date('Y');

$result = $koneksi->query("SELECT COUNT(*) AS total FROM siswa");
$total_siswa = (int) $result->fetch_assoc()['total'];

$stmt_stat = $koneksi->prepare("
    SELECT
        COUNT(*) AS total_absen,
        SUM(a.status_masuk = 'Hadir')     AS hadir,
        SUM(a.status_masuk = 'Terlambat') AS terlambat,
        SUM(a.status_masuk = 'Izin')      AS izin,
        SUM(a.status_masuk = 'Sakit')     AS sakit,
        SUM(a.jam_pulang IS NOT NULL
            AND a.status_masuk IN ('Hadir', 'Terlambat')) AS sudah_pulang
    FROM absensi a
    INNER JOIN siswa s ON s.nisn = a.nisn
    WHERE a.tanggal = ?
");
$stmt_stat->bind_param("s", $tanggal);
$stmt_stat->execute();
$stat = $stmt_stat->get_result()->fetch_assoc();
$stmt_stat->close();

$total_absen  = (int)($stat['total_absen'] ?? 0);
$hadir        = (int)($stat['hadir'] ?? 0);
$terlambat    = (int)($stat['terlambat'] ?? 0);
$izin         = (int)($stat['izin'] ?? 0);
$sakit        = (int)($stat['sakit'] ?? 0);
$sudah_pulang = (int)($stat['sudah_pulang'] ?? 0);

$sudah_masuk  = $hadir + $terlambat;
$belum_absen  = max(0, $total_siswa - $total_absen);
$belum_pulang = max(0, $sudah_masuk - $sudah_pulang);

$pengaturan = null;
$cek_pengaturan = $koneksi->query("SELECT * FROM pengaturan_absensi ORDER BY id DESC LIMIT 1");
if ($cek_pengaturan && $cek_pengaturan->num_rows > 0) {
    $pengaturan = $cek_pengaturan->fetch_assoc();
}

$pulang_khusus_aktif = false;
$jam_pulang_hari_ini = null;
if ($pengaturan) {
    $jam_pulang_hari_ini = $pengaturan['jam_pulang'];
    if (
        (int)$pengaturan['aktif_pulang_khusus'] === 1 &&
        !empty($pengaturan['tanggal_pulang_khusus']) &&
        $pengaturan['tanggal_pulang_khusus'] === $tanggal &&
        !empty($pengaturan['jam_pulang_khusus'])
    ) {
        $pulang_khusus_aktif = true;
        $jam_pulang_hari_ini = $pengaturan['jam_pulang_khusus'];
    }
}
$stmt_terbaru = $koneksi->prepare("
    SELECT
        s.nisn,
        s.nama,
        s.kelas,
        a.jam_masuk,
        a.status_masuk,
        a.jam_pulang,
        a.status_pulang
    FROM absensi a
    INNER JOIN siswa s ON s.nisn = a.nisn
    WHERE a.tanggal = ?
    ORDER BY COALESCE(a.jam_masuk, '00:00:00') DESC
    LIMIT 10
");
$stmt_terbaru->bind_param("s", $tanggal);
$stmt_terbaru->execute();
$terbaru = $stmt_terbaru->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Absensi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-wrap {
            max-width: 1100px;
            margin: 0 auto;
        }

        .dashboard-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }

        .dashboard-head h1 {
            margin-bottom: 6px;
        }

        .dashboard-date {
            margin: 0;
            color: var(--text-muted);
            font-size: 14px;
        }

        .dashboard-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .dashboard-actions a {
            white-space: nowrap;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin: 20px 0;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 5px 16px rgba(22,33,62,.05);
        }

        .stat-card__label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .stat-card__number {
            display: block;
            margin-top: 7px;
            font-family: var(--font-display);
            font-size: 30px;
            font-weight: 700;
            color: var(--navy);
        }

        .stat-card__small {
            color: var(--text-muted);
            font-size: 12px;
            margin-top: 4px;
        }

        .stat-card--green .stat-card__number { color: var(--accent-dark); }
        .stat-card--warn .stat-card__number { color: var(--warn); }
        .stat-card--red .stat-card__number { color: var(--danger); }

        .dashboard-section {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-top: 16px;
            box-shadow: 0 5px 16px rgba(22,33,62,.05);
        }

        .dashboard-section h2 {
            margin: 0 0 14px;
            font-family: var(--font-display);
            font-size: 18px;
            color: var(--navy);
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .schedule-item {
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 12px;
            background: #FBFBFD;
        }

        .schedule-item span {
            display: block;
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .schedule-item strong {
            display: block;
            margin-top: 4px;
            font-family: var(--font-mono);
            color: var(--navy);
        }

        .special-note {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #FFF8E8;
            border: 1px solid #F0D69A;
            font-size: 13px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-hadir {
            background: #E7F7EF;
            color: #23805F;
        }

        .status-telat {
            background: #FFF2D9;
            color: #9A671A;
        }

        .status-izin {
            background: #E9F0FF;
            color: #315B9A;
        }

        .status-sakit {
            background: #FDEBEC;
            color: #A63A43;
        }

        .status-pulang {
            background: #E9F0FF;
            color: #315B9A;
        }

        .status-belum {
            background: #F0F1F4;
            color: #6B7280;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .quick-links a {
            text-decoration: none;
            text-align: center;
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 12px 8px;
            font-weight: 600;
            font-size: 13px;
            background: #FBFBFD;
        }

        .quick-links a:hover {
            border-color: var(--accent);
            background: #F3FBF7;
        }

        @media (max-width: 800px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .quick-links {
                grid-template-columns: repeat(2, 1fr);
            }

            .schedule-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-head {
                flex-direction: column;
            }

            .dashboard-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 18px 10px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .stat-card {
                padding: 13px;
            }

            .stat-card__number {
                font-size: 25px;
            }

            .dashboard-section {
                padding: 14px;
            }

            .quick-links {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-wrap">

    <div class="dashboard-head">
        <div>
            <h1>DASHBOARD ABSENSI</h1>
            <p class="dashboard-date">
                <?= htmlspecialchars($tanggal_indo) ?> · Admin:
                <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong>
            </p>
        </div>

        <div class="dashboard-actions">
            <a href="index.php" class="btn">Beranda</a>
            <a href="logout.php" class="btn">Keluar</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-card__label">Total Siswa</span>
            <span class="stat-card__number"><?= $total_siswa ?></span>
            <div class="stat-card__small">Data siswa terdaftar</div>
        </div>

        <div class="stat-card stat-card--green">
            <span class="stat-card__label">Sudah Masuk</span>
            <span class="stat-card__number"><?= $sudah_masuk ?></span>
            <div class="stat-card__small"><?= $hadir ?> hadir tepat waktu</div>
        </div>

        <div class="stat-card stat-card--warn">
            <span class="stat-card__label">Terlambat</span>
            <span class="stat-card__number"><?= $terlambat ?></span>
            <div class="stat-card__small">Hari ini</div>
        </div>

        <div class="stat-card">
            <span class="stat-card__label">Belum Absen</span>
            <span class="stat-card__number"><?= $belum_absen ?></span>
            <div class="stat-card__small">Belum ada catatan hari ini</div>
        </div>

        <div class="stat-card">
            <span class="stat-card__label">Izin</span>
            <span class="stat-card__number"><?= $izin ?></span>
            <div class="stat-card__small">Hari ini</div>
        </div>

        <div class="stat-card stat-card--red">
            <span class="stat-card__label">Sakit</span>
            <span class="stat-card__number"><?= $sakit ?></span>
            <div class="stat-card__small">Hari ini</div>
        </div>

        <div class="stat-card stat-card--green">
            <span class="stat-card__label">Sudah Pulang</span>
            <span class="stat-card__number"><?= $sudah_pulang ?></span>
            <div class="stat-card__small">Checkout tercatat</div>
        </div>

        <div class="stat-card stat-card--warn">
            <span class="stat-card__label">Belum Pulang</span>
            <span class="stat-card__number"><?= $belum_pulang ?></span>
            <div class="stat-card__small">Sudah masuk, belum checkout</div>
        </div>
    </div>

    <div class="dashboard-section">
        <h2>Jadwal Hari Ini</h2>

        <?php if ($pengaturan): ?>
            <div class="schedule-grid">
                <div class="schedule-item">
                    <span>Jam Masuk</span>
                    <strong><?= htmlspecialchars(substr($pengaturan['jam_masuk'], 0, 5)) ?></strong>
                </div>

                <div class="schedule-item">
                    <span>Batas Terlambat</span>
                    <strong><?= htmlspecialchars(substr($pengaturan['batas_terlambat'], 0, 5)) ?></strong>
                </div>

                <div class="schedule-item">
                    <span>Jam Pulang</span>
                    <strong><?= htmlspecialchars(substr($jam_pulang_hari_ini, 0, 5)) ?></strong>
                </div>
            </div>

            <?php if ($pulang_khusus_aktif): ?>
                <div class="special-note">
                    <strong>Jadwal pulang khusus aktif hari ini.</strong>
                    Jam pulang diatur menjadi
                    <strong><?= htmlspecialchars(substr($pengaturan['jam_pulang_khusus'], 0, 5)) ?></strong>.
                </div>
            <?php endif; ?>

        <?php else: ?>
            <p>Pengaturan jam absensi belum tersedia.</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-section">
        <h2>Menu Cepat</h2>

        <div class="quick-links">
            <a href="scan.php?mode=masuk">Scan Masuk</a>
            <a href="scan.php?mode=pulang">Scan Pulang</a>
            <a href="siswa.php">Data Siswa</a>
            <a href="pengaturan.php">Pengaturan Jam</a>
            <a href="riwayat.php">Riwayat</a>
            <a href="rekap.php">Rekap</a>
            <a href="input_izin_sakit.php">Izin / Sakit</a>
            <a href="index.php">Halaman Utama</a>
        </div>
    </div>

    <div class="dashboard-section">
        <h2>Absensi Terbaru Hari Ini</h2>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Masuk</th>
                    <th>Status</th>
                    <th>Pulang</th>
                </tr>

                <?php if ($terbaru && $terbaru->num_rows > 0): ?>
                    <?php while ($row = $terbaru->fetch_assoc()): ?>
                        <?php $hadir_fisik = in_array($row['status_masuk'], ['Hadir', 'Terlambat'], true); ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nisn']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['kelas']) ?></td>
                            <td>
                                <?= ($hadir_fisik && !empty($row['jam_masuk']))
                                    ? htmlspecialchars(substr($row['jam_masuk'], 0, 5))
                                    : '-' ?>
                            </td>
                            <td>
                                <?php if ($row['status_masuk'] === 'Hadir'): ?>
                                    <span class="status-badge status-hadir">Hadir</span>
                                <?php elseif ($row['status_masuk'] === 'Terlambat'): ?>
                                    <span class="status-badge status-telat">Terlambat</span>
                                <?php elseif ($row['status_masuk'] === 'Izin'): ?>
                                    <span class="status-badge status-izin">Izin</span>
                                <?php elseif ($row['status_masuk'] === 'Sakit'): ?>
                                    <span class="status-badge status-sakit">Sakit</span>
                                <?php else: ?>
                                    <span class="status-badge status-belum">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$hadir_fisik): ?>
                                    -
                                <?php elseif (!empty($row['jam_pulang'])): ?>
                                    <span class="status-badge status-pulang">
                                        <?= htmlspecialchars(substr($row['jam_pulang'], 0, 5)) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-belum">Belum</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Belum ada absensi hari ini.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

</div>

<?php $stmt_terbaru->close(); ?>

</body>
</html>
