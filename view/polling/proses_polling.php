<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

if (isset($_POST['buku'])) {
    $userId = $_SESSION['id'];
    $username = $_SESSION['username'];
    list($idBuku, $namaBuku) = explode('|', $_POST['buku']);

    $data = "$userId|$username|$idBuku|$namaBuku\n";
    file_put_contents('data/polling.txt', $data, FILE_APPEND);

    echo "<script>alert('Polling berhasil dikirim!'); window.location='polling.php';</script>";
    exit;
} else {
    echo "<script>alert('Polling gagal, data tidak lengkap!'); window.location='polling.php';</script>";
    exit;
}
