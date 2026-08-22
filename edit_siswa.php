<?php
include 'koneksi.php';

$pesan = "";

if (!isset($_GET['id']) && !isset($_POST['id'])) {
    die("ID siswa tidak ditemukan.");
}

$id = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST['id'] : $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama']);
    $kelas   = trim($_POST['kelas']);
    $jurusan = trim($_POST['jurusan']);

    if ($nama === "" || $kelas === "" || $jurusan === "") {
        $pesan = "Semua kolom wajib diisi!";
    } else {
        $stmt = $koneksi->prepare("UPDATE siswa SET nama = ?, kelas = ?, jurusan = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nama, $kelas, $jurusan, $id);

        if ($stmt->execute()) {
            header("Location: siswa.php");
            exit;
        } else {
            $pesan = "Gagal menyimpan perubahan: " . $koneksi->error;
        }
        $stmt->close();
    }
}

$stmt = $koneksi->prepare("SELECT * FROM siswa WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$siswa = $result->fetch_assoc();
$stmt->close();

if (!$siswa) {
    die("Data siswa tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Siswa</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="container">
        <h1>EDIT SISWA</h1>

        <?php if ($pesan !== ""): ?>
            <p style="color:red;"><?= htmlspecialchars($pesan) ?></p>
        <?php endif; ?>

        <form method="POST" action="edit_siswa.php">
            <input type="hidden" name="id" value="<?= $siswa['id'] ?>">

            <label>NISN</label><br>
            <input type="text" value="<?= htmlspecialchars($siswa['nisn']) ?>" disabled><br><br>

            <label>Nama</label><br>
            <input type="text" name="nama" value="<?= htmlspecialchars($siswa['nama']) ?>" required><br><br>

            <label>Kelas</label><br>
            <input type="text" name="kelas" value="<?= htmlspecialchars($siswa['kelas']) ?>" required><br><br>

            <label>Jurusan</label><br>
            <input type="text" name="jurusan" value="<?= htmlspecialchars($siswa['jurusan']) ?>" required><br><br>

            <button type="submit">SIMPAN PERUBAHAN</button>
        </form>

        <br>
        <a href="siswa.php">Kembali</a>
    </div>

</body>
</html>