<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']); // tanpa hash
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);
    $alamat = trim($_POST['alamat']);

    $file = 'data/users.txt';
    if (!file_exists('data')) {
        mkdir('data');
    }

    // Cek apakah username sudah ada
    $id = 1;
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (isset($parts[2]) && $parts[2] == $username) {
            echo "<script>alert('Username sudah digunakan!'); window.location='register.php';</script>";
            exit;
        }
        $lastId = intval($parts[0]);
        if ($lastId >= $id) {
            $id = $lastId + 1;
        }
    }

    // Format ID jadi 3 digit
    $idFormatted = str_pad($id, 3, "0", STR_PAD_LEFT);

    $data = "$idFormatted|$nama|$username|$password|$email|$telepon|$alamat\n";
    file_put_contents($file, $data, FILE_APPEND);

    echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
    exit;
} else {
    header("Location: register.php");
    exit;
}
