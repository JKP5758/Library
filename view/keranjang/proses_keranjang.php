<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idBuku = $_POST['id'] ?? '';
    $jumlah = $_POST['jumlah'] ?? 1;
    $idUser = $_SESSION['id'];

    // Generate ID keranjang unik
    $keranjangFile = 'data/keranjang.txt';
    $idKeranjang = 1;

    if (file_exists($keranjangFile) && filesize($keranjangFile) > 0) {
        $lines = file($keranjangFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lastLine = end($lines);
        $lastId = explode('|', $lastLine)[0];
        $idKeranjang = (int)$lastId + 1;
    }

    // Simpan ke file
    $entry = "\n$idKeranjang|$idUser|$idBuku|$jumlah";
    file_put_contents($keranjangFile, $entry, FILE_APPEND);
    

    echo "<script>
        alert('Berhasil ditambahkan ke keranjang!');
        window.location.href = 'index.php';
    </script>";
    exit();
} else {
    echo "<script>alert('Akses tidak valid'); window.location.href='index.php';</script>";
    exit();
}
?>
