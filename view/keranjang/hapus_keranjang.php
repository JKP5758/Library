<?php
session_start();

if (!isset($_SESSION['id'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

$idUser = $_SESSION['id'];
$idBuku = $_GET['id'] ?? null;

if (!$idBuku) {
    echo "<script>alert('ID buku tidak ditemukan!'); window.location='keranjang.php';</script>";
    exit;
}

$filename = 'data/keranjang.txt';

if (file_exists($filename)) {
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newLines = [];

    foreach ($lines as $line) {
        list($id, $userId, $bookId, $jumlah) = explode('|', $line);

        // Simpan hanya yang bukan milik user ini dan bukan ID buku yang mau dihapus
        if (!($userId === $idUser && $bookId === $idBuku)) {
            $newLines[] = $line;
        }
    }

    file_put_contents($filename, implode(PHP_EOL, $newLines));
}

echo "<script>alert('Barang berhasil dihapus dari keranjang!'); window.location='keranjang.php';</script>";
exit;
?>
