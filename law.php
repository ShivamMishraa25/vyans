<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$pageTitle = 'कानून और न्याय';
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// total
$stmtC = $mysqli->prepare('SELECT COUNT(*) AS c FROM posts WHERE isLaw = 1');
$stmtC->execute();
$total = (int)$stmtC->get_result()->fetch_assoc()['c'];
$stmtC->close();

$totalPages = max(1, (int)ceil($total / $perPage));

// page data
$stmt = $mysqli->prepare('
	SELECT id, title_hi, slug, content_hi, category, cover_image_path, created_at
	FROM posts
	WHERE isNews = 1
	ORDER BY created_at DESC
	LIMIT ? OFFSET ?
');
$stmt->bind_param('ii', $perPage, $offset);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/header.php';
?>
<section class="max-w-6xl mx-auto px-4 py-8">
	<h1 class="text-2xl font-bold mb-4 text-blue-800">जीवनी लेख</h1>
	<?php if ($rows): ?>
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
	<?php else: ?>
		<p class="text-sm text-gray-600">कोई जीवनी लेख उपलब्ध नहीं है।</p>
	<?php endif; ?>

	<!-- Pagination -->
	<div class="flex items-center justify-center gap-2 mt-6">
		<?php if ($page > 1): ?>
			<a class="px-3 py-1 bg-white border rounded hover:bg-blue-50" href="?page=<?= $page-1 ?>">पिछला</a>
		<?php endif; ?>
		<span class="text-sm">पेज <?= $page ?> / <?= $totalPages ?></span>
		<?php if ($page < $totalPages): ?>
			<a class="px-3 py-1 bg-white border rounded hover:bg-fuchsia-50" href="?page=<?= $page+1 ?>">अगला</a>
		<?php endif; ?>
	</div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
