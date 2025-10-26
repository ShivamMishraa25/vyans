<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$pageTitle = 'होम';
include __DIR__ . '/header.php';

// Fetch hero (single row)
$hero = null;
if ($resH = $mysqli->query('SELECT image_path, intro_text FROM hero WHERE id=1 LIMIT 1')) {
	$hero = $resH->fetch_assoc();
	$resH->close();
}

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

// Featured 4 per category (News, Biography, Law)
$news4 = $bio4 = $law4 = [];

$stmtN = $mysqli->prepare('SELECT id, title_hi, slug, cover_image_path, created_at FROM posts WHERE isNews=1 ORDER BY created_at DESC LIMIT 4');
$stmtN->execute();
$news4 = $stmtN->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtN->close();

$stmtB = $mysqli->prepare('SELECT id, title_hi, slug, cover_image_path, created_at FROM posts WHERE isBiography=1 ORDER BY created_at DESC LIMIT 4');
$stmtB->execute();
$bio4 = $stmtB->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtB->close();

$stmtL = $mysqli->prepare('SELECT id, title_hi, slug, cover_image_path, created_at FROM posts WHERE isLaw=1 ORDER BY created_at DESC LIMIT 4');
$stmtL->execute();
$law4 = $stmtL->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtL->close();
?>

<?php if ($hero): ?>
<section class="max-w-6xl mx-auto px-0 pt-0 md:px-4 md:pt-6">
	<div class="relative rounded-none md:rounded-xl overflow-hidden shadow ring-1 ring-blue-100">
		<?php if (!empty($hero['image_path'])): ?>
			<img src="<?= e(img_src($hero['image_path'])) ?>" alt="हीरो इमेज" class="w-full h-64 md:h-80 object-cover">
		<?php else: ?>
			<div class="w-full h-64 md:h-80 bg-gradient-to-br from-blue-50 via-fuchsia-50 to-amber-50"></div>
		<?php endif; ?>
		<div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/20 to-transparent"></div>
		<div class="absolute inset-0 flex items-end">
			<div class="p-4 md:p-6">
				<p class="text-white text-lg md:text-xl drop-shadow">
					<?= nl2br(e($hero['intro_text'] ?? '')) ?>
				</p>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="max-w-6xl mx-auto px-4 py-4">
	<h2 class="text-2xl font-bold mb-4 text-blue-800">शीर्ष लेख</h2>
	<div class="grid md:grid-cols-3 gap-6">
		<?php foreach ($top as $p): ?>
			<a href="<?= e(BASE_URL . '/article.php?slug=' . urlencode($p['slug'])) ?>" class="block bg-white rounded shadow hover:shadow-lg transition overflow-hidden ring-1 ring-blue-100 hover:ring-fuchsia-300 hover:-translate-y-0.5 duration-200">
				<?php if (!empty($p['cover_image_path'])): ?>
					<img src="<?= e(img_src($p['cover_image_path'])) ?>" alt="<?= e($p['title_hi']) ?>" class="w-full h-40 object-cover">
				<?php else: ?>
					<div class="w-full h-40 bg-gradient-to-br from-blue-50 to-fuchsia-50"></div>
				<?php endif; ?>
				<div class="p-4">
					<h3 class="font-semibold"><?= e($p['title_hi']) ?></h3>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<section class="max-w-6xl mx-auto px-4 py-3">
	<div class="flex flex-wrap justify-center gap-2 md:gap-3">
		<a href="<?= e(BASE_URL) ?>/articles.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-blue-600 hover:bg-blue-700 shadow">
			सभी लेख
		</a>
		<a href="<?= e(BASE_URL) ?>/biography.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-indigo-600 hover:bg-indigo-700 shadow">
			जीवनी लेख
		</a>
		<a href="<?= e(BASE_URL) ?>/law.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-teal-600 hover:bg-teal-700 shadow">
			कानून और न्याय
		</a>
		<a href="<?= e(BASE_URL) ?>/news.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-fuchsia-600 hover:bg-fuchsia-700 shadow">
			ताज़ा समाचार
		</a>
		<a href="<?= e(BASE_URL) ?>/about.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-amber-600 hover:bg-amber-700 shadow">
			हमारे बारे में
		</a>
		<a href="<?= e(BASE_URL) ?>/contact.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-gray-800 hover:bg-gray-900 shadow">
			हमसे संपर्क करें
		</a>
	</div>
</section>

