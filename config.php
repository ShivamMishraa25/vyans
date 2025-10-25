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

// Ensure flag columns exist (auto-migrate if needed)
(function(mysqli $db){
	// Helper checks if a column exists
	$has = function(string $col) use ($db): bool {
		$res = $db->query("SHOW COLUMNS FROM posts LIKE '{$db->real_escape_string($col)}'");
		if (!$res) return false;
		$ok = $res->num_rows > 0;
		$res->close();
		return $ok;
	};
	// Add columns in order if missing
	if (!$has('isBiography')) { @$db->query("ALTER TABLE posts ADD COLUMN isBiography TINYINT(1) NOT NULL DEFAULT 0 AFTER gallery_count"); }
	if (!$has('isNews'))      { @$db->query("ALTER TABLE posts ADD COLUMN isNews TINYINT(1) NOT NULL DEFAULT 0 AFTER isBiography"); }
	if (!$has('isLaw'))       { @$db->query("ALTER TABLE posts ADD COLUMN isLaw TINYINT(1) NOT NULL DEFAULT 0 AFTER isNews"); }
})($mysqli);

// CSRF token helper
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
