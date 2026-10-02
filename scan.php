<?php
$mode = ($_GET['mode'] ?? 'masuk') === 'pulang' ? 'pulang' : 'masuk';

$judul = $mode === 'pulang' ? 'ABSEN PULANG' : 'ABSEN MASUK';
$action = $mode === 'pulang' ? 'proses_pulang.php' : 'proses_absen.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($judul) ?></title>
<link rel="stylesheet" href="style.css">
<style>
.scan-actions { display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin:20px 0; }
.scan-actions a { text-decoration:none; padding:10px 16px; border-radius:8px; background:#eee; color:#222; }
.scan-actions a.active { background:#2563eb; color:#fff; }
#reader { max-width:420px; margin:20px auto; }
#cameraBox { display:none; }
</style>
</head>
<body>
<div class="container" style="text-align:center;">
    <h1><?= htmlspecialchars($judul) ?></h1>

    <div class="scan-actions">
        <a class="<?= $mode === 'masuk' ? 'active' : '' ?>" href="scan.php?mode=masuk">Absen Masuk</a>
        <a class="<?= $mode === 'pulang' ? 'active' : '' ?>" href="scan.php?mode=pulang">Absen Pulang</a>
    </div>

    <form method="POST" action="<?= htmlspecialchars($action) ?>" id="formScan">
        <input type="text" name="nisn" id="nisn" autofocus
               autocomplete="off"
               inputmode="numeric"
               placeholder="Scan QR / masukkan NISN"
               style="font-size:24px; text-align:center; padding:10px; width:min(300px,80%);">
    </form>

    <button type="button" id="btnCamera" style="margin-top:15px;padding:10px 16px;">
        Scan dengan Kamera HP
    </button>

    <div id="cameraBox">
        <div id="reader"></div>
        <button type="button" id="btnStop">Tutup Kamera</button>
    </div>

    <p id="status">Menunggu scan...</p>
    <a href="index.php">Kembali</a>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
const input = document.getElementById('nisn');
const form = document.getElementById('formScan');
const statusEl = document.getElementById('status');
const cameraBox = document.getElementById('cameraBox');
const btnCamera = document.getElementById('btnCamera');
const btnStop = document.getElementById('btnStop');

let scanner = null;
let sudahScan = false;

input.focus();

input.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && input.value.trim() !== '') {
        e.preventDefault();
        statusEl.innerText = 'Memproses...';
        form.submit();
    }
});

document.addEventListener('click', function(e) {
    if (!cameraBox.contains(e.target) && e.target !== btnCamera) {
        input.focus();
    }
});

btnCamera.addEventListener('click', async function() {
    cameraBox.style.display = 'block';
    statusEl.innerText = 'Meminta akses kamera...';

    scanner = new Html5Qrcode("reader");

    try {
        await scanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText) => {
                if (sudahScan) return;
                sudahScan = true;

                input.value = decodedText.trim();
                statusEl.innerText = 'QR terbaca. Memproses...';

                scanner.stop().then(() => {
                    form.submit();
                });
            },
            () => {}
        );
        statusEl.innerText = 'Arahkan kamera ke QR siswa.';
    } catch (err) {
        statusEl.innerText = 'Kamera tidak dapat digunakan: ' + err;
        cameraBox.style.display = 'none';
    }
});

btnStop.addEventListener('click', async function() {
    if (scanner) {
        try { await scanner.stop(); } catch (e) {}
        scanner = null;
    }
    cameraBox.style.display = 'none';
    statusEl.innerText = 'Menunggu scan...';
    input.focus();
});
</script>
</body>
</html>
