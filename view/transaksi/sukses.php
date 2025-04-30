<?php
include '../../includes/header.php';
include '../../includes/db.php';

// Get transaction ID from URL
$transactionId = isset($_GET['id']) ? $_GET['id'] : '';

// Get transaction details from database if needed
$transactionDetails = null;
if ($transactionId) {
    $query = "SELECT t.*, ti.jumlah, b.nama_buku, b.harga_buku 
              FROM transactions t 
              JOIN transaction_items ti ON t.transaction_id = ti.transaction_id 
              JOIN books b ON ti.id_buku = b.id_buku 
              WHERE t.transaction_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $transactionId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $transactionDetails = mysqli_fetch_assoc($result);
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
            <?php if ($transactionDetails): ?>
            <p>Total Pembayaran: <strong>Rp. <?= number_format($transactionDetails['total_tagihan'], 0, ',', '.') ?></strong></p>
            <p>Status: <strong><?= ucfirst($transactionDetails['status']) ?></strong></p>
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
</style>

<?php include '../../includes/footer.php'; ?> 