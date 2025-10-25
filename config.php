<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
date_default_timezone_set('Asia/Kolkata');

// Database credentials (adjust as needed)
define('DB_HOST', 'localhost'); // sdb-h.hosting.stackcp.net
define('DB_USER', 'root'); // thevyans
define('DB_PASS', ''); // rBI0~uxU|z+}
define('DB_NAME', 'vyans'); // thevyans-313836796c

// Base URL (adjust if your local URL differs)
define('BASE_URL', 'http://localhost/vyans');

// Mysqli connection with utf8mb4
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
	http_response_code(500);
	die('डेटाबेस से कनेक्शन विफल रहा।');
}
$mysqli->set_charset('utf8mb4');

// CSRF token helper
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
