<?php
// =========================================================
// Konfigurasi koneksi database (PDO)
// Sesuaikan host, nama db, user, dan password dengan server Anda
// =========================================================

$db_host    = 'localhost';
$db_name    = 'survey_game';
$db_user    = 'root';
$db_pass    = '';
$db_charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Jangan tampilkan detail error ke pengguna di production
    http_response_code(500);
    die('Koneksi database gagal. Silakan hubungi admin.');
}
