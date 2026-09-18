<?php
session_start();
$isLoggedIn = isset($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Sekolah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="home-body">

    <div class="gate">
        <div class="gate__pattern"></div>
        <div class="gate__scanline"></div>

        <p class="gate__eyebrow">Gerbang Absensi Digital</p>
        <h1 class="gate__title">SISTEM ABSENSI</h1>

        <div class="gate__clock" id="liveClock">00:00:00</div>
        <p class="gate__date" id="liveDate">Memuat tanggal&hellip;</p>
    </div>

    <div class="container container--home">
        <?php if ($isLoggedIn): ?>
            <p class="subtitle">
                Masuk sebagai <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong>
                &middot; <a href="logout.php">Keluar</a>
            </p>
        <?php else: ?>
            <p class="subtitle">Pilih menu untuk mulai. <a href="login.php">Login admin</a> untuk kelola data.</p>
        <?php endif; ?>

        <div class="menu-grid">
            <a href="scan.php" class="menu-card menu-card--accent">
                <span class="menu-card__icon">&#9635;</span>
                <span class="menu-card__text">
                    <span class="menu-card__label">Scan Absensi</span>
                    <span class="menu-card__desc">Absen pakai scanner QR</span>
                </span>
            </a>

            <a href="siswa.php" class="menu-card">
                <span class="menu-card__icon">&#9776;</span>
                <span class="menu-card__text">
                    <span class="menu-card__label">Data Siswa <?= !$isLoggedIn ? '&#128274;' : '' ?></span>
                    <span class="menu-card__desc">Kelola daftar siswa</span>
                </span>
            </a>

            <a href="riwayat.php" class="menu-card">
                <span class="menu-card__icon">&#9201;</span>
                <span class="menu-card__text">
                    <span class="menu-card__label">Riwayat <?= !$isLoggedIn ? '&#128274;' : '' ?></span>
                    <span class="menu-card__desc">Absensi harian</span>
                </span>
            </a>

            <a href="rekap.php" class="menu-card">
                <span class="menu-card__icon">&#9636;</span>
                <span class="menu-card__text">
                    <span class="menu-card__label">Rekap <?= !$isLoggedIn ? '&#128274;' : '' ?></span>
                    <span class="menu-card__desc">Total per siswa</span>
                </span>
            </a>

            <a href="input_izin_sakit.php" class="menu-card">
                <span class="menu-card__icon">&#9993;</span>
                <span class="menu-card__text">
                    <span class="menu-card__label">Izin/Sakit <?= !$isLoggedIn ? '&#128274;' : '' ?></span>
                    <span class="menu-card__desc">Catat izin/sakit manual</span>
                </span>
            </a>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            document.getElementById('liveClock').textContent =
                `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;

            const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][now.getDay()];
            const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][now.getMonth()];
            document.getElementById('liveDate').textContent =
                `${hari}, ${now.getDate()} ${bulan} ${now.getFullYear()}`;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>

</body>
</html>
