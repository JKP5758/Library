<?php
session_start();

// Set timezone ke WIB (GMT +7)
date_default_timezone_set('Asia/Jakarta');

$nickname = $_SESSION['username'] ?? null;
$chat = $_POST['chat'] ?? '';
$mediaName = '';

if (!$nickname || !$chat) {
    header("Location: community.php");
    exit;
}

if (!is_dir('data/uploads')) {
    mkdir('data/uploads', 0777, true);
}

if (!empty($_FILES['media']['name'])) {
    $ext = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm'];

    if (in_array(strtolower($ext), $allowed)) {
        $mediaName = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['media']['tmp_name'], 'data/uploads/' . $mediaName);
    }
}

// encode chat agar newline tidak rusak format
$chatEncoded = base64_encode($chat);

// Dapatkan timestamp saat ini dalam format WIB
$timestamp = date('Y-m-d H:i:s');

$line = "$nickname|$chatEncoded|$mediaName|$timestamp\n";
file_put_contents('data/community.txt', $line, FILE_APPEND);

header("Location: community.php");
exit;
