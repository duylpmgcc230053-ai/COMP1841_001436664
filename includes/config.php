<?php
// includes/config.php - edit DB credentials
// Khởi động session trước mọi thứ (trừ thẻ <?php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '127.0.0.1';
$db   = 'student_qna';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Đảm bảo Prepared Statements hoạt động đúng
    PDO::ATTR_EMULATE_PREPARES   => false, 
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Thoát nếu không thể kết nối CSDL
    exit('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}
// KHÔNG CẦN THẺ ĐÓNG ?> 