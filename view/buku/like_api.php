<?php
session_start();
include '../../includes/db.php';
header('Content-Type: application/json');

$id_buku = $_GET['id_buku'] ?? $_POST['id_buku'] ?? null;
$response = ["success" => false];

if (!$id_buku) {
    echo json_encode(["success" => false, "message" => "ID buku tidak ditemukan."]);
    exit;
}

// Ambil jumlah like dan status like user
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $countQuery = "SELECT COUNT(*) as total FROM like_buku WHERE id_buku = ?";
    $stmt = mysqli_prepare($conn, $countQuery);
    mysqli_stmt_bind_param($stmt, "s", $id_buku);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $total = $row['total'] ?? 0;

    $liked = false;
    if (isset($_SESSION['id_user'])) {
        $userQuery = "SELECT 1 FROM like_buku WHERE id_buku = ? AND id_user = ? LIMIT 1";
        $stmt2 = mysqli_prepare($conn, $userQuery);
        mysqli_stmt_bind_param($stmt2, "ss", $id_buku, $_SESSION['id_user']);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        $liked = mysqli_num_rows($result2) > 0;
    }
    echo json_encode(["success" => true, "total" => (int)$total, "liked" => $liked]);
    exit;
}

// Toggle like
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['id_user'])) {
        echo json_encode(["success" => false, "message" => "Belum login"]);
        exit;
    }
    $id_user = $_SESSION['id_user'];
    // Cek apakah sudah like
    $checkQuery = "SELECT id_like FROM like_buku WHERE id_buku = ? AND id_user = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "ss", $id_buku, $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        // Sudah like, hapus
        $deleteQuery = "DELETE FROM like_buku WHERE id_buku = ? AND id_user = ?";
        $stmt2 = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($stmt2, "ss", $id_buku, $id_user);
        mysqli_stmt_execute($stmt2);
        $liked = false;
    } else {
        // Belum like, tambah
        $insertQuery = "INSERT INTO like_buku (id_user, id_buku) VALUES (?, ?)";
        $stmt2 = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt2, "ss", $id_user, $id_buku);
        mysqli_stmt_execute($stmt2);
        $liked = true;
    }
    // Ambil jumlah terbaru
    $countQuery = "SELECT COUNT(*) as total FROM like_buku WHERE id_buku = ?";
    $stmt3 = mysqli_prepare($conn, $countQuery);
    mysqli_stmt_bind_param($stmt3, "s", $id_buku);
    mysqli_stmt_execute($stmt3);
    $result3 = mysqli_stmt_get_result($stmt3);
    $row3 = mysqli_fetch_assoc($result3);
    $total = $row3['total'] ?? 0;
    echo json_encode(["success" => true, "total" => (int)$total, "liked" => $liked]);
    exit;
}

echo json_encode(["success" => false, "message" => "Metode tidak didukung"]);
