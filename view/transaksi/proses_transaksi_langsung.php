<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['username']);
$userId = $isLoggedIn ? $_SESSION['id'] : '000'; // Use '000' for guest users

// Get current date and time
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

// Format items for transaction record
$itemsData = $idBuku . ':' . $judul . ':' . $jumlah . ':' . $harga;

// Create transaction record
$transactionRecord = $transactionId . '|' . 
                    $userId . '|' . 
                    $nama . '|' . 
                    $email . '|' . 
                    $alamat . '|' . 
                    $noHp . '|' . 
                    $jenisPengiriman . '|' . 
                    $metodePembayaran . '|' . 
                    $totalTagihan . '|' . 
                    $shippingCost . '|' . 
                    $discountAmount . '|' . 
                    $itemsData . '|' . 
                    $currentDate . '|' . 
                    'pending';

// Save transaction to file
$transaksiFile = 'data/transaksi.txt';

// Create header if file doesn't exist
if (!file_exists($transaksiFile)) {
    $header = "ID Transaksi|ID User|Nama|Email|Alamat|No HP|Jenis Pengiriman|Metode Pembayaran|Total Tagihan|Biaya Pengiriman|Diskon|Items|Tanggal|Status\n";
    file_put_contents($transaksiFile, $header);
}

// Append transaction record
file_put_contents($transaksiFile, $transactionRecord . "\n", FILE_APPEND);

// Redirect to success page
header('Location: sukses.php?id=' . $transactionId);
exit;
?> 