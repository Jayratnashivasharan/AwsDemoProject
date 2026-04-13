<?php
/**
 * db.php - Database connection (MySQLi)
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'fbuser');
define('DB_PASS', 'FbSecure@2025!');
define('DB_NAME', 'feedbackdb');
define('DB_CHARSET', 'utf8mb4');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset(DB_CHARSET);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die('Database connection failed. Please try later.');
}
