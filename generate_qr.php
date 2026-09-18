<?php
require 'vendor/autoload.php';
include 'koneksi.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

if (!isset($_GET['nisn'])) {
    die("NISN tidak ditemukan.");
}

$nisn = $_GET['nisn'];

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


$generator = new BarcodeGeneratorPNG();
$widthFactor = 4;   
$barHeight   = 120; 
$barcodePng  = $generator->getBarcode($nisn, $generator::TYPE_CODE_128, $widthFactor, $barHeight);


$barcodeImg = imagecreatefromstring($barcodePng);
$origW = imagesx($barcodeImg);
$origH = imagesy($barcodeImg);

$margin = 40; 
$newW = $origW + ($margin * 2);
$newH = $origH + ($margin * 2) + 30; 

$canvas = imagecreatetruecolor($newW, $newH);
$white  = imagecolorallocate($canvas, 255, 255, 255);
$black  = imagecolorallocate($canvas, 20, 25, 45);
imagefill($canvas, 0, 0, $white);
imagecopy($canvas, $barcodeImg, $margin, $margin, 0, 0, $origW, $origH);

$fontSize = 4;
$textW = imagefontwidth($fontSize) * strlen($nisn);
$textX = (int)(($newW - $textW) / 2);
$textY = $margin + $origH + 8;
imagestring($canvas, $fontSize, $textX, $textY, $nisn, $black);

$path = 'qr/' . $nisn . '.png';
imagepng($canvas, $path);
imagedestroy($barcodeImg);
imagedestroy($canvas);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Barcode Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container" style="text-align:center;">
        <h1>BARCODE SISWA</h1>

        <p><strong>Nama:</strong> <?= htmlspecialchars($siswa['nama']) ?></p>
        <p><strong>NISN:</strong> <?= htmlspecialchars($siswa['nisn']) ?></p>
        <p><strong>Kelas:</strong> <?= htmlspecialchars($siswa['kelas']) ?></p>

        <img src="<?= $path ?>?t=<?= time() ?>" alt="Barcode" style="max-width:320px; width:100%; background:#fff; border:1px solid #DADFE8; border-radius:8px;">

        <br>
        <a href="<?= $path ?>" download>Download Barcode</a> |
        <a href="siswa.php">Kembali ke Data Siswa</a>
    </div>

</body>
</html>
