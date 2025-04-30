<?php
session_start();
include '../../includes/db.php';

// Set timezone ke WIB (GMT +7)
date_default_timezone_set('Asia/Jakarta');

$idUser = $_SESSION['id_user'] ?? null;
$chat = $_POST['chat'] ?? '';
$mediaName = '';

if (!$idUser || !$chat) {
    header("Location: community.php");
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = '../../assets/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!empty($_FILES['media']['name'])) {
    $ext = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm'];

    if (in_array(strtolower($ext), $allowed)) {
        $mediaName = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['media']['tmp_name'], $uploadDir . $mediaName);
    }
}

// Insert post into database (now using id_user)
$query = "INSERT INTO community_posts (id_user, chat, media, waktu) VALUES (?, ?, ?, NOW())";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iss", $idUser, $chat, $mediaName);
mysqli_stmt_execute($stmt);

// Close database connection
mysqli_close($conn);

header("Location: ../member");
exit;
