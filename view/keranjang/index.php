<?php 
include '../../includes/header.php';

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login/index.php';</script>";
    exit;
}

// Include database connection
include '../../includes/db.php';

$idUser = $_SESSION['id_user'];

// Fetch cart items from database
$query = "SELECT c.id_keranjang, c.id_buku, c.jumlah, b.judul, b.harga, b.gambar01 
          FROM carts c 
          JOIN books b ON c.id_buku = b.id_buku 
          WHERE c.id_user = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = [
        'id_keranjang' => $row['id_keranjang'],
        'id_buku' => $row['id_buku'],
        'jumlah' => $row['jumlah'],
        'judul' => $row['judul'],
        'harga' => $row['harga'],
        'gambar' => $row['gambar01']
    ];
}

// Close database connection
mysqli_close($conn);
?>

<div class="cart-container">
    <h2>Keranjang</h2>
    <div class="cart-header">
        <input type="checkbox" id="selectAll" />
        <span>Pilih Semua (<?= count($items) ?>)</span>
    </div>

    <?php if (empty($items)): ?>
        <p>Keranjang kamu kosong.</p>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
        <div class="cart-item">
            <input type="checkbox" class="item-check" data-id-keranjang="<?= $item['id_keranjang'] ?>">

            <div class="cart-content">
                <div class="item-detail">
                    <img src="../../assets/images/cover/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['judul']) ?>">
                    <div class="info">
                        <p class="product-title"><?= htmlspecialchars($item['judul']) ?></p>
                            <?php
                            // Bersihin harga jadi angka murni
                            $hargaBersih = (int) str_replace(['Rp.', '.', ','], '', $item['harga']);
                            $jumlah = (int) $item['jumlah'];
                            $total = $hargaBersih * $jumlah;
                            ?>

                            <p class="product-price total-item-price"
                            data-price="<?= $hargaBersih ?>"
                            data-qty="<?= $jumlah ?>">
                            Rp<?= number_format($total, 0, ',', '.') ?>
                        </p>


                    </div>
                    <div class="actions">
                    <a href="hapus_keranjang.php?id=<?= $item['id_buku'] ?>" onclick="return confirm('Yakin mau hapus item ini?')">🗑️</a>
                        <div class="qty">
                            <button>-</button>
                            <input type="text" value="<?= htmlspecialchars($item['jumlah']) ?>" readonly>
                            <button>+</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Bayar -->
<?php if (!empty($items)): ?>
    <div class="checkout-section">
        <div class="total-harga" id="totalHarga">Total: Rp.0</div>
        <button class="checkout-btn" id="bayarBtn" disabled>Bayar Sekarang</button>
    </div>
<?php endif; ?>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const items = document.querySelectorAll('.total-item-price');

        items.forEach(item => {
            const price = parseInt(item.getAttribute('data-price'));
            const qtyInput = item.closest('.cart-item').querySelector('.qty input');
            const updatePrice = () => {
                const qty = parseInt(qtyInput.value);
                const total = price * qty;
                item.textContent = "Rp" + total.toLocaleString("id-ID");
            };

            updatePrice();

            const minusBtn = item.closest('.cart-item').querySelector('.qty button:first-child');
            const plusBtn = item.closest('.cart-item').querySelector('.qty button:last-child');

            minusBtn.addEventListener('click', () => {
                let current = parseInt(qtyInput.value);
                if (current > 1) {
                    qtyInput.value = current - 1;
                    updatePrice();
                    updateTotal(); // Update total when quantity changes
                }
            });

            plusBtn.addEventListener('click', () => {
                let current = parseInt(qtyInput.value);
                qtyInput.value = current + 1;
                updatePrice();
                updateTotal(); // Update total when quantity changes
            });
        });
    });

    // Bayar
    const checkboxes = document.querySelectorAll('.item-check');
    const totalHarga = document.getElementById('totalHarga');
    const bayarBtn = document.getElementById('bayarBtn');
    const selectAllCheckbox = document.getElementById('selectAll');

    function updateTotal() {
        let total = 0;
        let selectedIds = [];

        checkboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                const item = checkbox.closest('.cart-item');
                const priceElement = item.querySelector('.total-item-price');
                const price = parseInt(priceElement.getAttribute('data-price'));
                const qty = parseInt(item.querySelector('.qty input').value);
                const idKeranjang = checkbox.getAttribute('data-id-keranjang');
                
                if (!isNaN(price) && !isNaN(qty)) {
                    total += price * qty;
                    selectedIds.push(idKeranjang);
                }
            }
        });

        totalHarga.textContent = 'Total: Rp.' + total.toLocaleString('id-ID');
        bayarBtn.disabled = total === 0;
        
        // Update the onclick attribute of the bayar button to include the total and selected IDs
        if (total > 0) {
            bayarBtn.onclick = function() {
                window.location.href = '../transaksi/transaksi.php?tagihan=' + total + '&id_keranjang=' + selectedIds.join(',');
            };
        } else {
            bayarBtn.onclick = null;
        }
    }

    // Add event listener for "Pilih Semua" checkbox
    selectAllCheckbox.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateTotal();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));

    // Initialize total
    updateTotal();
</script>


<?php include '../../includes/footer.php'; ?>