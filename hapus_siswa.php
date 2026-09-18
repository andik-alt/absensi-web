<?php
require 'auth.php';
include 'koneksi.php';

if (!isset($_GET['id'])) {
    die("ID siswa tidak ditemukan.");
}

$id = $_GET['id'];

$stmt = $koneksi->prepare("DELETE FROM siswa WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: siswa.php");
    exit;
} else {
    die("Gagal menghapus data: " . $koneksi->error);
}

$stmt->close();
?>
