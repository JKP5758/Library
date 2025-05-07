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
$idKeranjang = isset($_POST['id_keranjang']) ? $_POST['id_keranjang'] : '';

// Convert comma-separated string to array
$purchasedEntryIds = !empty($idKeranjang) ? explode(',', $idKeranjang) : [];

// Get items from cart and books
$items = [];
if ($isLoggedIn && !empty($purchasedEntryIds)) {
    $idList = implode(',', array_map('intval', $purchasedEntryIds));
    $query = "SELECT c.id_keranjang, c.id_buku, c.jumlah, b.judul, b.harga 
              FROM carts c 
              JOIN books b ON c.id_buku = b.id_buku 
              WHERE c.id_keranjang IN ($idList) AND c.id_user = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = [
            'id_buku' => $row['id_buku'],
            'judul' => $row['judul'],
            'harga' => $row['harga'],
            'jumlah' => $row['jumlah'],
            'entry_id' => $row['id_keranjang']
        ];
    }
}

// Insert transaction into database
$query = "INSERT INTO transaksi (id_transaksi, id_user, nama, email, alamat, telepon, kurir, metode_pembayaran, subtotal, ongkir, diskon, waktu_transaksi, status) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sissssssiiss", 
    $transactionId, $userId, $nama, $email, $alamat, $noHp, 
    $jenisPengiriman, $metodePembayaran, $totalTagihan, $shippingCost, 
    $discountAmount, $currentDate
);
mysqli_stmt_execute($stmt);

// Insert transaction items
foreach ($items as $item) {
    $query = "INSERT INTO transaksi_detail (id_transaksi, id_buku, jumlah) 
              VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sii", 
        $transactionId, $item['id_buku'], $item['jumlah']
    );
    mysqli_stmt_execute($stmt);
}

// Delete purchased items from cart
if ($isLoggedIn && !empty($purchasedEntryIds)) {
    $idList = implode(',', array_map('intval', $purchasedEntryIds));
    $query = "DELETE FROM carts WHERE id_keranjang IN ($idList) AND id_user = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
}

// Close database connection
mysqli_close($conn);

// Redirect to success page
header('Location: sukses.php?id=' . $transactionId);
exit;
?> 