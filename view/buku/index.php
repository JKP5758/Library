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

// Fetch genres for this book
$genres = [];
if ($book) {
    $genreQuery = "SELECT g.genre FROM genre_relasi gr JOIN genre g ON gr.id_genre = g.id_genre WHERE gr.id_book = ?";
    $genreStmt = mysqli_prepare($conn, $genreQuery);
    mysqli_stmt_bind_param($genreStmt, "s", $book['id']);
    mysqli_stmt_execute($genreStmt);
    $genreResult = mysqli_stmt_get_result($genreStmt);
    while ($genreRow = mysqli_fetch_assoc($genreResult)) {
        $genres[] = $genreRow['genre'];
    }
}

// Fetch chat data from database with user information
$chatTree = [];
$chatIndex = [];
$chatQuery = "SELECT c.id_chat, c.id_parent, c.chat, c.media_chat, c.waktu, u.nama, u.username 
              FROM chats c 
              JOIN users u ON c.id_user = u.id_user 
              WHERE c.id_buku = ? 
              ORDER BY c.waktu ASC";
$chatStmt = mysqli_prepare($conn, $chatQuery);
mysqli_stmt_bind_param($chatStmt, "s", $idParam);
mysqli_stmt_execute($chatStmt);
$chatResult = mysqli_stmt_get_result($chatStmt);

if ($chatResult && mysqli_num_rows($chatResult) > 0) {
    while ($chatRow = mysqli_fetch_assoc($chatResult)) {
        $chat = [
            'id_chat' => $chatRow['id_chat'],
            'id_parent' => $chatRow['id_parent'],
            'user' => $chatRow['nama'],
            'username' => $chatRow['username'],
            'img' => $chatRow['media_chat'],
            'pesan' => $chatRow['chat'],
            'waktu' => $chatRow['waktu'],
            'balasan' => []
        ];
        $chatIndex[$chat['id_chat']] = $chat;

        if ($chat['id_parent'] === null) {
            $chatTree[] = &$chatIndex[$chat['id_chat']];
        } else {
            $chatIndex[$chat['id_parent']]['balasan'][] = &$chatIndex[$chat['id_chat']];
        }
    }
}

// Close database connection
mysqli_close($conn);

function tampilkanChat($chatList, $level = 0) {
    foreach ($chatList as $chat) {
        $indent = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $level);
        echo '<div class="chat-item" id="chat-'.$chat['id_chat'].'" data-level="'.$level.'">';
        echo '<div class="chat-header">';
        echo '<div class="chat-user">';
        echo '<span class="username">' . htmlspecialchars($chat['username']) . '</span>';
        echo '<span class="chat-time">' . date('d M Y', strtotime($chat['waktu'])) . '</span>';
        echo '</div>';
        echo '</div>';

        echo '<div class="chat-content">';
        if (!empty($chat['img']) && $chat['img'] !== 'default.png') {
            echo '<div class="chat-media">';
            echo '<a href="../../data/img/' . htmlspecialchars($chat['img']) . '" target="_blank">';
            echo '<img src="../../data/img/' . htmlspecialchars($chat['img']) . '" alt="Gambar Komentar">';
            echo '</a>';
            echo '</div>';
        }

        echo '<div class="chat-message">' . htmlspecialchars($chat['pesan']) . '</div>';
        echo '</div>';

        echo '<div class="chat-actions">';
        echo '<button class="reply-btn" onclick="toggleReplyForm('.$chat['id_chat'].')">';
        echo '<i class="fas fa-reply"></i> Balas';
        echo '</button>';
        echo '</div>';

        echo '<div id="reply-form-'.$chat['id_chat'].'" class="reply-form" style="display:none;">';
        echo '<form action="proses_chat.php" method="post" enctype="multipart/form-data">';
        echo '<input type="hidden" name="id_buku" value="'.htmlspecialchars($GLOBALS['book']['id']).'">';
        echo '<input type="hidden" name="id_parent" value="'.$chat['id_chat'].'">';
        echo '<div class="reply-header">';
        echo '<div class="reply-info">';
        echo '<i class="fas fa-reply"></i>';
        echo '<span>Membalas <strong>'.htmlspecialchars($chat['username']).'</strong></span>';
        echo '</div>';
        echo '<div class="original-message">';
        echo '<p>'.htmlspecialchars($chat['pesan']).'</p>';
        echo '</div>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<textarea name="pesan" placeholder="Tulis balasan..." required></textarea>';
        echo '</div>';
        echo '<div class="form-actions">';
        echo '<div class="file-upload">';
        echo '<label for="gambar-'.$chat['id_chat'].'" class="file-label">';
        echo '<i class="fas fa-image"></i> Tambah Gambar';
        echo '</label>';
        echo '<input type="file" name="gambar" id="gambar-'.$chat['id_chat'].'" accept="image/*">';
        echo '</div>';
        echo '<button type="submit" class="submit-btn">Kirim Balasan</button>';
        echo '</div>';
        echo '</form>';
        echo '</div>';

        if (!empty($chat['balasan'])) {
            echo '<div class="chat-replies">';
            tampilkanChat($chat['balasan'], $level + 1);
            echo '</div>';
        }

        echo '</div>';
    }
}

