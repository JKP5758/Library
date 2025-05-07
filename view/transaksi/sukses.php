<?php
include '../../includes/header.php';
include '../../includes/db.php';

// Get transaction ID from URL
$transactionId = isset($_GET['id']) ? $_GET['id'] : '';

// Get transaction details from database if needed
$transactionDetails = [];
$totalPembayaran = 0; // Initialize total payment variable
if ($transactionId) {
    $query = "SELECT t.*, ti.jumlah, b.judul, b.harga, t.subtotal 
              FROM transaksi t 
              JOIN transaksi_detail ti ON t.id_transaksi = ti.id_transaksi 
              JOIN books b ON ti.id_buku = b.id_buku 
              WHERE t.id_transaksi = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $transactionId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $transactionDetails[] = $row; // Store all transaction details
        $totalPembayaran = $row['subtotal']; // Get subtotal for total payment
    }
}

// Close database connection
mysqli_close($conn);
?>

<div class="site-content">
    <div class="form-container" style="max-width: 600px; text-align: center;">
        <h2>Transaksi Berhasil!</h2>
        <div class="success-icon">
            <img src="../../assets/images/success.png" alt="Success" style="width: 100px; margin: 20px auto;">
        </div>
        <p>Terima kasih telah berbelanja di Toko Buku kami.</p>
        <p>Pesanan Anda sedang diproses dan akan segera dikirimkan.</p>
        <p>Anda akan menerima email konfirmasi segera.</p>
        <?php if ($transactionId): ?>
        <div class="transaction-details">
            <p>ID Transaksi: <strong><?= htmlspecialchars($transactionId) ?></strong></p>
            <?php if (!empty($transactionDetails)): ?>
                <p>Total Pembayaran: <strong>Rp. <?= number_format($totalPembayaran, 0, ',', '.') ?></strong></p>
                <p>Status: <strong><?= ucfirst($transactionDetails[0]['status']) ?></strong></p>
                <h3>Detail Pesanan:</h3>
                <ul class="order-list">
                    <?php foreach ($transactionDetails as $detail): ?>
                        <li>
                            <strong><?= htmlspecialchars($detail['judul']) ?></strong> - 
                            Jumlah: <?= htmlspecialchars($detail['jumlah']) ?> - 
                            Harga: Rp. <?= number_format($detail['harga'], 0, ',', '.') ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <p>Simpan ID transaksi ini untuk melacak pesanan Anda.</p>
        </div>
        <?php endif; ?>
        <div style="margin-top: 30px;">
            <a href="../../index.php" class="checkout-btn" style="display: inline-block; text-decoration: none;">Kembali ke Beranda</a>
        </div>
    </div>
</div>

<style>
    .success-icon {
        margin: 20px 0;
    }
    
    .form-container p {
        margin: 10px 0;
        font-size: 16px;
        color: #555;
    }
    
    .transaction-details {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
        border: 1px solid #ddd;
    }
    
    .transaction-details p {
        margin: 5px 0;
    }

    .order-list {
        list-style-type: none; /* Remove default list styling */
        padding: 0; /* Remove default padding */
        margin: 10px 0; /* Add margin for spacing */
        background-color: #e9ecef; /* Light background for the list */
        border-radius: 5px; /* Rounded corners */
        padding: 10px; /* Padding around the list */
    }

    .order-list li {
        padding: 10px; /* Padding for each list item */
        border-bottom: 1px solid #ddd; /* Separator line */
    }

    .order-list li:last-child {
        border-bottom: none; /* Remove bottom border for last item */
    }
</style>

<?php include '../../includes/footer.php'; ?> 