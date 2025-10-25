<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_admin();

$msg = flash('msg');

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
	if (!hash_equals($_SESSION['csrf_token'], $_GET['csrf'] ?? '')) {
		flash('msg', 'अमान्य अनुरोध।');
		header('Location: ' . BASE_URL . '/admin_dashboard.php');
		exit;
	}
	$id = (int)$_GET['id'];
	// get image path to optionally remove
	$stmtI = $mysqli->prepare('SELECT cover_image_path FROM posts WHERE id=?');
	$stmtI->bind_param('i', $id);
	$stmtI->execute();
	$img = $stmtI->get_result()->fetch_assoc()['cover_image_path'] ?? null;
	$stmtI->close();

	$stmt = $mysqli->prepare('DELETE FROM posts WHERE id=?');
	$stmt->bind_param('i', $id);
	$stmt->execute();
	$stmt->close();

	// remove image file if exists (optional)
	if ($img) {
		$path = __DIR__ . '/' . ltrim($img, '/');
		if (is_file($path)) { @unlink($path); }
	}
	flash('msg', 'लेख हटाया गया।');
	header('Location: ' . BASE_URL . '/admin_dashboard.php');
	exit;
}

// fetch posts
$res = $mysqli->query('SELECT id, slug, title_hi, category, is_top_article, created_at FROM posts ORDER BY created_at DESC');
$posts = $res->fetch_all(MYSQLI_ASSOC);
$res->close();

$pageTitle = 'डैशबोर्ड';
include __DIR__ . '/header.php';
?>
<section class="max-w-6xl mx-auto px-4 py-8">
	<div class="flex items-center justify-between mb-4">
		<h1 class="text-2xl font-bold text-blue-800">डैशबोर्ड</h1>
		<a href="<?= e(BASE_URL) ?>/admin_edit.php" class="btn-primary px-4 py-2 rounded shadow">नया लेख</a>
	</div>
	<?php if ($msg): ?><div class="bg-green-50 text-green-700 px-3 py-2 rounded mb-3"><?= e($msg) ?></div><?php endif; ?>
	<div class="overflow-auto bg-white rounded shadow ring-1 ring-blue-100">
		<table class="min-w-full text-sm">
			<thead class="bg-blue-50 text-blue-800">
				<tr>
					<th class="text-left p-2">शीर्षक</th>
					<th class="text-left p-2">श्रेणी</th>
					<th class="text-left p-2">शीर्ष</th>
					<th class="text-left p-2">तारीख</th>
					<th class="text-left p-2">कार्य</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($posts as $p): ?>
				<tr class="border-t">
					<td class="p-2"><?= e($p['title_hi']) ?></td>
					<td class="p-2"><?= e($p['category']) ?></td>
					<td class="p-2"><?= $p['is_top_article'] ? '<span class="chip">हाँ</span>' : 'नहीं' ?></td>
					<td class="p-2"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
					<td class="p-2 space-x-3">
						<a class="text-blue-700 hover:underline" href="<?= e(BASE_URL) ?>/admin_edit.php?id=<?= (int)$p['id'] ?>">संपादित</a>
						<a class="text-red-700 hover:underline" href="<?= e(BASE_URL) ?>/admin_dashboard.php?action=delete&id=<?= (int)$p['id'] ?>&csrf=<?= e($_SESSION['csrf_token']) ?>" onclick="return confirm('क्या आप वाकई हटाना चाहते हैं?');">हटाएँ</a>
						<a class="text-fuchsia-700 hover:underline" target="_blank" href="<?= e(BASE_URL) ?>/article.php?slug=<?= urlencode($p['slug']) ?>">देखें</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
