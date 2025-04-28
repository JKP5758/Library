<?php
session_start();

$username = $_POST['username'];
$password = $_POST['password'];

$file = 'data/users.txt';
$valid = false;

if (file_exists($file) && filesize($file) > 0) {
    $handle = fopen($file, 'r');
    $contents = fread($handle, filesize($file));
    fclose($handle);

    $lines = explode("\n", $contents);
    foreach ($lines as $line) {
        if (trim($line) === '') continue;

        $parts = explode('|', $line);

        // Cek apakah kolom username dan password cocok
        if (isset($parts[2]) && isset($parts[3]) &&
            $username === trim($parts[2]) && $password === trim($parts[3])) {

            $_SESSION['username'] = $username;
            $_SESSION['id'] = $parts[0];
            $valid = true;
            break;
        }
    }
}

if ($valid) {
    echo "<script>
        alert('Login berhasil! Selamat datang, $username');
        window.location.href = 'index.php';
    </script>";
    exit;
} else {
    echo "<script>
        alert('Username atau password salah!');
        window.location.href = 'login.php';
    </script>";
    exit;
}
