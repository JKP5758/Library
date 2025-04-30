<?php
include '../../includes/header.php';
include '../../includes/db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['username']);
$userData = [];

// Get product data from URL parameters
$idBuku = isset($_GET['id']) ? $_GET['id'] : '';
$judul = isset($_GET['judul']) ? $_GET['judul'] : '';
$harga = isset($_GET['harga']) ? $_GET['harga'] : '0';
$jumlah = isset($_GET['jumlah']) ? $_GET['jumlah'] : '1';

// Calculate total price
$hargaBersih = (int) str_replace(['Rp.', '.', ','], '', $harga);
$totalTagihan = $hargaBersih * $jumlah;

// If user is logged in, get their data from database
if ($isLoggedIn) {
    $username = $_SESSION['username'];
    $query = "SELECT nama, email, telfon, alamat FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $userData = [
            'nama' => $row['nama'],
            'email' => $row['email'],
            'noHp' => $row['telfon'],
            'alamat' => $row['alamat']
        ];
    }
}

// Define shipping costs
$shippingCosts = [
    'regular' => 15000,
    'express' => 30000,
    'sameDay' => 50000
];

// Close database connection
mysqli_close($conn);
?>

<div class="site-content">
    <div class="form-container" style="max-width: 600px;">
        <h2>Form Transaksi Langsung</h2>
        <?php if (!$isLoggedIn): ?>
            <div class="alert alert-info">
                <p>Anda sedang melakukan pembelian sebagai tamu. Silakan isi data diri Anda di bawah ini.</p>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="proses_transaksi_langsung.php">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?= $isLoggedIn ? htmlspecialchars($userData['nama']) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= $isLoggedIn ? htmlspecialchars($userData['email']) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat Pengiriman</label>
                <textarea id="alamat" name="alamat" rows="3" required><?= $isLoggedIn ? htmlspecialchars($userData['alamat']) : '' ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="noHp">Nomor Telepon</label>
                <input type="tel" id="noHp" name="noHp" value="<?= $isLoggedIn ? htmlspecialchars($userData['noHp']) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="jenisPengiriman">Jenis Pengiriman</label>
                <select id="jenisPengiriman" name="jenisPengiriman" required onchange="updateTotal()">
                    <option value="">Pilih Jenis Pengiriman</option>
                    <option value="regular">Regular (2-3 hari) - Rp. 15.000</option>
                    <option value="express">Express (1 hari) - Rp. 30.000</option>
                    <option value="sameDay">Same Day - Rp. 50.000</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="metodePembayaran">Metode Pembayaran</label>
                <select id="metodePembayaran" name="metodePembayaran" required>
                    <option value="">Pilih Metode Pembayaran</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="ewallet">E-Wallet</option>
                    <option value="cod">Cash on Delivery</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="couponCode">Kode Kupon</label>
                <div class="coupon-container">
                    <input type="text" id="couponCode" name="couponCode" placeholder="Masukkan kode kupon">
                    <button type="button" id="applyCouponBtn" class="apply-coupon-btn">Terapkan</button>
                </div>
                <div id="couponMessage" class="coupon-message"></div>
            </div>
            
            <div class="form-group">
                <label>Rincian Tagihan</label>
                <div class="bill-details">
                    <div class="bill-row">
                        <span>Total Harga Produk:</span>
                        <span>Rp. <?= number_format($totalTagihan, 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="bill-row discount" id="discountRow" style="display: none;">
                        <span>Kupon Promo (15%):</span>
                        <span id="discountAmount">- Rp. 0</span>
                    </div>
                    
                    <div class="bill-row shipping">
                        <span>Ongkir:</span>
                        <span id="shippingCost">Rp. 0</span>
                    </div>
                    
                    <div class="bill-row total">
                        <span>Total Tagihan:</span>
                        <span id="finalTotal">Rp. <?= number_format($totalTagihan, 0, ',', '.') ?></span>
                    </div>
                </div>
                
                <input type="hidden" name="totalTagihan" id="totalTagihanInput" value="<?= $totalTagihan ?>">
                <input type="hidden" name="shippingCost" id="shippingCostInput" value="0">
                <input type="hidden" name="discountAmount" id="discountAmountInput" value="0">
                <input type="hidden" name="id_buku" value="<?= htmlspecialchars($idBuku) ?>">
                <input type="hidden" name="judul" value="<?= htmlspecialchars($judul) ?>">
                <input type="hidden" name="harga" value="<?= htmlspecialchars($harga) ?>">
                <input type="hidden" name="jumlah" value="<?= htmlspecialchars($jumlah) ?>">
            </div>
            
            <button type="submit" name="submit" class="checkout-btn">Konfirmasi Pembayaran</button>
        </form>
    </div>
</div>

<script>
    // Shipping costs
    const shippingCosts = {
        'regular': 15000,
        'express': 30000,
        'sameDay': 50000
    };
    
    // Base product total
    const baseTotal = <?= $totalTagihan ?>;
    
    // Valid coupon code and discount percentage
    const validCouponCode = 'DISKON15';
    const discountPercentage = 15;
    
    // Variables to track discount state
    let discountApplied = false;
    let discountAmount = 0;
    
    // Function to update the total
    function updateTotal() {
        const shippingSelect = document.getElementById('jenisPengiriman');
        const shippingCost = shippingCosts[shippingSelect.value] || 0;
        
        // Update shipping cost display
        document.getElementById('shippingCost').textContent = 'Rp. ' + shippingCost.toLocaleString('id-ID');
        document.getElementById('shippingCostInput').value = shippingCost;
        
        // Calculate final total
        const finalTotal = baseTotal - discountAmount + shippingCost;
        
        // Update final total display
        document.getElementById('finalTotal').textContent = 'Rp. ' + finalTotal.toLocaleString('id-ID');
        document.getElementById('totalTagihanInput').value = finalTotal;
    }
    
    // Function to apply coupon
    function applyCoupon() {
        const couponInput = document.getElementById('couponCode');
        const couponCode = couponInput.value.trim();
        const couponMessage = document.getElementById('couponMessage');
        const discountRow = document.getElementById('discountRow');
        
        // Reset message
        couponMessage.textContent = '';
        couponMessage.className = 'coupon-message';
        
        if (couponCode === validCouponCode) {
            // Apply discount
            discountApplied = true;
            discountAmount = (baseTotal * discountPercentage) / 100;
            
            // Update UI
            document.getElementById('discountAmount').textContent = '- Rp. ' + discountAmount.toLocaleString('id-ID');
            document.getElementById('discountAmountInput').value = discountAmount;
            discountRow.style.display = 'flex';
            
            // Show success message
            couponMessage.textContent = 'Kupon berhasil diterapkan! Diskon ' + discountPercentage + '%';
            couponMessage.className = 'coupon-message success-message';
            
            // Update total
            updateTotal();
        } else {
            // Show error message
            couponMessage.textContent = 'Kode kupon tidak valid!';
            couponMessage.className = 'coupon-message error-message';
            
            // Reset discount
            discountApplied = false;
            discountAmount = 0;
            document.getElementById('discountAmountInput').value = 0;
            discountRow.style.display = 'none';
            
            // Update total
            updateTotal();
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set up coupon button event listener
        document.getElementById('applyCouponBtn').addEventListener('click', applyCoupon);
        
        // Initialize total
        updateTotal();
    });
</script>

<?php include '../../includes/footer.php'; ?> 