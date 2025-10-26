<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$q = trim($_GET['query'] ?? '');
$pageTitle = 'खोज परिणाम';
$rows = [];
$error = '';

if ($q !== '') {
	$like = '%' . $q . '%';
	$stmt = $mysqli->prepare('
		SELECT id, title_hi, slug, content_hi, category, cover_image_path, created_at
		FROM posts
		WHERE title_hi LIKE ?
		ORDER BY created_at DESC
		LIMIT 50
	');
	$stmt->bind_param('s', $like);
	$stmt->execute();
	$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	$stmt->close();
} else {
	$error = 'कृपया खोज शब्द दर्ज करें।';
}

include __DIR__ . '/header.php';
?>
<section class="max-w-6xl mx-auto px-4 py-8">
	<h1 class="text-2xl font-bold mb-4 text-blue-800">खोज परिणाम</h1>

	<form action="<?= e(BASE_URL) ?>/search_results.php" method="get" class="mb-4">
		<input type="search" name="query" value="<?= e($q) ?>" placeholder="शीर्षक से खोजें..." class="px-3 py-2 border rounded w-full md:w-1/2 focus:ring-2 focus:ring-blue-500" required>
	</form>

	<?php if ($error): ?>
		<p class="text-sm text-red-600"><?= e($error) ?></p>
	<?php elseif (!$rows): ?>
		<p class="text-sm text-gray-600">"<?= e($q) ?>" के लिए कोई परिणाम नहीं मिला।</p>
	<?php else: ?>
		<p class="text-sm text-gray-600 mb-3">"<?= e($q) ?>" के लिए <?= count($rows) ?> परिणाम:</p>
		<div class="grid md:grid-cols-2 gap-6">
			<?php foreach ($rows as $p): ?>
				<a href="<?= e(BASE_URL . '/article.php?slug=' . urlencode($p['slug'])) ?>" class="block bg-white rounded shadow hover:shadow-lg transition overflow-hidden ring-1 ring-blue-100 hover:ring-fuchsia-300 hover:-translate-y-0.5 duration-200">
					<?php if (!empty($p['cover_image_path'])): ?>
						<img src="<?= e(img_src($p['cover_image_path'])) ?>" alt="<?= e($p['title_hi']) ?>" class="w-full h-44 object-cover">
					<?php else: ?>
						<div class="w-full h-44 bg-gradient-to-br from-blue-50 to-amber-50"></div>
					<?php endif; ?>
					<div class="p-4">
						<span class="badge badge-accent"><?= e($p['category']) ?></span>
						<h3 class="font-semibold text-lg mt-2"><?= e($p['title_hi']) ?></h3>
						<p class="text-sm text-gray-700"><?= e(excerpt($p['content_hi'], 180)) ?></p>
						<p class="text-xs text-gray-500 mt-2"><?= date('d M Y', strtotime($p['created_at'])) ?></p>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
<?php include __DIR__ . '/footer.php'; ?>
