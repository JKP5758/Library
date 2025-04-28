<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_POST['id_buku']) || !isset($_POST['pesan'])) {
    echo "<script>alert('Data tidak lengkap atau belum login.'); window.history.back();</script>";
    exit;
}

$idBuku = $_POST['id_buku'];
$username = $_SESSION['username'];
$pesan = trim($_POST['pesan']);
$uploadDir = 'data/img/';
$chatFile = 'data/chat.txt';
$gambar = '';

if (!empty($_FILES['gambar']['name'])) {
    $fileName = uniqid() . '_' . basename($_FILES['gambar']['name']);
    $targetPath = $uploadDir . $fileName;

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
        $gambar = $fileName;
    }
}

// Jika tidak ada gambar, beri nilai placeholder (bisa juga kosong)
if ($gambar === '') {
    $gambar = 'default.png'; // atau kosongkan jika tidak ingin gambar sama sekali
}

$baris = "$idBuku|$username|$gambar|$pesan\n";
file_put_contents($chatFile, $baris, FILE_APPEND);

header("Location: detail.php?id=$idBuku");
exit;
