<?php
require 'auth.php';

require 'vendor/autoload.php';

include 'koneksi.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

if (!isset($_GET['nisn'])) {
    die("NISN tidak ditemukan.");
}

$nisn = trim($_GET['nisn']);

$stmt = $koneksi->prepare("SELECT * FROM siswa WHERE nisn = ?");
$stmt->bind_param("s", $nisn);
$stmt->execute();

$result = $stmt->get_result();
$siswa = $result->fetch_assoc();

$stmt->close();

if (!$siswa) {
    die("Siswa dengan NISN tersebut tidak ditemukan.");
}

if (!is_dir('qr')) {
    mkdir('qr', 0777, true);
}

/*
 * Buat QR Code
 * Isi QR = NISN siswa
 */
$qrCode = QrCode::create($nisn)
    ->setSize(400)
    ->setMargin(20)
    ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh());

$writer = new PngWriter();

$resultQr = $writer->write($qrCode);

$path = 'qr/' . $nisn . '.png';

$resultQr->saveToFile($path);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>QR Code Siswa</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container" style="text-align:center;">

    <h1>QR CODE SISWA</h1>

    <p>
        <strong>Nama:</strong>
        <?= htmlspecialchars($siswa['nama']) ?>
    </p>

    <p>
        <strong>NISN:</strong>
        <?= htmlspecialchars($siswa['nisn']) ?>
    </p>

    <p>
        <strong>Kelas:</strong>
        <?= htmlspecialchars($siswa['kelas']) ?>
    </p>

    <img
        src="<?= htmlspecialchars($path) ?>?t=<?= time() ?>"
        alt="QR Code"
        style="
            max-width:320px;
            width:100%;
            background:#fff;
            border:1px solid #DADFE8;
            border-radius:8px;
            padding:10px;
        "
    >

    <br><br>

    <a href="<?= htmlspecialchars($path) ?>" download>
        Download QR Code
    </a>

    |

    <a href="siswa.php">
        Kembali ke Data Siswa
    </a>

</div>

</body>
</html>
