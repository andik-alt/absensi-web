<?php
require 'auth.php';
include 'koneksi.php';

$tanggal_hari_ini = date('Y-m-d');
$pesan = '';
$pesan_error = false;

/* Token sederhana agar form tidak bisa dikirim dari situs lain */
if (empty($_SESSION['csrf_izin'])) {
    $_SESSION['csrf_izin'] = bin2hex(random_bytes(16));
}

/* Kolom lama `jam` dan `status` masih wajib diisi selama belum dihapus dari tabel */
$cek_kolom = $koneksi->query("SHOW COLUMNS FROM absensi LIKE 'jam'");
$punya_kolom_lama = $cek_kolom && $cek_kolom->num_rows > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn    = trim($_POST['nisn'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');
    $status  = trim($_POST['status'] ?? '');
    $token   = $_POST['csrf'] ?? '';

    $tgl_valid = DateTime::createFromFormat('Y-m-d', $tanggal);
    $tgl_valid = $tgl_valid && $tgl_valid->format('Y-m-d') === $tanggal;

    if (!hash_equals($_SESSION['csrf_izin'], $token)) {
        $pesan = 'Sesi form tidak valid. Muat ulang halaman lalu coba lagi.';
        $pesan_error = true;
    } elseif ($nisn === '' || !$tgl_valid || !in_array($status, ['Izin', 'Sakit'], true)) {
        $pesan = 'Data tidak lengkap atau tidak valid.';
        $pesan_error = true;
    } else {
        /* Pastikan siswa ada */
        $stmt = $koneksi->prepare("SELECT nama FROM siswa WHERE nisn = ? LIMIT 1");
        $stmt->bind_param("s", $nisn);
        $stmt->execute();
        $siswa = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$siswa) {
            $pesan = 'Siswa dengan NISN tersebut tidak ditemukan.';
            $pesan_error = true;
        } else {
            /* Cek apakah sudah ada catatan di tanggal itu (unique key nisn + tanggal) */
            $stmt = $koneksi->prepare("SELECT id, status_masuk FROM absensi WHERE nisn = ? AND tanggal = ? LIMIT 1");
            $stmt->bind_param("ss", $nisn, $tanggal);
            $stmt->execute();
            $ada = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($ada && !in_array($ada['status_masuk'], ['Izin', 'Sakit'], true)) {
                $pesan = $siswa['nama'] . ' sudah tercatat ' . $ada['status_masuk'] .
                         ' pada tanggal itu, jadi tidak bisa diubah menjadi ' . $status . '.';
                $pesan_error = true;
            } elseif ($ada) {
                /* Sudah izin/sakit: perbarui statusnya */
                $id = (int) $ada['id'];
                if ($punya_kolom_lama) {
                    $stmt = $koneksi->prepare("UPDATE absensi SET status_masuk = ?, status = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $status, $status, $id);
                } else {
                    $stmt = $koneksi->prepare("UPDATE absensi SET status_masuk = ? WHERE id = ?");
                    $stmt->bind_param("si", $status, $id);
                }
                $stmt->execute();
                $stmt->close();
                $pesan = 'Status ' . $siswa['nama'] . ' diperbarui menjadi ' . $status . '.';
            } else {
                /* Catatan baru. Izin/sakit tidak punya jam masuk, jadi jam_masuk dibiarkan kosong */
                if ($punya_kolom_lama) {
                    $jam_input = date('H:i:s');
                    $stmt = $koneksi->prepare(
                        "INSERT INTO absensi (nisn, tanggal, jam, status, status_masuk) VALUES (?, ?, ?, ?, ?)"
                    );
                    $stmt->bind_param("sssss", $nisn, $tanggal, $jam_input, $status, $status);
                } else {
                    $stmt = $koneksi->prepare(
                        "INSERT INTO absensi (nisn, tanggal, status_masuk) VALUES (?, ?, ?)"
                    );
                    $stmt->bind_param("sss", $nisn, $tanggal, $status);
                }
                $stmt->execute();
                $stmt->close();
                $pesan = $siswa['nama'] . ' dicatat ' . $status . '.';
            }
        }
    }
}

/* Daftar siswa untuk dropdown */
$daftar_siswa = $koneksi->query("SELECT nisn, nama, kelas, jurusan FROM siswa ORDER BY kelas, nama");

/* 15 catatan izin/sakit terbaru */
$riwayat = $koneksi->query("
    SELECT a.tanggal, a.status_masuk, s.nisn, s.nama, s.kelas
    FROM absensi a
    INNER JOIN siswa s ON s.nisn = a.nisn
    WHERE a.status_masuk IN ('Izin', 'Sakit')
    ORDER BY a.tanggal DESC, a.id DESC
    LIMIT 15
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Izin / Sakit</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .izin-wrap {
            max-width: 720px;
            margin: 0 auto;
        }

        .izin-card {
            background: var(--card, #fff);
            border: 1px solid var(--border, #e3e5ec);
            border-radius: 12px;
            padding: 20px;
            margin-top: 16px;
            box-shadow: 0 5px 16px rgba(22,33,62,.05);
        }

        .izin-card label {
            display: block;
            margin: 12px 0 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .izin-card select,
        .izin-card input[type="date"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border, #e3e5ec);
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
        }

        .izin-card button {
            margin-top: 18px;
            padding: 11px 18px;
            border: 0;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            background: var(--accent, #2fbf8f);
            color: #fff;
        }

        .notice {
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 16px;
        }

        .notice--ok {
            background: #E7F7EF;
            border: 1px solid #BFE6D3;
            color: #23805F;
        }

        .notice--error {
            background: #FDEBEC;
            border: 1px solid #F3C4C8;
            color: #A63A43;
        }

        .table-wrap { overflow-x: auto; }
    </style>
</head>
<body>

<div class="izin-wrap">

    <h1>INPUT IZIN / SAKIT</h1>
    <p><a href="dashboard.php" class="btn">Kembali ke Dashboard</a></p>

    <?php if ($pesan !== ''): ?>
        <div class="notice <?= $pesan_error ? 'notice--error' : 'notice--ok' ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="izin-card">
        <form method="post">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_izin']) ?>">

            <label for="nisn">Siswa</label>
            <select id="nisn" name="nisn" required>
                <option value="">-- Pilih siswa --</option>
                <?php while ($s = $daftar_siswa->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($s['nisn']) ?>">
                        <?= htmlspecialchars($s['nama'] . ' — ' . $s['kelas'] . ' ' . $s['jurusan']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal_hari_ini) ?>" required>

            <label for="status">Keterangan</label>
            <select id="status" name="status" required>
                <option value="Izin">Izin</option>
                <option value="Sakit">Sakit</option>
            </select>

            <button type="submit">Simpan</button>
        </form>
    </div>

    <div class="izin-card">
        <h2 style="margin-top:0;">Izin / Sakit Terbaru</h2>
        <div class="table-wrap">
            <table>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Keterangan</th>
                </tr>
                <?php if ($riwayat && $riwayat->num_rows > 0): ?>
                    <?php while ($r = $riwayat->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('d-m-Y', strtotime($r['tanggal']))) ?></td>
                            <td><?= htmlspecialchars($r['nama']) ?></td>
                            <td><?= htmlspecialchars($r['kelas']) ?></td>
                            <td><?= htmlspecialchars($r['status_masuk']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">Belum ada catatan izin atau sakit.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

</div>

</body>
</html>
