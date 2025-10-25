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

// Build absolute image URL for og:image
$ogImage = $post['cover_image_path'] ?? '';
if ($ogImage) {
	if (!preg_match('~^https?://~i', $ogImage)) {
		$ogImage = rtrim(BASE_URL, '/') . '/' . ltrim($ogImage, '/');
	}
}
// Provide OG context to header
$og = [
	'title' => $post['title_hi'],
	'image' => $ogImage,
	'url'   => current_url(),
	'twitter_card' => 'summary_large_image',
];

// Use shared header
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
				<?= embed_links($post['content_hi']) ?>
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
				<?php $shareUrl = urlencode(current_url()); $shareText = urlencode($post['title_hi']); ?>
				<div class="flex items-center gap-2">
					<!-- Facebook -->
					<a class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white ring-1 ring-blue-200 text-blue-700 hover:bg-blue-50"
					   href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="Facebook पर साझा करें">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06C2 17.06 5.66 21.2 10.44 22v-7.03H7.9v-2.9h2.54v-2.2c0-2.5 1.5-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.62.77-1.62 1.56v1.86h2.77l-.44 2.9h-2.33V22C18.34 21.2 22 17.06 22 12.06z"/></svg>
					</a>
					<!-- X (Twitter) -->
					<a class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white ring-1 ring-gray-300 text-gray-900 hover:bg-gray-50"
					   href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="X (Twitter) पर साझा करें">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 3h3.2l5.1 7.1L16.8 3H21l-7.6 9.9L21.7 21h-3.2l-5.6-7.6L7 21H2.7l7.9-10.2L3 3z"/></svg>
					</a>
					<!-- WhatsApp (wa.me format) -->
					<a class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white ring-1 ring-green-200 text-green-600 hover:bg-green-50"
					   href="https://wa.me/?text=<?= $shareText ?>%20<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="व्हाट्सएप पर साझा करें">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 3.9A10 10 0 0 0 4.6 18.8L3 22l3.3-1.6A10 10 0 1 0 20 3.9Zm-8 16.2c-1.7 0-3.3-.5-4.6-1.5l-.3-.2-2 .9.8-2.1-.2-.3A8 8 0 1 1 12 20.1Zm4.6-5.5c-.3-.2-1.7-.8-1.9-.9s-.4-.1-.5.1l-.7.9c-.1.1-.2.2-.4.1-.2-.1-.9-.3-1.7-1-.6-.5-1-1.2-1.1-1.4-.1-.2 0-.3.1-.4l.3-.3c.1-.1.1-.2.2-.3s.1-.2.1-.3 0-.2 0-.3 0-.3-.2-.3l-.9-1.9c-.2-.5-.4-.4-.6-.4h-.5s-.3 0-.5.2-.6.5-.6 1.3.6 1.6.7 1.7c.1.2 1.2 2 3 2.8 1.8.8 1.8.5 2.1.5s1-.4 1.1-.7c.1-.2.1-.5.1-.6 0-.1 0-.1-.1-.2Z"/></svg>
					</a>
					<!-- Copy Link -->
					<button type="button" id="copyLinkBtn"
						class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-white ring-1 ring-gray-200 text-gray-700 hover:bg-gray-50"
						data-url="<?= e(current_url()) ?>" aria-label="लिंक कॉपी करें">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
							<path d="M16 1H8a2 2 0 0 0-2 2v3h2V3h8v6h3V3a2 2 0 0 0-2-2z"/><path d="M5 7h10a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2zm0 2v11h10V9H5z"/>
						</svg>
					</button>
				</div>
				<p id="copyLinkMsg" class="text-xs text-green-600 mt-2 hidden">लिंक कॉपी हो गया!</p>
			</div>

			<?php
			// Fetch gallery images for this post
			$stmtG = $mysqli->prepare('SELECT image_path, caption FROM post_images WHERE post_id = ? ORDER BY COALESCE(sort_order, 999999), id');
			$stmtG->bind_param('i', $post['id']);
			$stmtG->execute();
			$gallery = $stmtG->get_result()->fetch_all(MYSQLI_ASSOC);
			$stmtG->close();
			?>
			<?php if ($gallery): ?>
				<div class="mt-8">
					<h3 class="text-xl font-bold mb-3 text-blue-800">गैलरी</h3>
					<div class="grid grid-cols-2 md:grid-cols-3 gap-3">
						<?php foreach ($gallery as $gi): ?>
							<figure class="bg-white rounded overflow-hidden border">
								<a href="<?= e(img_src($gi['image_path'])) ?>" target="_blank" rel="noopener">
									<img src="<?= e(img_src($gi['image_path'])) ?>" alt="<?= e($gi['caption'] ?? $post['title_hi']) ?>" class="w-full h-40 object-cover">
								</a>
								<?php if (!empty($gi['caption'])): ?>
									<figcaption class="px-2 py-1 text-xs text-gray-600"><?= e($gi['caption']) ?></figcaption>
								<?php endif; ?>
							</figure>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

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

        <!-- buttons -->
         <section class="max-w-6xl mx-auto px-4 mt-12">
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

	<?php endif; ?>
</section>
<?php
// ...existing code before footer include...
?>
<script>
	(function(){
		var btn = document.getElementById('copyLinkBtn');
		var msg = document.getElementById('copyLinkMsg');
		if (!btn) return;
		function showMsg(){
			if (!msg) return;
			msg.classList.remove('hidden');
			clearTimeout(showMsg._t);
			showMsg._t = setTimeout(function(){ msg.classList.add('hidden'); }, 1800);
		}
		btn.addEventListener('click', function(){
			var url = btn.getAttribute('data-url') || window.location.href;
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url).then(showMsg).catch(function(){
					// Fallback
					var ta = document.createElement('textarea');
					ta.value = url;
					document.body.appendChild(ta);
					ta.select();
					try { document.execCommand('copy'); showMsg(); } catch(e){}
					document.body.removeChild(ta);
				});
			} else {
				// Legacy fallback
				var ta = document.createElement('textarea');
				ta.value = url;
				document.body.appendChild(ta);
				ta.select();
				try { document.execCommand('copy'); showMsg(); } catch(e){}
				document.body.removeChild(ta);
			}
		});
	})();
</script>
<?php include __DIR__ . '/footer.php'; ?>
