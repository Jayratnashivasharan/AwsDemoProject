<?php
/**
 * submit.php - Processes the feedback form POST
 */
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /feedback/');
    exit;
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$message = trim($_POST['message'] ?? '');
$rating  = (int) ($_POST['rating'] ?? 5);
$ip      = $_SERVER['REMOTE_ADDR'] ?? null;

$errors = [];
if ($name === '' || mb_strlen($name) < 2)                        $errors[] = 'Name must be at least 2 characters.';
if ($name !== '' && mb_strlen($name) > 120)                      $errors[] = 'Name must be 120 characters or fewer.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
if ($message === '' || mb_strlen($message) < 10)                 $errors[] = 'Message must be at least 10 characters.';
if (mb_strlen($message) > 2000)                                  $errors[] = 'Message must be 2000 characters or fewer.';
if ($rating < 1 || $rating > 5)                                  $rating = 5;

if (!empty($errors)) {
    header('Location: /feedback/?status=error');
    exit;
}

// Duplicate check (same email within 60 minutes)
$check = $conn->prepare(
    "SELECT id FROM feedback WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 60 MINUTE) LIMIT 1"
);
$check->bind_param('s', $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close();
    header('Location: /feedback/?status=dup');
    exit;
}
$check->close();

$stmt = $conn->prepare(
    "INSERT INTO feedback (name, email, message, rating, ip_address) VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param('sssis', $name, $email, $message, $rating, $ip);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: /feedback/?status=ok');
} else {
    $stmt->close();
    header('Location: /feedback/?status=error');
}
exit;
