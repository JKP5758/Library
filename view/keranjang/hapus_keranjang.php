<?php
session_start();

// Include database connection
include '../../includes/db.php';

if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login/';</script>";
    exit;
}

$idUser = $_SESSION['id_user'];
$idBuku = $_GET['id'] ?? null;

if (!$idBuku) {
    echo "<script>alert('ID buku tidak ditemukan!'); window.location='../keranjang';</script>";
    exit;
}

// Delete the cart item from database
$deleteQuery = "DELETE FROM carts WHERE id_user = ? AND id_buku = ?";
$deleteStmt = mysqli_prepare($conn, $deleteQuery);
mysqli_stmt_bind_param($deleteStmt, "ii", $idUser, $idBuku);
mysqli_stmt_execute($deleteStmt);

// Close database connection
mysqli_close($conn);

echo "<script>alert('Barang berhasil dihapus dari keranjang!'); window.location='../keranjang';</script>";
exit;
?>
