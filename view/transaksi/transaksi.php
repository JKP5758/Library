<?php
include 'includes/header.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['username']);
$userData = [];

// Get total amount and cart entry IDs from keranjang.php
$totalTagihan = isset($_GET['tagihan']) ? $_GET['tagihan'] : '0';
$idKeranjang = isset($_GET['id_keranjang']) ? $_GET['id_keranjang'] : '';

// If user is logged in, get their data from users.txt
if ($isLoggedIn) {
    $usersFile = 'data/users.txt';
    if (file_exists($usersFile)) {
        $lines = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        // Skip header line
        array_shift($lines);
        
        foreach ($lines as $line) {
            list($id, $nama, $username, $password, $email, $noHp, $alamat) = explode('|', $line);
            if ($username === $_SESSION['username']) {
                $userData = [
                    'nama' => $nama,
                    'email' => $email,
                    'noHp' => $noHp,
                    'alamat' => $alamat
                ];
                break;
            }
        }
    }
}

// Define shipping costs
$shippingCosts = [
    'regular' => 15000,
    'express' => 30000,
    'sameDay' => 50000
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Here you would process the form data and create the transaction
    // For now, we'll just redirect to a success page
    header('Location: sukses.php');
    exit;
}
?>

<div class="site-content">
    <div class="form-container" style="max-width: 600px;">
        <h2>Form Transaksi</h2>
        <form method="POST" action="proses_transaksi.php">
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
                <input type="hidden" name="id_keranjang" value="<?= htmlspecialchars($idKeranjang) ?>">
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

<?php include 'includes/footer.php'; ?>
