<?php
include 'includes/header.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$dataFile = 'data/users.txt';
$userData = null;

if (file_exists($dataFile)) {
    $lines = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        list($id, $nama, $user, $pass, $email, $telp, $alamat) = explode('|', $line);
        if (trim($user) === $username) {
            $userData = [
                'ID' => $id,
                'Nama Lengkap' => $nama,
                'Username' => $user,
                'Email' => $email,
                'Telepon' => $telp,
                'Alamat' => $alamat
            ];
            break;
        }
    }
}

if (!$userData) {
    echo "<p>Data pengguna tidak ditemukan.</p>";
    exit();
}
?>

<div class="profile-card">
    <div class="profile-header">
        <img src="assets/images/user.jpg" alt="Foto Profil" class="profile-avatar">
        <div class="profile-title">
            <h2>Profil Pengguna</h2>
            <p>Selamat datang, <?= htmlspecialchars($userData['Nama Lengkap']) ?></p>
        </div>
    </div>
    
    <div class="profile-info">
        <?php foreach ($userData as $label => $value): ?>
            <div class="info-group">
                <div class="info-label"><?= $label ?></div>
                <div class="info-value"><?= htmlspecialchars($value) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="profile-actions">
        <a href="edit_profil.php" class="profile-btn edit-btn">Edit Profil</a>
        <a href="logout.php" class="profile-btn logout-btn">Logout</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>