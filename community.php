<?php 
include 'includes/header.php';

$nickname = $_SESSION['username'] ?? null;
$communityFile = 'data/community.txt';

$posts = [];
if (file_exists($communityFile)) {
    $lines = file($communityFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) >= 4) {
            list($user, $chatEncoded, $media, $timestampRaw) = $parts;
            $chat = base64_decode($chatEncoded);

            // Ubah format waktu jadi lebih ramah
            $timestamp = date('d M Y, H:i', strtotime($timestampRaw));

            $posts[] = [
                'user' => $user,
                'chat' => $chat,
                'media' => $media,
                'timestamp' => $timestamp
            ];
        }
    }
}
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
        <?php foreach (array_reverse($posts) as $post): ?>
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
                        <img src="data/uploads/<?= htmlspecialchars($post['media']) ?>" alt="Media">
                    </div>
                <?php elseif (in_array(strtolower($ext), ['mp4', 'webm'])): ?>
                    <div class="post-media">
                        <video controls>
                            <source src="data/uploads/<?= htmlspecialchars($post['media']) ?>" type="video/<?= $ext ?>">
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

<?php include 'includes/footer.php'; ?>
