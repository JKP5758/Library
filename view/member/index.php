<?php 
include '../../includes/header.php';
include '../../includes/db.php';

$nickname = $_SESSION['username'] ?? null;

// Fetch posts from database, join with users table to get display name
$query = "SELECT u.nama AS display_name, u.username, c.chat, c.media, c.waktu FROM community_posts c JOIN users u ON c.id_user = u.id_user ORDER BY c.waktu DESC";
$result = mysqli_query($conn, $query);

$posts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $posts[] = [
        'user' => $row['username'],
        'chat' => $row['chat'],
        'media' => $row['media'],
        'timestamp' => date('d M Y, H:i', strtotime($row['waktu']))
    ];
}

// Close database connection
mysqli_close($conn);
?>

<div class="community-container">
    <h2 class="community-title">Community Feed</h2>

    <?php if ($nickname): ?>
    <form action="proses_community.php" method="post" enctype="multipart/form-data" class="post-form">
        <textarea name="chat" rows="3" placeholder="Apa yang kamu pikirkan?" required></textarea>
        <div class="form-actions">
            <div class="file-upload">
                <label for="media" class="file-label">
                    <i class="fas fa-image"></i> Tambah Media
                </label>
                <input type="file" name="media" id="media" accept="image/*,video/*">
            </div>
            <button type="submit" class="post-btn">Bagikan</button>
        </div>
    </form>
    <?php else: ?>
    <div class="login-message">
        <p><em>Silakan login untuk memposting ke komunitas.</em></p>
    </div>
    <?php endif; ?>

    <div class="feed-grid">
        <?php foreach ($posts as $post): ?>
        <div class="post">
            <div class="post-header">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="username"><?= htmlspecialchars($post['user']) ?></span>
                </div>
                <div class="timestamp"><?= $post['timestamp'] ?></div>
            </div>
            
            <?php if ($post['media']): ?>
                <?php 
                $ext = pathinfo($post['media'], PATHINFO_EXTENSION); 
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                    <div class="post-media">
                        <img src="../../assets/uploads/<?= htmlspecialchars($post['media']) ?>" alt="Media">
                    </div>
                <?php elseif (in_array(strtolower($ext), ['mp4', 'webm'])): ?>
                    <div class="post-media">
                        <video controls>
                            <source src="../../assets/uploads/<?= htmlspecialchars($post['media']) ?>" type="video/<?= $ext ?>">
                        </video>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="post-content">
                <p><?= htmlspecialchars($post['chat']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
