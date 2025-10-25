<?php
function e(string $s): string {
	return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function excerpt(string $text, int $length = 250): string {
	if (function_exists('mb_substr')) {
		$t = trim($text);
		return mb_strlen($t) > $length ? mb_substr($t, 0, $length) . '…' : $t;
	}
	$t = trim($text);
	return strlen($t) > $length ? substr($t, 0, $length) . '…' : $t;
}

function current_url(): string {
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
	$uri = $_SERVER['REQUEST_URI'] ?? '/';
	return "$scheme://$host$uri";
}

function is_admin(): bool {
	return !empty($_SESSION['admin_id']);
}

function require_admin(): void {
	if (!is_admin()) {
		header('Location: ' . BASE_URL . '/admin_login.php');
		exit;
	}
}

function safe_filename(string $name): string {
	$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
	$allowed = ['jpg','jpeg','png','gif','webp'];
	if (!in_array($ext, $allowed, true)) {
		$ext = 'jpg';
	}
	return 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
}

function ensure_upload_dir(string $path): void {
	if (!is_dir($path)) {
		@mkdir($path, 0775, true);
	}
}

function generate_slug(string $title): string {
	// Try to make a readable slug; fallback to unique id
	$t = trim($title);
	$t = preg_replace('~[^\pL\pN]+~u', '-', $t);
	$t = trim($t, '-');
	if (function_exists('iconv')) {
		$t = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
	}
	$t = strtolower(preg_replace('~[^-\w]+~', '', $t));
	if (!$t) {
		$t = 'post-' . time() . '-' . substr(md5($title), 0, 6);
	}
	return $t;
}

function ensure_unique_slug(mysqli $db, string $slug, ?int $ignoreId = null): string {
	$base = $slug;
	$i = 0;
	while (true) {
		$check = $base . ($i ? "-$i" : '');
		if ($ignoreId) {
			$stmt = $db->prepare('SELECT id FROM posts WHERE slug=? AND id<>? LIMIT 1');
			$stmt->bind_param('si', $check, $ignoreId);
		} else {
			$stmt = $db->prepare('SELECT id FROM posts WHERE slug=? LIMIT 1');
			$stmt->bind_param('s', $check);
		}
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows === 0) {
			$stmt->close();
			return $check;
		}
		$stmt->close();
		$i++;
	}
}

function flash(string $key, ?string $val = null): ?string {
	if ($val !== null) {
		$_SESSION['_flash'][$key] = $val;
		return null;
	}
	if (!empty($_SESSION['_flash'][$key])) {
		$v = $_SESSION['_flash'][$key];
		unset($_SESSION['_flash'][$key]);
		return $v;
	}
	return null;
}

// Add: normalize image src to avoid leading "/" breaking relative paths
function img_src(string $p): string {
	$p = trim($p);
	if ($p === '') return '';
	if (preg_match('~^(https?:)?//|^data:~i', $p)) return $p;
	return ($p[0] === '/') ? '.' . $p : $p;
}
