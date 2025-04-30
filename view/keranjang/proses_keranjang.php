<?php
session_start();

// Include database connection
include '../../includes/db.php';

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='../login/';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idBuku = $_POST['id'] ?? '';
    $jumlah = $_POST['jumlah'] ?? 1;
    $idUser = $_SESSION['id_user'];

    // Check if the book is already in the cart
    $checkQuery = "SELECT id_keranjang, jumlah FROM carts WHERE id_user = ? AND id_buku = ?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "ii", $idUser, $idBuku);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);

    if (mysqli_num_rows($checkResult) > 0) {
        // Update existing cart item
        $cartItem = mysqli_fetch_assoc($checkResult);
        $newJumlah = $cartItem['jumlah'] + $jumlah;
        
        $updateQuery = "UPDATE carts SET jumlah = ? WHERE id_keranjang = ?";
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "ii", $newJumlah, $cartItem['id_keranjang']);
        mysqli_stmt_execute($updateStmt);
    } else {
        // Insert new cart item
        $insertQuery = "INSERT INTO carts (id_user, id_buku, jumlah) VALUES (?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($insertStmt, "iii", $idUser, $idBuku, $jumlah);
        mysqli_stmt_execute($insertStmt);
    }

    // Close database connection
    mysqli_close($conn);
    
    echo "<script>
        alert('Berhasil ditambahkan ke keranjang!');
        window.location.href = '../dashboard/index.php';
    </script>";
    exit();
} else {
    echo "<script>alert('Akses tidak valid'); window.location.href='../dashboard/index.php';</script>";
    exit();
}
?>
