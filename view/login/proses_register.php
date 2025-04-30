<?php
session_start();

// Include database connection
include '../../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']); // tanpa hash
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);
    $alamat = trim($_POST['alamat']);

    // Check if username already exists
    $checkQuery = "SELECT id_user FROM users WHERE username = ?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "s", $username);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>alert('Username sudah digunakan!'); window.location='register.php';</script>";
        exit;
    }

    // Check if email already exists
    $emailQuery = "SELECT id_user FROM users WHERE email = ?";
    $emailStmt = mysqli_prepare($conn, $emailQuery);
    mysqli_stmt_bind_param($emailStmt, "s", $email);
    mysqli_stmt_execute($emailStmt);
    $emailResult = mysqli_stmt_get_result($emailStmt);

    if (mysqli_num_rows($emailResult) > 0) {
        echo "<script>alert('Email sudah digunakan!'); window.location='register.php';</script>";
        exit;
    }

    // Insert new user into database
    $insertQuery = "INSERT INTO users (nama, username, password, email, telfon, alamat) VALUES (?, ?, ?, ?, ?, ?)";
    $insertStmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($insertStmt, "ssssss", $nama, $username, $password, $email, $telepon, $alamat);

    if (mysqli_stmt_execute($insertStmt)) {
        // Close database connection
        mysqli_close($conn);
        
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='../login';</script>";
        exit;
    } else {
        // Close database connection
        mysqli_close($conn);
        
        echo "<script>alert('Registrasi gagal: " . mysqli_error($conn) . "'); window.location='register.php';</script>";
        exit;
    }
} else {
    header("Location: register.php");
    exit;
}
