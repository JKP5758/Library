<?php
// Cek dulu apa fungsi loadEnv udah ada
if (!function_exists('loadEnv')) {
    function loadEnv($path)
    {
        if (!file_exists($path)) {
            throw new Exception('.env file not found!');
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue; // Skip komentar
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Load file .env
loadEnv(__DIR__ . '/../.env');

// Database connection parameters dari .env
$host     = $_ENV['DB_HOST'] ?? 'localhost';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';
$database = $_ENV['DB_NAME'] ?? '';
$charset  = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

// Buat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set karakter koneksi
mysqli_set_charset($conn, $charset);
?>
