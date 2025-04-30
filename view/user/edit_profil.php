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
        'id' => $row['id_user'],
        'nama' => $row['nama'],
        'username' => $row['username'],
        'email' => $row['email'],
        'telepon' => $row['telfon'],
        'alamat' => $row['alamat']
    ];
}

// Close database connection
mysqli_close($conn);

if (!$userData) {
    echo "<p>Data pengguna tidak ditemukan.</p>";
    exit();
}
?>

<div class="form-container edit-profile-container">
    <h2>Edit Profil</h2>
    
    <form action="proses_edit_profil.php" method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($userData['nama']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($userData['username']) ?>" readonly>
            <small>Username tidak dapat diubah</small>
        </div>
        
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>No Telepon</label>
            <input type="text" name="telepon" value="<?= htmlspecialchars($userData['telepon']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" required><?= htmlspecialchars($userData['alamat']) ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Password Baru (Kosongkan jika tidak ingin mengubah)</label>
            <input type="password" name="password" id="password" placeholder="Min. 8 karakter, huruf besar, kecil & angka">
            <div id="password-error" class="error-message"></div>
        </div>
        
        <div class="form-group">
            <label>Konfirmasi Password Baru</label>
            <input type="password" id="konfirmasi_password">
            <div id="konfirmasi-error" class="error-message"></div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
            <a href="profil.php" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
    const passwordInput = document.getElementById("password");
    const konfirmasiInput = document.getElementById("konfirmasi_password");
    const passwordError = document.getElementById("password-error");
    const konfirmasiError = document.getElementById("konfirmasi-error");

    function checkPasswordStrength() {
        const pass = passwordInput.value;
        if (pass === "") return true; // Password kosong diizinkan
        
        const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        if (!pattern.test(pass)) {
            passwordError.textContent = "Password harus minimal 8 karakter, huruf besar, kecil, dan angka.";
            return false;
        } else {
            passwordError.textContent = "";
            return true;
        }
    }

    function checkPasswordMatch() {
        const pass = passwordInput.value;
        const konfirmasi = konfirmasiInput.value;
        
        if (pass === "" && konfirmasi === "") return true; // Keduanya kosong diizinkan
        
        if (konfirmasi !== pass) {
            konfirmasiError.textContent = "Konfirmasi password tidak cocok.";
            return false;
        } else {
            konfirmasiError.textContent = "";
            return true;
        }
    }

    function validateForm() {
        const isPasswordValid = checkPasswordStrength();
        const isPasswordMatch = checkPasswordMatch();
        
        return isPasswordValid && isPasswordMatch;
    }

    passwordInput.addEventListener("input", checkPasswordStrength);
    konfirmasiInput.addEventListener("input", checkPasswordMatch);
</script>

<?php include '../../includes/footer.php'; ?> 