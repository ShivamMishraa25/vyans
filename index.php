<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$pageTitle = 'होम';
include __DIR__ . '/header.php';

// Latest 5
$stmtLatest = $mysqli->prepare('SELECT id, title_hi, slug, content_hi, cover_image_path, created_at FROM posts ORDER BY created_at DESC LIMIT 5');
$stmtLatest->execute();
$resLatest = $stmtLatest->get_result();
$latest = $resLatest->fetch_all(MYSQLI_ASSOC);
$stmtLatest->close();

// Top 3
$stmtTop = $mysqli->prepare('SELECT id, title_hi, slug, cover_image_path FROM posts WHERE is_top_article=1 ORDER BY created_at DESC LIMIT 3');
$stmtTop->execute();
$resTop = $stmtTop->get_result();
$top = $resTop->fetch_all(MYSQLI_ASSOC);
$stmtTop->close();
?>
<section class="max-w-6xl mx-auto px-4 py-8">
	<h2 class="text-2xl font-bold mb-4">शीर्ष लेख</h2>
	<div class="grid md:grid-cols-3 gap-6">
		<?php foreach ($top as $p): ?>
			<a href="<?= e(BASE_URL . '/article.php?slug=' . urlencode($p['slug'])) ?>" class="block bg-white rounded shadow hover:shadow-lg transition overflow-hidden">
				<?php if (!empty($p['cover_image_path'])): ?>
					<img src="<?= e(img_src($p['cover_image_path'])) ?>" alt="<?= e($p['title_hi']) ?>" class="w-full h-40 object-cover">
				<?php else: ?>
					<div class="w-full h-40 bg-gray-200"></div>
				<?php endif; ?>
				<div class="p-4">
					<h3 class="font-semibold"><?= e($p['title_hi']) ?></h3>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<section class="max-w-6xl mx-auto px-4 py-4">
	<h2 class="text-2xl font-bold mb-4">नवीनतम लेख</h2>
	<div class="grid md:grid-cols-2 gap-6">
		<?php foreach ($latest as $p): ?>
			<a href="<?= e(BASE_URL . '/article.php?slug=' . urlencode($p['slug'])) ?>" class="block bg-white rounded shadow hover:shadow-lg transition overflow-hidden">
				<?php if (!empty($p['cover_image_path'])): ?>
					<img src="<?= e(img_src($p['cover_image_path'])) ?>" alt="<?= e($p['title_hi']) ?>" class="w-full h-48 object-cover">
				<?php else: ?>
					<div class="w-full h-48 bg-gray-200"></div>
				<?php endif; ?>
				<div class="p-4">
					<h3 class="font-semibold text-lg mb-2"><?= e($p['title_hi']) ?></h3>
					<p class="text-sm text-gray-600"><?= e(excerpt($p['content_hi'], 250)) ?></p>
					<p class="text-xs text-gray-400 mt-2"><?= date('d M Y', strtotime($p['created_at'])) ?></p>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
