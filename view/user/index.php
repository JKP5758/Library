<?php
include '../../includes/header.php';
include '../../includes/db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login/");
    exit();
}

$username = $_SESSION['username'];
$userData = null;

// Get user data from database
$query = "SELECT id_user, nama, username, email, telfon, alamat FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $userData = [
        'ID' => $row['id_user'],
        'Nama Lengkap' => $row['nama'],
        'Username' => $row['username'],
        'Email' => $row['email'],
        'Telepon' => $row['telfon'],
        'Alamat' => $row['alamat']
    ];
}

// Close database connection
mysqli_close($conn);

if (!$userData) {
    echo "<p>Data pengguna tidak ditemukan.</p>";
    exit();
}
?>

<div class="profile-card">
    <div class="profile-header">
        <img src="../../assets/images/user.jpg" alt="Foto Profil" class="profile-avatar">
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
        <a href="../login/logout.php" class="profile-btn logout-btn">Logout</a>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>