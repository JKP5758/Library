<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h2>Login</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="login_process.php">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Login</button>
        </div>
        
        <p>Belum punya akun? <a href="register.php">Daftar!</a></p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
