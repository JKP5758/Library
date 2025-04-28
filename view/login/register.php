<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h2>Registrasi Akun</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="proses_register.php" method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" required>
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" id="password" required placeholder="Min. 8 karakter, huruf besar, kecil & angka">
            <div id="password-error" class="error-message"></div>
        </div>

        <div class="form-group">
            <label>Konfirmasi Password</label>
            <input type="password" id="konfirmasi_password" required>
            <div id="konfirmasi-error" class="error-message"></div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>No Telepon</label>
            <input type="text" name="telepon" required>
        </div>

        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" required></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Daftar</button>
        </div>
        
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </form>
</div>

<script>
    const passwordInput = document.getElementById("password");
    const konfirmasiInput = document.getElementById("konfirmasi_password");
    const passwordError = document.getElementById("password-error");
    const konfirmasiError = document.getElementById("konfirmasi-error");

    function checkPasswordStrength() {
        const pass = passwordInput.value;
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
        if (konfirmasiInput.value !== passwordInput.value) {
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

<?php include 'includes/footer.php'; ?>
