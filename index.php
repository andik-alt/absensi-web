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
        <p class="gate__eyebrow">Gerbang Absensi Digital</p>
        <h1 class="gate__title">SISTEM ABSENSI</h1>

    </div>

    <div class="container container--home">
        <p class="subtitle">Pilih menu untuk mulai.</p>

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
                    <span class="menu-card__label">Data Siswa</span>
                    <span class="menu-card__desc">Kelola daftar siswa</span>
                </span>
            </a>

            <a href="riwayat.php" class="menu-card">
                <span class="menu-card__icon">&#9201;</span>
                <span class="menu-card__text">
                    <span class="menu-card__label">Riwayat</span>
                    <span class="menu-card__desc">Absensi harian</span>
                </span>
            </a>

            <a href="rekap.php" class="menu-card">
                <span class="menu-card__icon">&#9636;</span>
                <span class="menu-card__text">
                    <span class="menu-card__label">Rekap</span>
                    <span class="menu-card__desc">Total per siswa</span>
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