<section class="max-w-6xl mx-auto px-4 py-4">
	<h2 class="text-2xl font-bold mb-4 text-fuchsia-800">नवीनतम लेख</h2>
	<div class="grid md:grid-cols-2 gap-6">
		<?php foreach ($latest as $p): ?>
			<a href="<?= e(BASE_URL . '/article.php?slug=' . urlencode($p['slug'])) ?>" class="block bg-white rounded shadow hover:shadow-lg transition overflow-hidden ring-1 ring-teal-100 hover:ring-amber-300 hover:-translate-y-0.5 duration-200">
				<?php if (!empty($p['cover_image_path'])): ?>
					<img src="<?= e(img_src($p['cover_image_path'])) ?>" alt="<?= e($p['title_hi']) ?>" class="w-full h-48 object-cover">
				<?php else: ?>
					<div class="w-full h-48 bg-gradient-to-br from-amber-50 to-blue-50"></div>
				<?php endif; ?>
				<div class="p-4">
					<h3 class="font-semibold text-lg mb-2"><?= e($p['title_hi']) ?></h3>
					<p class="text-sm text-gray-700"><?= e(excerpt($p['content_hi'], 250)) ?></p>
					<p class="text-xs text-gray-500 mt-2"><?= date('d M Y', strtotime($p['created_at'])) ?></p>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<section class="max-w-6xl mx-auto px-4 py-3">
	<div class="flex flex-wrap justify-center gap-2 md:gap-3">
		<a href="<?= e(BASE_URL) ?>/articles.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-blue-600 hover:bg-blue-700 shadow">
			सभी लेख
		</a>
		<a href="<?= e(BASE_URL) ?>/biography.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-indigo-600 hover:bg-indigo-700 shadow">
			जीवनी लेख
		</a>
		<a href="<?= e(BASE_URL) ?>/law.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-teal-600 hover:bg-teal-700 shadow">
			कानून और न्याय
		</a>
		<a href="<?= e(BASE_URL) ?>/news.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-fuchsia-600 hover:bg-fuchsia-700 shadow">
			ताज़ा समाचार
		</a>
		<a href="<?= e(BASE_URL) ?>/about.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-amber-600 hover:bg-amber-700 shadow">
			हमारे बारे में
		</a>
		<a href="<?= e(BASE_URL) ?>/contact.php"
		   class="inline-flex items-center px-3 py-2 rounded text-white bg-gray-800 hover:bg-gray-900 shadow">
			हमसे संपर्क करें
		</a>
	</div>
</section>

<!-- New: Featured 2x2 grids (News, Biography, Law) -->
<section class="max-w-6xl mx-auto px-4 py-6">
	<?php if ($news4): ?>
		<h2 class="text-xl font-bold mb-3 text-blue-800">ताज़ा समाचार</h2>
		<div class="grid grid-cols-2 gap-3">
			<?php foreach ($news4 as $p): ?>
				<a href="<?= e(BASE_URL . '/article.php?slug=' . urlencode($p['slug'])) ?>" class="block bg-white rounded shadow overflow-hidden ring-1 ring-blue-100 hover:shadow-lg hover:-translate-y-0.5 transition">
					<?php if (!empty($p['cover_image_path'])): ?>
						<img src="<?= e(img_src($p['cover_image_path'])) ?>" alt="<?= e($p['title_hi']) ?>" class="w-full h-20 md:h-36 object-cover">
					<?php else: ?>
						<div class="w-full h-36 bg-gradient-to-br from-blue-50 to-fuchsia-50"></div>
					<?php endif; ?>
					<div class="p-2 md:p-3">
						<h3 class="font-semibold text-sm"><?= e($p['title_hi']) ?></h3>
						<p class="text-xs text-gray-500 mt-1"><?= date('d M Y', strtotime($p['created_at'])) ?></p>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ($bio4): ?>
		<h2 class="text-xl font-bold mt-6 mb-3 text-indigo-700">जीवनी लेख</h2>
		<div class="grid grid-cols-2 gap-3">
			<?php foreach ($bio4 as $p): ?>
				<a href="<?= e(BASE_URL . '/article.php?slug=' . urlencode($p['slug'])) ?>" class="block bg-white rounded shadow overflow-hidden ring-1 ring-blue-100 hover:shadow-lg hover:-translate-y-0.5 transition">
					<?php if (!empty($p['cover_image_path'])): ?>
						<img src="<?= e(img_src($p['cover_image_path'])) ?>" alt="<?= e($p['title_hi']) ?>" class="w-full h-20 md:h-36 object-cover">
					<?php else: ?>
						<div class="w-full h-36 bg-gradient-to-br from-amber-50 to-blue-50"></div>
					<?php endif; ?>
					<div class="p-2 md:p-3">
						<h3 class="font-semibold text-sm"><?= e($p['title_hi']) ?></h3>
						<p class="text-xs text-gray-500 mt-1"><?= date('d M Y', strtotime($p['created_at'])) ?></p>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ($law4): ?>
		<h2 class="text-xl font-bold mt-6 mb-3 text-teal-700">कानून और न्याय</h2>
		<div class="grid grid-cols-2 gap-3">
			<?php foreach ($law4 as $p): ?>
				<a href="<?= e(BASE_URL . '/article.php?slug=' . urlencode($p['slug'])) ?>" class="block bg-white rounded shadow overflow-hidden ring-1 ring-blue-100 hover:shadow-lg hover:-translate-y-0.5 transition">
					<?php if (!empty($p['cover_image_path'])): ?>
						<img src="<?= e(img_src($p['cover_image_path'])) ?>" alt="<?= e($p['title_hi']) ?>" class="w-full h-20 md:h-36 object-cover">
					<?php else: ?>
						<div class="w-full h-36 bg-gradient-to-br from-fuchsia-50 to-blue-50"></div>
					<?php endif; ?>
					<div class="p-2 md:p-3">
						<h3 class="font-semibold text-sm"><?= e($p['title_hi']) ?></h3>
						<p class="text-xs text-gray-500 mt-1"><?= date('d M Y', strtotime($p['created_at'])) ?></p>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>

<section class="max-w-6xl mx-auto px-4 py-4">
	<div class="flex justify-center">
		<a href="<?= e(BASE_URL) ?>/articles.php"
		   class="px-5 py-3 rounded text-white bg-indigo-600 hover:bg-indigo-700 shadow">
			सारे आर्टिकल पढ़ें
		</a>
	</div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
