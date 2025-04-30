<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login/index.php';</script>";
    exit;
}

if (isset($_POST['buku'])) {
    $userId = $_SESSION['id_user'];
    list($idBuku, $namaBuku) = explode('|', $_POST['buku']);

    // Insert poll into database
    $query = "INSERT INTO polling (id_user, id_buku) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $userId, $idBuku);
    mysqli_stmt_execute($stmt);

    // Close database connection
    mysqli_close($conn);

    echo "<script>alert('Polling berhasil dikirim!'); window.location='../polling';</script>";
    exit;
} else {
    echo "<script>alert('Polling gagal, data tidak lengkap!'); window.location='../polling';</script>";
    exit;
}
