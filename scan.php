<?php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Absensi Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container" style="text-align:center;">
        <h1>ABSENSI SISWA</h1>
        <p>SILAKAN SCAN QR CODE</p>

        <form method="POST" action="proses_absen.php" id="formScan">
            <input type="text" name="nisn" id="nisn" autofocus
                   autocomplete="off"
                   style="font-size:24px; text-align:center; padding:10px; width:300px;">
        </form>

        <p id="status">Menunggu scan...</p>

        <br>
        <a href="index.php">Kembali</a>
    </div>

    <script>
        const input = document.getElementById('nisn');
        input.focus();

        input.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                document.getElementById('status').innerText = "Memproses...";
                document.getElementById('formScan').submit();
            }
        });

        document.addEventListener('click', function () {
            input.focus();
        });
    </script>

</body>
</html>