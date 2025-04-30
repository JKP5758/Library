<?php
session_start();
include '../../includes/db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['username']);
$userId = $isLoggedIn ? $_SESSION['id_user'] : '000'; // Use '000' for guest users

// Get current date and time
date_default_timezone_set('Asia/Jakarta');
$currentDate = date('Y-m-d H:i:s');

// Generate a unique transaction ID
$transactionId = 'TRX' . date('YmdHis') . rand(100, 999);

// Get form data
$nama = isset($_POST['nama']) ? $_POST['nama'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$alamat = isset($_POST['alamat']) ? $_POST['alamat'] : '';
$noHp = isset($_POST['noHp']) ? $_POST['noHp'] : '';
$jenisPengiriman = isset($_POST['jenisPengiriman']) ? $_POST['jenisPengiriman'] : '';
$metodePembayaran = isset($_POST['metodePembayaran']) ? $_POST['metodePembayaran'] : '';
$totalTagihan = isset($_POST['totalTagihan']) ? $_POST['totalTagihan'] : '0';
$shippingCost = isset($_POST['shippingCost']) ? $_POST['shippingCost'] : '0';
$discountAmount = isset($_POST['discountAmount']) ? $_POST['discountAmount'] : '0';

// Get product data
$idBuku = isset($_POST['id_buku']) ? $_POST['id_buku'] : '';
$judul = isset($_POST['judul']) ? $_POST['judul'] : '';
$harga = isset($_POST['harga']) ? $_POST['harga'] : '0';
$jumlah = isset($_POST['jumlah']) ? $_POST['jumlah'] : '1';

// Insert transaction into database
$query = "INSERT INTO transactions (transaction_id, id_user, nama, email, alamat, no_hp, 
          jenis_pengiriman, metode_pembayaran, total_tagihan, biaya_pengiriman, diskon, 
          created_at, status) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sissssssiiss", 
    $transactionId, $userId, $nama, $email, $alamat, $noHp, 
    $jenisPengiriman, $metodePembayaran, $totalTagihan, $shippingCost, 
    $discountAmount, $currentDate
);
mysqli_stmt_execute($stmt);

// Insert transaction item
$query = "INSERT INTO transaction_items (transaction_id, id_buku, jumlah, harga) 
          VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
$hargaBersih = (int) str_replace(['Rp.', '.', ','], '', $harga);
mysqli_stmt_bind_param($stmt, "siid", $transactionId, $idBuku, $jumlah, $hargaBersih);
mysqli_stmt_execute($stmt);

// Close database connection
mysqli_close($conn);

// Redirect to success page
header('Location: sukses.php?id=' . $transactionId);
exit;
?> 