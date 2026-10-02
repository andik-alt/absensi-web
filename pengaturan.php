<?php
include 'koneksi.php';

$pesan = '';

/* Ambil pengaturan terbaru */
$result = $koneksi->query(
    "SELECT * FROM pengaturan_absensi ORDER BY id DESC LIMIT 1"
);
$setting = $result->fetch_assoc();

if (!$setting) {
    $koneksi->query(
        "INSERT INTO pengaturan_absensi
        (jam_masuk, batas_terlambat, jam_pulang)
        VALUES ('07:00:00', '07:15:00', '15:30:00')"
    );

    $setting = $koneksi->query(
        "SELECT * FROM pengaturan_absensi ORDER BY id DESC LIMIT 1"
    )->fetch_assoc();
}

/* Simpan pengaturan */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jamMasuk = $_POST['jam_masuk'] ?? '07:00';
    $batasTerlambat = $_POST['batas_terlambat'] ?? '07:15';
    $jamPulang = $_POST['jam_pulang'] ?? '15:30';

    $aktifKhusus = isset($_POST['aktif_pulang_khusus']) ? 1 : 0;
    $tanggalKhusus = !empty($_POST['tanggal_pulang_khusus'])
        ? $_POST['tanggal_pulang_khusus']
        : null;
    $jamPulangKhusus = !empty($_POST['jam_pulang_khusus'])
        ? $_POST['jam_pulang_khusus']
        : null;

    if ($aktifKhusus && (!$tanggalKhusus || !$jamPulangKhusus)) {
        $pesan = "Jika jam pulang khusus diaktifkan, tanggal dan jam khusus wajib diisi.";
    } else {
        $stmt = $koneksi->prepare(
            "UPDATE pengaturan_absensi
             SET jam_masuk = ?,
                 batas_terlambat = ?,
                 jam_pulang = ?,
                 jam_pulang_khusus = ?,
                 tanggal_pulang_khusus = ?,
                 aktif_pulang_khusus = ?
             WHERE id = ?"
        );

        $id = (int)$setting['id'];

        $stmt->bind_param(
            "sssssii",
            $jamMasuk,
            $batasTerlambat,
            $jamPulang,
            $jamPulangKhusus,
            $tanggalKhusus,
            $aktifKhusus,
            $id
        );

        if ($stmt->execute()) {
            $pesan = "Pengaturan berhasil disimpan.";
        } else {
            $pesan = "Gagal menyimpan pengaturan.";
        }

        $stmt->close();

        $setting = $koneksi->query(
            "SELECT * FROM pengaturan_absensi ORDER BY id DESC LIMIT 1"
        )->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan Absensi</title>
<link rel="stylesheet" href="style.css">
<style>
.settings-box {
    max-width: 650px;
    margin: 30px auto;
    padding: 25px;
    border-radius: 12px;
    background: #fff;
}
.settings-box label {
    display: block;
    margin-top: 15px;
    margin-bottom: 6px;
    font-weight: bold;
}
.settings-box input[type="time"],
.settings-box input[type="date"] {
    width: 100%;
    max-width: 300px;
    padding: 10px;
    box-sizing: border-box;
}
.special {
    margin-top: 25px;
    padding: 18px;
    border: 1px solid #ddd;
    border-radius: 10px;
}
.notice {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    background: #eef6ff;
}
button {
    margin-top: 20px;
    padding: 11px 18px;
    cursor: pointer;
}
</style>
</head>
<body>

<div class="container">
    <div class="settings-box">
        <h1>Pengaturan Absensi</h1>

        <?php if ($pesan): ?>
            <div class="notice">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Jam Masuk</label>
            <input type="time"
                   name="jam_masuk"
                   value="<?= htmlspecialchars(substr($setting['jam_masuk'], 0, 5)) ?>"
                   required>

            <label>Batas Terlambat</label>
            <input type="time"
                   name="batas_terlambat"
                   value="<?= htmlspecialchars(substr($setting['batas_terlambat'], 0, 5)) ?>"
                   required>

            <label>Jam Pulang Normal</label>
            <input type="time"
                   name="jam_pulang"
                   value="<?= htmlspecialchars(substr($setting['jam_pulang'], 0, 5)) ?>"
                   required>

            <div class="special">
                <h3>Jam Pulang Khusus</h3>
                <p>
                    Gunakan ketika sekolah pulang lebih awal,
                    misalnya karena rapat guru.
                </p>

                <label>
                    <input type="checkbox"
                           name="aktif_pulang_khusus"
                           value="1"
                           <?= $setting['aktif_pulang_khusus'] ? 'checked' : '' ?>>
                    Aktifkan jam pulang khusus
                </label>

                <label>Tanggal</label>
                <input type="date"
                       name="tanggal_pulang_khusus"
                       value="<?= htmlspecialchars($setting['tanggal_pulang_khusus'] ?? '') ?>">

                <label>Jam Pulang Khusus</label>
                <input type="time"
                       name="jam_pulang_khusus"
                       value="<?= htmlspecialchars(!empty($setting['jam_pulang_khusus']) ? substr($setting['jam_pulang_khusus'], 0, 5) : '') ?>">
            </div>

            <button type="submit">Simpan Pengaturan</button>
        </form>

        <p style="margin-top:20px;">
            <a href="index.php">← Kembali ke Dashboard</a>
        </p>
    </div>
</div>

</body>
</html>
