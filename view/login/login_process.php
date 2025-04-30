<?php
session_start();

// Include database connection
include '../../includes/db.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Query to check user credentials
$query = "SELECT id_user, username, nama FROM users WHERE username = ? AND password = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $username, $password);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    
    // Set session variables
    $_SESSION['username'] = $user['username'];
    $_SESSION['id_user'] = $user['id_user'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['isLoggedIn'] = true;
    
    // Close database connection
    mysqli_close($conn);
    
    echo "<script>
        alert('Login berhasil! Selamat datang, " . $user['nama'] . "');
        window.location.href = '../dashboard/index.php';
    </script>";
    exit;
} else {
    // Close database connection
    mysqli_close($conn);
    
    echo "<script>
        alert('Username atau password salah!');
        window.location.href = 'index.php';
    </script>";
    exit;
}