include '../../includes/header.php'; 

?>

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
                    <?php if (!empty($genres)): ?>
                        <div class="book-genres" style="margin-bottom:15px;">
                            <strong>Genre:</strong>
                            <?php foreach ($genres as $genre): ?>
                                <span class="genre-badge" style="display:inline-block;background:#e3f6ff;color:#3498db;padding:3px 10px;border-radius:12px;margin-right:5px;font-size:0.95em;"> <?= htmlspecialchars($genre) ?> </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="like" id="likeBtn" data-id="<?= htmlspecialchars($book['id']) ?>">
                        <img id="likeIcon" src="../../assets/images/love_outlane.png" alt="like">
                        <p id="likeCount">0</p>
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
                            <a href="../transaksi/transaksi_langsung.php?id=<?= htmlspecialchars($book['id']) ?>&judul=<?= urlencode($book['judul']) ?>&harga=<?= urlencode($book['harga']) ?>&jumlah=<?= htmlspecialchars($_POST['jumlah'] ?? '1') ?>" class="beli">Beli Langsung</a>
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

                <?php if (empty($chatTree)): ?>
                    <p class="book-login-msg">Belum ada komentar untuk buku ini.</p>
                <?php else: ?>
                    <div class="book-chat-list">
                        <?php tampilkanChat($chatTree); ?>
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

        // Like AJAX
        const likeBtn = document.getElementById('likeBtn');
        const likeIcon = document.getElementById('likeIcon');
        const likeCount = document.getElementById('likeCount');
        const bookId = likeBtn ? likeBtn.getAttribute('data-id') : null;

        function updateLikeDisplay(total, liked) {
            likeCount.textContent = total;
            likeIcon.src = liked ? '../../assets/images/love_fill.png' : '../../assets/images/love_outlane.png';
        }

        function fetchLike() {
            if (!bookId) return;
            fetch('like_api.php?id_buku=' + encodeURIComponent(bookId))
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateLikeDisplay(data.total, data.liked);
                    }
                });
        }

        if (likeBtn) {
            fetchLike();
            likeBtn.onclick = function() {
                if (!isLoggedIn) {
                    alert('Silakan login untuk menyukai produk ini.');
                    window.location.href = '../../view/login';
                    return;
                }
                fetch('like_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id_buku=' + encodeURIComponent(bookId)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateLikeDisplay(data.total, data.liked);
                    }
                });
            }
        }

        // Fungsi untuk toggle form balas
        function toggleReplyForm(chatId) {
            if (!isLoggedIn) {
                alert("Anda Belum Login");
                window.location.href = "../../view/login";
                return;
            }
            const replyForm = document.getElementById('reply-form-' + chatId);
            const allReplyForms = document.querySelectorAll('.reply-form');
            
            // Sembunyikan semua form balas
            allReplyForms.forEach(form => {
                if (form.id !== 'reply-form-' + chatId) {
                    form.style.display = 'none';
                }
            });
            
            // Toggle form yang dipilih
            if (replyForm.style.display === 'none' || replyForm.style.display === '') {
                replyForm.style.display = 'block';
                // Scroll ke form yang dibuka
                replyForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                replyForm.style.display = 'none';
            }
        }
    </script>
    <script>
    // Scroll smooth ke chat jika ada anchor di URL
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash && window.location.hash.startsWith('#chat-')) {
            var el = document.querySelector(window.location.hash);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('highlight-chat'); // Optional: highlight
                setTimeout(function(){ el.classList.remove('highlight-chat'); }, 2000);
            }
        }
    });
    </script>
</body>
</html>
