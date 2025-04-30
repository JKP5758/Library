<?php
// Include database connection
include '../../includes/db.php';

$idParam = $_GET['id'] ?? '';
$book = null;

// Fetch book data from database
$query = "SELECT id_buku, judul, harga, gambar01, gambar02, gambar03, deskripsi FROM books WHERE id_buku = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $idParam);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $book = [
        'id' => $row['id_buku'],
        'judul' => $row['judul'],
        'harga' => $row['harga'],
        'gambar' => $row['gambar01'],
        'gambar2' => $row['gambar02'],
        'gambar3' => $row['gambar03'],
        'desc' => $row['deskripsi']
    ];
}

// Fetch chat data from database with user information
$chats = [];
$chatQuery = "SELECT c.id_chat, c.chat, c.media_chat, c.waktu, u.nama, u.username 
              FROM chats c 
              JOIN users u ON c.id_user = u.id_user 
              WHERE c.id_buku = ? 
              ORDER BY c.waktu DESC";
$chatStmt = mysqli_prepare($conn, $chatQuery);
mysqli_stmt_bind_param($chatStmt, "s", $idParam);
mysqli_stmt_execute($chatStmt);
$chatResult = mysqli_stmt_get_result($chatStmt);

if ($chatResult && mysqli_num_rows($chatResult) > 0) {
    while ($chatRow = mysqli_fetch_assoc($chatResult)) {
        $chats[] = [
            'user' => $chatRow['nama'],
            'username' => $chatRow['username'],
            'img' => $chatRow['media_chat'],
            'pesan' => $chatRow['chat'],
            'waktu' => $chatRow['waktu']
        ];
    }
}

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Buku</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main class="site-content">
        <?php if ($book): ?>
            <div class="product-detail">

                <div class="product-gallery">
                    <div class="main-image" style="background-image: url('../../assets/images/cover/<?= htmlspecialchars($book['gambar']) ?>');">
                        <img id="mainPreview" src="../../assets/images/cover/<?= htmlspecialchars($book['gambar']) ?>" alt="Gambar Produk">
                    </div>
                    <div class="thumbnail-gallery">
                        <img src="../../assets/images/cover/<?= htmlspecialchars($book['gambar']) ?>" onclick="changeImage(this)">
                        <img src="../../assets/images/cover/<?= htmlspecialchars($book['gambar2']) ?>" onclick="changeImage(this)">
                        <img src="../../assets/images/cover/<?= htmlspecialchars($book['gambar3']) ?>" onclick="changeImage(this)">
                    </div>
                </div>


                <div style="flex: 1; max-width: 500px;">
                    <h2><?= htmlspecialchars($book['judul']) ?></h2>
                    <p class="price">Rp.<?= number_format($book['harga'], 0, ',', '.') ?></p>
                    <p class="desc"><?= htmlspecialchars($book['desc']) ?></p>

                    <div class="like" onclick="toggleLike(this)">
                        <img src="../../assets/images/love_outlane.png" alt="like">
                        <p>276</p>
                    </div>


                    <form method="POST" style="margin-top: 50px;">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($book['id']) ?>">
                        <input type="hidden" name="judul" value="<?= htmlspecialchars($book['judul']) ?>">
                        <input type="hidden" name="harga" value="<?= htmlspecialchars($book['harga']) ?>">
                        

                        <div class="jumlah">
                            <label for="jumlah">Jumlah:</label>
                            <input type="number" name="jumlah" id="jumlah" min="1" value="1" required>
                        </div>

                        <?php if (!$isLoggedIn): ?>
                            <a href="transaksi_langsung.php?id=<?= htmlspecialchars($book['id']) ?>&judul=<?= urlencode($book['judul']) ?>&harga=<?= urlencode($book['harga']) ?>&jumlah=<?= htmlspecialchars($_POST['jumlah'] ?? '1') ?>" class="beli">Beli Langsung</a>
                        <?php else: ?>
                            <button type="submit" formaction="../keranjang/proses_keranjang.php" class="keranjang">+ Keranjang</button>
                            <a href="../transaksi/transaksi_langsung.php?id=<?= htmlspecialchars($book['id']) ?>&judul=<?= urlencode($book['judul']) ?>&harga=<?= urlencode($book['harga']) ?>&jumlah=<?= htmlspecialchars($_POST['jumlah'] ?? '1') ?>" class="beli">Beli Langsung</a>
                        <?php endif; ?>
                    </form>

                </div>
            </div>
            
            <!-- Chat Section -->
            <div class="book-chat">
                <h2>Komentar Buku</h2>

                <?php if ($isLoggedIn): ?>
                    <form action="proses_chat.php" method="post" enctype="multipart/form-data" class="post-form chat-form">
                        <input type="hidden" name="id_buku" value="<?= htmlspecialchars($book['id']) ?>">

                        <textarea name="pesan" rows="4" placeholder="Tulis pesanmu..." required></textarea>

                        <div class="form-actions">
                            <div class="file-upload">
                                <label for="gambar" class="file-label">
                                    <i class="fas fa-image"></i> Tambah Gambar
                                </label>
                                <input type="file" name="gambar" id="gambar" accept="image/*">
                            </div>
                            <button type="submit" class="post-btn">Kirim</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="book-login-msg">Silakan login untuk mengirim komentar.</p>
                <?php endif; ?>

                <?php if (empty($chats)): ?>
                    <p class="book-login-msg">Belum ada komentar untuk buku ini.</p>
                <?php else: ?>
                    <div class="book-chat-list">
                        <?php foreach ($chats as $chat): ?>
                            <div class="book-chat-box">
                                <div class="book-chat-username"><?= htmlspecialchars($chat['user']) ?> <span class="chat-time"><?= date('d M Y H:i', strtotime($chat['waktu'])) ?></span></div>
                                <?php if (!empty($chat['img']) && $chat['img'] !== 'default.png'): ?>
                                    <div class="book-chat-img">
                                        <a href="../../data/img/<?= htmlspecialchars($chat['img']) ?>">
                                            <img src="../../data/img/<?= htmlspecialchars($chat['img']) ?>" alt="Gambar Komentar">
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <p class="book-chat-message"><?= htmlspecialchars($chat['pesan']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>Produk tidak ditemukan.</p>
        <?php endif; ?>
    </main>

    <?php include '../../includes/footer.php'; ?>

    <script>
        // Mengganti gambar utama
        function changeImage(el) {
            const preview = document.getElementById("mainPreview");
            const container = document.querySelector(".main-image");

            preview.src = el.src;
            container.style.backgroundImage = `url(${el.src})`;
        }

        // Effek Zoom
        const image = document.querySelector(".main-image");

        image.addEventListener("mousemove", (e) => {
            image.style.backgroundPosition = -e.offsetX + "px " + -e.offsetY + "px";
        });

        // Tombol Like
        const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

        function toggleLike(element) {
            if (!isLoggedIn) {
                alert("Silakan login untuk menyukai produk ini.");
                return;
            }

            const img = element.querySelector('img');
            const count = element.querySelector('p');
            const liked = img.src.includes('love_fill.png');

            img.src = liked ? '../../assets/images/love_outlane.png' : '../../assets/images/love_fill.png';

            let likeCount = parseInt(count.textContent);
            count.textContent = liked ? likeCount - 1 : likeCount + 1;
        }
    </script>

</body>
</html>
