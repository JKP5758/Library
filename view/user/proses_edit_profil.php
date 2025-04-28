<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$dataFile = 'data/users.txt';
$tempFile = 'data/users_temp.txt';

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

// Baca file users.txt dan update data
if (file_exists($dataFile)) {
    $lines = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated = false;
    $newLines = [];
    
    foreach ($lines as $line) {
        list($id, $oldNama, $oldUser, $oldPass, $oldEmail, $oldTelp, $oldAlamat) = explode('|', $line);
        
        if (trim($oldUser) === $username) {
            // Gunakan password baru jika diisi, jika tidak gunakan password lama
            $newPass = !empty($password) ? $password : $oldPass;
            
            // Buat baris baru dengan data yang diperbarui
            $newLine = "$id|$nama|$oldUser|$newPass|$email|$telepon|$alamat";
            $newLines[] = $newLine;
            $updated = true;
        } else {
            $newLines[] = $line;
        }
    }
    
    if ($updated) {
        // Tulis ke file temporary
        file_put_contents($tempFile, implode("\n", $newLines) . "\n");
        
        // Ganti file asli dengan file temporary
        rename($tempFile, $dataFile);
        
        $_SESSION['success'] = "Profil berhasil diperbarui.";
        header("Location: profil.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal memperbarui profil. Pengguna tidak ditemukan.";
        header("Location: edit_profil.php");
        exit();
    }
} else {
    $_SESSION['error'] = "File data pengguna tidak ditemukan.";
    header("Location: edit_profil.php");
    exit();
}
?> 