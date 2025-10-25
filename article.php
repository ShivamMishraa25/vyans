<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
	header('Location: ' . BASE_URL . '/articles.php');
	exit;
}

$stmt = $mysqli->prepare('SELECT id, title_hi, content_hi, category, cover_image_path, tags, created_at FROM posts WHERE slug = ? LIMIT 1');
$stmt->bind_param('s', $slug);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
	http_response_code(404);
	$pageTitle = 'लेख नहीं मिला';
	include __DIR__ . '/header.php';
	echo '<section class="max-w-3xl mx-auto px-4 py-12"><h1 class="text-2xl font-bold">लेख नहीं मिला</h1></section>';
	include __DIR__ . '/footer.php';
	exit;
}

$pageTitle = $post['title_hi'];
$metaKeywords = $post['tags'] ?? '';

// Use shared header (removes duplicate HTML/head)
include __DIR__ . '/header.php';
?>
<section class="max-w-3xl mx-auto px-4 py-8">
	<article class="bg-white rounded shadow overflow-hidden ring-1 ring-blue-100">
		<?php if (!empty($post['cover_image_path'])): ?>
			<img src="<?= e(img_src($post['cover_image_path'])) ?>" alt="<?= e($post['title_hi']) ?>" class="w-full h-72 object-cover">
		<?php endif; ?>
		<div class="p-6">
			<div class="flex items-center gap-3 text-sm text-gray-600">
				<span class="badge badge-accent"><?= e($post['category']) ?></span>
				<time><?= date('d M Y', strtotime($post['created_at'])) ?></time>
			</div>
			<h1 class="text-3xl font-bold mt-2 text-blue-800"><?= e($post['title_hi']) ?></h1>
			<div class="prose max-w-none prose-indigo mt-4">
				<p><?= nl2br(e($post['content_hi'])) ?></p>
			</div>

			<?php if (!empty($post['tags'])): ?>
				<div class="mt-4 flex flex-wrap gap-2">
					<?php foreach (explode(',', $post['tags']) as $tag): $t = trim($tag); if (!$t) continue; ?>
						<span class="chip">#<?= e($t) ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="mt-6">
				<h3 class="font-semibold mb-2 text-fuchsia-800">शेयर करें</h3>
				<?php $u = urlencode(current_url()); $t = urlencode($post['title_hi']); ?>
				<div class="flex gap-3">
					<a class="px-3 py-1 rounded text-white bg-blue-600 hover:bg-blue-700" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= $u ?>">फेसबुक</a>
					<a class="px-3 py-1 rounded text-white bg-sky-500 hover:bg-sky-600" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?= $u ?>&text=<?= $t ?>">ट्विटर</a>
					<a class="px-3 py-1 rounded text-white bg-green-600 hover:bg-green-700" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text=<?= $t ?>%20<?= $u ?>">व्हाट्सएप</a>
				</div>
			</div>
		</div>
	</article>

	<?php
	// Related articles via post_relations
	$stmtR = $mysqli->prepare('
		SELECT p.id, p.title_hi, p.slug, p.cover_image_path
		FROM post_relations pr
		JOIN posts p ON p.id = pr.related_post_id
		WHERE pr.post_id = ?
		LIMIT 5
	');
	$stmtR->bind_param('i', $post['id']);
	$stmtR->execute();
	$related = $stmtR->get_result()->fetch_all(MYSQLI_ASSOC);
	$stmtR->close();

	if (!$related) {
		// fallback: latest others
		$stmtF = $mysqli->prepare('SELECT id, title_hi, slug, cover_image_path FROM posts WHERE id<>? ORDER BY created_at DESC LIMIT 3');
		$stmtF->bind_param('i', $post['id']);
		$stmtF->execute();
		$related = $stmtF->get_result()->fetch_all(MYSQLI_ASSOC);
		$stmtF->close();
	}
	?>
	<?php if ($related): ?>
		<div class="mt-8">
			<h3 class="text-xl font-bold mb-3 text-blue-800">संबंधित लेख</h3>
			<div class="grid md:grid-cols-3 gap-6">
				<?php foreach ($related as $r): ?>
					<a href="<?= e(BASE_URL . '/article.php?slug=' . urlencode($r['slug'])) ?>" class="block bg-white rounded shadow hover:shadow-lg transition overflow-hidden ring-1 ring-teal-100 hover:ring-amber-300 hover:-translate-y-0.5 duration-200">
						<?php if (!empty($r['cover_image_path'])): ?>
							<img src="<?= e($r['cover_image_path']) ?>" alt="<?= e($r['title_hi']) ?>" class="w-full h-36 object-cover">
						<?php else: ?>
							<div class="w-full h-36 bg-gradient-to-br from-fuchsia-50 to-blue-50"></div>
						<?php endif; ?>
						<div class="p-3">
							<h4 class="font-semibold text-sm"><?= e($r['title_hi']) ?></h4>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
<?php include __DIR__ . '/footer.php'; ?>
