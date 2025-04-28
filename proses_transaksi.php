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
$idKeranjang = isset($_POST['id_keranjang']) ? $_POST['id_keranjang'] : '';

// Convert comma-separated string to array
$purchasedEntryIds = !empty($idKeranjang) ? explode(',', $idKeranjang) : [];

// Get items from cart
$keranjangFile = 'data/keranjang.txt';
$produkFile = 'data/books.txt';
$items = [];

// Read cart items
if (file_exists($keranjangFile) && $isLoggedIn) {
    $lines = file($keranjangFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        list($entryId, $entryUser, $entryBuku, $entryJumlah) = explode('|', $line);
        if ($entryUser === $userId && in_array($entryId, $purchasedEntryIds)) {
            $items[] = [
                'id_buku' => $entryBuku,
                'jumlah' => $entryJumlah,
                'entry_id' => $entryId
            ];
        }
    }
}

// Get book details
$books = [];
if (file_exists($produkFile)) {
    $bookLines = file($produkFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($bookLines as $bookLine) {
        list($id, $judul, $harga, $gambar) = explode('|', $bookLine);
        $books[$id] = [
            'judul' => $judul,
            'harga' => $harga
        ];
    }
}

// Format items for transaction record
$itemsData = '';

foreach ($items as $item) {
    $bookId = $item['id_buku'];
    $quantity = $item['jumlah'];
    $bookTitle = isset($books[$bookId]) ? $books[$bookId]['judul'] : 'Unknown Book';
    $bookPrice = isset($books[$bookId]) ? $books[$bookId]['harga'] : '0';
    
    $itemsData .= $bookId . ':' . $bookTitle . ':' . $quantity . ':' . $bookPrice . ';';
}

// Remove trailing semicolon
$itemsData = rtrim($itemsData, ';');

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

// Clear only the purchased items from cart for logged-in users
if ($isLoggedIn && file_exists($keranjangFile)) {
    $lines = file($keranjangFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newLines = [];
    
    foreach ($lines as $line) {
        list($entryId, $entryUser, $entryBuku, $entryJumlah) = explode('|', $line);
        
        // Keep items that either:
        // 1. Belong to a different user
        // 2. Are cart entries that weren't purchased
        if ($entryUser !== $userId || !in_array($entryId, $purchasedEntryIds)) {
            $newLines[] = $line;
        }
    }
    
    file_put_contents($keranjangFile, implode("\n", $newLines) . "\n");
}

// Redirect to success page
header('Location: sukses.php?id=' . $transactionId);
exit;
?> 