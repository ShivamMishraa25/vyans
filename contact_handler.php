<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ' . BASE_URL . '/index.php');
	exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
	$to = $_POST['redirect_to'] ?? (BASE_URL . '/index.php');
	header('Location: ' . $to . '?contact_success=0');
	exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

$valid = $name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $message !== '';

$to = $_POST['redirect_to'] ?? (BASE_URL . '/index.php');

if (!$valid) {
	header('Location: ' . $to . '?contact_success=0');
	exit;
}

// Optionally log to file
$logDir = __DIR__ . '/storage';
if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
$line = sprintf("[%s] %s | %s | %s\n%s\n----\n", date('Y-m-d H:i:s'), $name, $email, $phone, $message);
@file_put_contents($logDir . '/contacts.log', $line, FILE_APPEND | LOCK_EX);

header('Location: ' . $to . '?contact_success=1');
exit;
