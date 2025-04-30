<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login/index.php");
    exit();
}

$username = $_SESSION['username'];

// Ambil data dari form
$nama = $_POST['nama'];
$email = $_POST['email'];
$telepon = $_POST['telepon'];
$alamat = $_POST['alamat'];
$password = $_POST['password'];

// Validasi data
if (empty($nama) || empty($email) || empty($telepon) || empty($alamat)) {
    $_SESSION['error'] = "Semua field harus diisi kecuali password.";
    header("Location: edit_profil.php");
    exit();
}

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Format email tidak valid.";
    header("Location: edit_profil.php");
    exit();
}

// Update user data in database
if (!empty($password)) {
    // Update with new password
    $query = "UPDATE users SET nama = ?, email = ?, telfon = ?, alamat = ?, password = ? WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssss", $nama, $email, $telepon, $alamat, $password, $username);
} else {
    // Update without changing password
    $query = "UPDATE users SET nama = ?, email = ?, telfon = ?, alamat = ? WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $nama, $email, $telepon, $alamat, $username);
}

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = "Profil berhasil diperbarui.";
    header("Location: ../user");
} else {
    $_SESSION['error'] = "Gagal memperbarui profil. " . mysqli_error($conn);
    header("Location: edit_profil.php");
}

// Close database connection
mysqli_close($conn);
exit();
?> 