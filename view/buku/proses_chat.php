<?php
session_start();

// Include database connection
include '../../includes/db.php';

if (!isset($_SESSION['username']) || !isset($_POST['id_buku']) || !isset($_POST['pesan'])) {
    echo "<script>alert('Data tidak lengkap atau belum login.'); window.history.back();</script>";
    exit;
}

$idBuku = $_POST['id_buku'];
$idParent = isset($_POST['id_parent']) && is_numeric($_POST['id_parent']) ? $_POST['id_parent'] : null;
$username = $_SESSION['username'];
$pesan = trim($_POST['pesan']);
$uploadDir = '../../data/img/';
$mediaChat = 'default.png';

// Get user ID from username
$userQuery = "SELECT id_user FROM users WHERE username = ?";
$userStmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($userStmt, "s", $username);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);

if (!$userResult || mysqli_num_rows($userResult) === 0) {
    echo "<script>alert('User tidak ditemukan.'); window.history.back();</script>";
    exit;
}

$userRow = mysqli_fetch_assoc($userResult);
$idUser = $userRow['id_user'];

// Handle image upload if provided
if (!empty($_FILES['gambar']['name'])) {
    $fileName = uniqid() . '_' . basename($_FILES['gambar']['name']);
    $targetPath = $uploadDir . $fileName;

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
        $mediaChat = $fileName;
    }
}

// Insert chat data into database (include id_parent)
$insertQuery = "INSERT INTO chats (id_user, id_buku, chat, media_chat, id_parent) VALUES (?, ?, ?, ?, ?)";
$insertStmt = mysqli_prepare($conn, $insertQuery);
mysqli_stmt_bind_param($insertStmt, "iissi", $idUser, $idBuku, $pesan, $mediaChat, $idParent);

if (mysqli_stmt_execute($insertStmt)) {
    header("Location: index.php?id=$idBuku");
} else {
    echo "<script>alert('Gagal menyimpan komentar: " . mysqli_error($conn) . "'); window.history.back();</script>";
}

// Close connection
mysqli_close($conn);
exit;
