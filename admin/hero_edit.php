<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';
require_admin();

$msg = '';
$err = [];

// Fetch current hero
$hero = ['image_path' => '', 'intro_text' => ''];
if ($res = $mysqli->query('SELECT image_path, intro_text FROM hero WHERE id=1 LIMIT 1')) {
	$hero = $res->fetch_assoc() ?: $hero;
	$res->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
		$err[] = 'अमान्य अनुरोध।';
	} else {
		$intro = trim($_POST['intro_text'] ?? '');
		if ($intro === '') $err[] = 'परिचय संदेश आवश्यक है।';

		$newPath = $hero['image_path'];
		if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] !== UPLOAD_ERR_NO_FILE) {
			if ($_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
				$tmp = $_FILES['hero_image']['tmp_name'];
				$mime = @mime_content_type($tmp);
				if (!in_array($mime, ['image/jpeg','image/png','image/gif','image/webp'], true)) {
					$err[] = 'कृपया वैध छवि अपलोड करें (JPG, PNG, GIF, WEBP)।';
				} else {
					$uploadDir = dirname(__DIR__) . '/uploads/hero';
					ensure_upload_dir($uploadDir);
					$fname = safe_filename($_FILES['hero_image']['name']);
					$dest = $uploadDir . '/' . $fname;
					if (!@move_uploaded_file($tmp, $dest)) {
						$err[] = 'छवि अपलोड विफल रही।';
					} else {
						// Save relative path
						$rel = 'uploads/hero/' . $fname;
						// Remove old file if existed
						if (!empty($hero['image_path'])) {
							$old = dirname(__DIR__) . '/' . ltrim($hero['image_path'], '/');
							if (is_file($old)) { @unlink($old); }
						}
						$newPath = $rel;
					}
				}
			} else {
				$err[] = 'छवि अपलोड में त्रुटि।';
			}
		}

		if (!$err) {
			// Upsert hero row with id=1
			$stmt = $mysqli->prepare('INSERT INTO hero (id, image_path, intro_text) VALUES (1, ?, ?)
				ON DUPLICATE KEY UPDATE image_path=VALUES(image_path), intro_text=VALUES(intro_text)');
			$stmt->bind_param('ss', $newPath, $intro);
			$stmt->execute();
			$stmt->close();
			$msg = 'हीरो सेक्शन अपडेट किया गया।';
			$hero['image_path'] = $newPath;
			$hero['intro_text'] = $intro;
		}
	}
}

$pageTitle = 'हीरो संपादन';
include dirname(__DIR__) . '/header.php';
?>
<section class="max-w-3xl mx-auto px-4 py-8">
	<h1 class="text-2xl font-bold mb-4 text-blue-800">हीरो सेक्शन</h1>

	<?php if ($msg): ?>
		<div class="bg-green-50 text-green-700 px-3 py-2 rounded mb-3"><?= e($msg) ?></div>
	<?php endif; ?>
	<?php if ($err): ?>
		<div class="bg-red-50 text-red-700 px-3 py-2 rounded mb-3">
			<?php foreach ($err as $e): ?><div><?= e($e) ?></div><?php endforeach; ?>
		</div>
	<?php endif; ?>

	<form method="post" enctype="multipart/form-data" class="bg-white rounded shadow p-4 space-y-4 ring-1 ring-blue-100" novalidate>
		<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

		<div>
			<label class="block text-sm mb-1 text-gray-700">परिचय पाठ</label>
			<textarea name="intro_text" rows="4" required class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500" placeholder="संक्षिप्त परिचय लिखें..."><?= e($hero['intro_text'] ?? '') ?></textarea>
			<p class="text-xs text-gray-500 mt-1">यह पाठ होमपेज के शीर्ष पर दिखेगा।</p>
		</div>

		<div>
			<label class="block text-sm mb-1 text-gray-700">हीरो छवि</label>
			<input type="file" name="hero_image" accept="image/*" class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-fuchsia-500">
			<?php if (!empty($hero['image_path'])): ?>
				<p class="text-xs mt-2">वर्तमान: <a target="_blank" class="text-blue-700 hover:underline" href="<?= e(img_src($hero['image_path'])) ?>"><?= e($hero['image_path']) ?></a></p>
				<div class="mt-2">
					<img src="../<?= e(img_src($hero['image_path'])) ?>" alt="वर्तमान हीरो" class="w-full h-48 object-cover rounded">
				</div>
			<?php endif; ?>
			<p class="text-xs text-gray-500 mt-1">अनुशंसित अनुपात: 16:9</p>
		</div>

		<div class="flex gap-3">
			<button class="btn-primary text-white px-4 py-2 rounded shadow">सहेजें</button>
			<a href="<?= e(BASE_URL) ?>/admin_dashboard.php" class="px-4 py-2 rounded border hover:bg-blue-50">डैशबोर्ड</a>
		</div>
	</form>
</section>
<?php include dirname(__DIR__) . '/footer.php'; ?>
