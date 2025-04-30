<?php
include '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $harga = str_replace('.', '', $_POST['harga']); // Remove thousand separators
    $deskripsi = $_POST['deskripsi'];

    // Process uploaded images
    $gambar01 = '';
    $gambar02 = '';
    $gambar03 = '';

    if (isset($_FILES['gambar01']) && $_FILES['gambar01']['error'] === UPLOAD_ERR_OK) {
        $gambar01 = uploadImage($_FILES['gambar01']);
    }
    if (isset($_FILES['gambar02']) && $_FILES['gambar02']['error'] === UPLOAD_ERR_OK) {
        $gambar02 = uploadImage($_FILES['gambar02']);
    }
    if (isset($_FILES['gambar03']) && $_FILES['gambar03']['error'] === UPLOAD_ERR_OK) {
        $gambar03 = uploadImage($_FILES['gambar03']);
    }

    // Insert book into database
    $query = "INSERT INTO books (judul, harga, gambar01, gambar02, gambar03, deskripsi) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sdssss", $judul, $harga, $gambar01, $gambar02, $gambar03, $deskripsi);
    mysqli_stmt_execute($stmt);

    // Close database connection
    mysqli_close($conn);

    header("Location: index.php");
    exit;
}

function uploadImage($file) {
    $targetDir = "../../assets/images/cover";
    $date = date('d-m-y');
    $unique = rand(100, 999);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = "{$date}-{$unique}.{$ext}";
    $targetPath = $targetDir . $fileName;
    move_uploaded_file($file['tmp_name'], $targetPath);
    return $fileName;
}
?> 