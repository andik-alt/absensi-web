<?php
date_default_timezone_set('Asia/Jakarta');
 $host = "localhost";
 $user = "root";
 $pass = "";
 $db = "absensi_sekolah";

 $koneksi = new mysqli ($host, $user, $pass, $db);
 if ($koneksi->connect_error){
    die("koneksi gagal: " . $koneksi->connect_error);

 }
 $koneksi ->set_charset("utf8");
?>