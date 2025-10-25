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

// Normalize image src (avoid leading "/" for relative paths)
function img_src(string $p): string {
	$p = trim($p);
	if ($p === '') return '';
	if (preg_match('~^(https?:)?//|^data:~i', $p)) return $p;
	return ($p[0] === '/') ? '.' . $p : $p;
}

// Auto-link plain URLs in a line while escaping other text
function autolink_line(string $line): string {
	$pattern = '~(?:(?:https?|ftp)://|www\.)[^\s<]+~i';
	$out = '';
	$last = 0;
	if (preg_match_all($pattern, $line, $matches, PREG_OFFSET_CAPTURE)) {
		foreach ($matches[0] as $m) {
			$url = $m[0];
			$pos = $m[1];
			$out .= e(substr($line, $last, $pos - $last));
			$href = preg_match('~^www\.~i', $url) ? ('http://' . $url) : $url;
			$out .= '<a href="' . e($href) . '" target="_blank" rel="noopener">' . e($url) . '</a>';
			$last = $pos + strlen($url);
		}
	}
	$out .= e(substr($line, $last));
	return $out;
}

// Client-side auto-embed placeholders + visible fallback links
function embed_links(string $content): string {
	$lines = preg_split("/\r\n|\n|\r/", (string)$content);
	$out = [];

	$yt = '~^https?://(?:www\.)?(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})(?:[^\s]*)?$~i';
	$ig = '~^https?://(?:www\.)?instagram\.com/(?:p|reel|tv)/[A-Za-z0-9_-]+/?(?:\?.*)?$~i';
	$fbAny = '~^https?://(?:www\.|m\.)?(?:facebook\.com|fb\.watch)/[^\s]+$~i';

	foreach ($lines as $line) {
		$raw = trim($line);
		if ($raw === '') {
			$out[] = '<br>';
			continue;
		}

		// YouTube
		if (preg_match($yt, $raw, $m)) {
			$vid = $m[1];
			$src = 'https://www.youtube.com/embed/' . rawurlencode($vid);
			$out[] =
				'<div class="relative" style="padding-top:56.25%">' .
					'<iframe src="' . e($src) . '" title="YouTube video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen ' .
					'style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"></iframe>' .
				'</div>' .
				'<p><a href="' . e($raw) . '" target="_blank" rel="noopener">' . e($raw) . '</a></p>';
			continue;
		}

		// Instagram
		if (preg_match($ig, $raw)) {
			$url = e($raw);
			$out[] =
				'<blockquote class="instagram-media" data-instgrm-permalink="' . $url . '" data-instgrm-version="14" style="background:#fff;border:0;border-radius:3px;box-shadow:0 0 1px rgba(0,0,0,0.5),0 1px 10px rgba(0,0,0,0.15);margin:1px;max-width:540px;min-width:326px;padding:0;width:100%;"></blockquote>' .
				'<p><a href="' . $url . '" target="_blank" rel="noopener">' . $url . '</a></p>';
			continue;
		}

		// Facebook
		if (preg_match($fbAny, $raw)) {
			$isVideo = (bool)preg_match('~/(?:video(s)?/|watch|share/v/)~i', $raw) || (bool)preg_match('~^https?://fb\.watch/~i', $raw);
			$url = e($raw);
			$out[] = $isVideo
				? '<div class="fb-video" data-href="' . $url . '" data-width="auto" data-allowfullscreen="true"></div>'
				: '<div class="fb-post" data-href="' . $url . '" data-width="auto"></div>';
			$out[] = '<p><a href="' . $url . '" target="_blank" rel="noopener">' . $url . '</a></p>';
			continue;
		}

		// Default: keep text and make URLs clickable
		$out[] = '<p>' . autolink_line($line) . '</p>';
	}

	return implode("\n", $out);
}