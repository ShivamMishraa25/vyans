<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;

$title_hi = $category = $content_hi = $tags = $cover_image_path = '';
$is_top_article = 0;
$slug = '';

if ($editing) {
	$stmt = $mysqli->prepare('SELECT * FROM posts WHERE id=? LIMIT 1');
	$stmt->bind_param('i', $id);
	$stmt->execute();
	$cur = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	if (!$cur) {
		flash('msg', 'लेख नहीं मिला।');
		header('Location: ' . BASE_URL . '/admin_dashboard.php');
		exit;
	}
	$title_hi = $cur['title_hi']; $category = $cur['category']; $content_hi = $cur['content_hi'];
	$tags = $cur['tags']; $cover_image_path = $cur['cover_image_path']; $is_top_article = (int)$cur['is_top_article'];
	$slug = $cur['slug'];
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
		$errors[] = 'अमान्य अनुरोध।';
	} else {
		$title_hi = trim($_POST['title_hi'] ?? '');
		$category = trim($_POST['category'] ?? '');
		$content_hi = trim($_POST['content_hi'] ?? '');
		$tags = trim($_POST['tags'] ?? '');
		$is_top_article = isset($_POST['is_top_article']) ? 1 : 0;
		$selected_related = array_map('intval', $_POST['related_posts'] ?? []);

		if ($title_hi === '') $errors[] = 'शीर्षक आवश्यक है।';
		if ($content_hi === '') $errors[] = 'सामग्री आवश्यक है।';
		if ($category === '') $errors[] = 'श्रेणी आवश्यक है।';

		// Handle image upload
		if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
			if ($_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
				$tmp = $_FILES['cover_image']['tmp_name'];
				// Basic mime check
				$finfo = @mime_content_type($tmp);
				if (!in_array($finfo, ['image/jpeg','image/png','image/gif','image/webp'], true)) {
					$errors[] = 'कृपया वैध छवि अपलोड करें (JPG, PNG, GIF, WEBP)।';
				} else {
					$uploadDir = __DIR__ . '/uploads/images';
					ensure_upload_dir($uploadDir);
					$fname = safe_filename($_FILES['cover_image']['name']);
					$dest = $uploadDir . '/' . $fname;
					if (!@move_uploaded_file($tmp, $dest)) {
						$errors[] = 'छवि अपलोड विफल रही।';
					} else {
						$relPath = 'uploads/images/' . $fname; // was '/uploads/images/...'
						// remove old image if editing
						if ($editing && $cover_image_path) {
							$old = __DIR__ . '/' . ltrim($cover_image_path, '/');
							if (is_file($old)) { @unlink($old); }
						}
						$cover_image_path = $relPath;
					}
				}
			} else {
				$errors[] = 'छवि अपलोड में त्रुटि।';
			}
		}

		// slug
		if ($editing) {
			$slugInput = trim($_POST['slug'] ?? $slug);
			if ($slugInput === '') $slugInput = generate_slug($title_hi);
			$slug = ensure_unique_slug($mysqli, $slugInput, $id);
		} else {
			$slugInput = trim($_POST['slug'] ?? '');
			if ($slugInput === '') $slugInput = generate_slug($title_hi);
			$slug = ensure_unique_slug($mysqli, $slugInput, null);
		}

		if (!$errors) {
			if ($editing) {
				$stmt = $mysqli->prepare('UPDATE posts SET title_hi=?, slug=?, content_hi=?, category=?, cover_image_path=?, tags=?, is_top_article=? WHERE id=?');
				$stmt->bind_param('ssssssii', $title_hi, $slug, $content_hi, $category, $cover_image_path, $tags, $is_top_article, $id);
				$stmt->execute();
				$stmt->close();

				// update relations
				$stmtD = $mysqli->prepare('DELETE FROM post_relations WHERE post_id=?');
				$stmtD->bind_param('i', $id);
				$stmtD->execute();
				$stmtD->close();
				if ($selected_related) {
					$stmtR = $mysqli->prepare('INSERT INTO post_relations (post_id, related_post_id) VALUES (?, ?)');
					foreach ($selected_related as $rid) {
						if ($rid === $id) continue;
						$stmtR->bind_param('ii', $id, $rid);
						$stmtR->execute();
					}
					$stmtR->close();
				}

				flash('msg', 'लेख अपडेट किया गया।');
			} else {
				$stmt = $mysqli->prepare('INSERT INTO posts (title_hi, slug, content_hi, category, cover_image_path, tags, is_top_article) VALUES (?,?,?,?,?,?,?)');
				$stmt->bind_param('ssssssi', $title_hi, $slug, $content_hi, $category, $cover_image_path, $tags, $is_top_article);
				$stmt->execute();
				$newId = $stmt->insert_id;
				$stmt->close();

				if ($selected_related) {
					$stmtR = $mysqli->prepare('INSERT INTO post_relations (post_id, related_post_id) VALUES (?, ?)');
					foreach ($selected_related as $rid) {
						if ($rid === $newId) continue;
						$stmtR->bind_param('ii', $newId, $rid);
						$stmtR->execute();
					}
					$stmtR->close();
				}

				flash('msg', 'नया लेख जोड़ा गया।');
			}
			header('Location: ' . BASE_URL . '/admin_dashboard.php');
			exit;
		}
	}
}

// fetch all posts for related selection
$res = $mysqli->query('SELECT id, title_hi FROM posts ORDER BY created_at DESC');
$allPosts = $res->fetch_all(MYSQLI_ASSOC);
$res->close();

$relatedSelected = [];
if ($editing) {
	$stmtS = $mysqli->prepare('SELECT related_post_id FROM post_relations WHERE post_id=?');
	$stmtS->bind_param('i', $id);
	$stmtS->execute();
	$relatedSelected = array_column($stmtS->get_result()->fetch_all(MYSQLI_ASSOC), 'related_post_id');
	$stmtS->close();
}

$pageTitle = $editing ? 'लेख संपादन' : 'नया लेख';
include __DIR__ . '/header.php';
?>
<section class="max-w-3xl mx-auto px-4 py-8">
	<h1 class="text-2xl font-bold mb-4 text-blue-800"><?= $editing ? 'लेख संपादन' : 'नया लेख' ?></h1>
	<?php if ($errors): ?>
		<div class="bg-red-50 text-red-700 px-3 py-2 rounded mb-3">
			<?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?>
		</div>
	<?php endif; ?>
	<form id="postForm" method="post" enctype="multipart/form-data" class="bg-white rounded shadow p-4 space-y-3 ring-1 ring-blue-100" novalidate>
		<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
		<div>
			<label class="block text-sm mb-1 text-gray-700">शीर्षक (हिंदी)</label>
			<input type="text" name="title_hi" value="<?= e($title_hi) ?>" placeholder="लेख का शीर्षक" required class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500">
		</div>
		<div class="grid md:grid-cols-2 gap-3">
			<div>
				<label class="block text-sm mb-1 text-gray-700">श्रेणी</label>
				<input type="text" name="category" value="<?= e($category) ?>" placeholder="श्रेणी (जैसे: टेक्नोलॉजी)" required class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-fuchsia-500">
			</div>
			<div>
				<label class="block text-sm mb-1 text-gray-700">स्लग</label>
				<input type="text" name="slug" value="<?= e($slug) ?>" placeholder="URL स्लग (स्वतः जनरेट होगा)" class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-teal-500">
			</div>
		</div>
		<div>
			<label class="block text-sm mb-1 text-gray-700">टैग्स/हैशटैग (कौमा से अलग करें)</label>
			<input type="text" name="tags" value="<?= e($tags) ?>" placeholder="उदा: भारत,टेक,AI" class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-amber-500">
		</div>
		<div class="flex items-center gap-2">
			<input id="topChk" type="checkbox" name="is_top_article" <?= $is_top_article ? 'checked' : '' ?> class="w-4 h-4 text-blue-600">
			<label for="topChk">शीर्ष लेख के रूप में दिखाएँ</label>
		</div>
		<div>
			<label class="block text-sm mb-1 text-gray-700">कवर छवि</label>
			<input id="coverInput" type="file" name="cover_image" accept="image/*" class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-indigo-500">
			<?php if ($cover_image_path): ?>
				<p class="text-xs mt-1">वर्तमान: <a target="_blank" class="text-blue-700 hover:underline" href="<?= e(img_src($cover_image_path)) ?>"><?= e($cover_image_path) ?></a></p>
				<div class="mt-2">
					<img id="coverPreview" src="<?= e(img_src($cover_image_path)) ?>" alt="कवर छवि पूर्वावलोकन" class="w-full h-48 object-cover rounded border">
				</div>
			<?php else: ?>
				<div class="mt-2 hidden" id="coverPreviewWrap">
					<img id="coverPreview" src="" alt="कवर छवि पूर्वावलोकन" class="w-full h-48 object-cover rounded border">
				</div>
			<?php endif; ?>
		</div>
		<div>
			<label class="block text-sm mb-1 text-gray-700">सामग्री (हिंदी)</label>
			<textarea name="content_hi" rows="10" placeholder="पूरा लेख यहाँ लिखें..." required class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-fuchsia-500"><?= e($content_hi) ?></textarea>
		</div>
		<div>
			<label class="block text-sm mb-1 text-gray-700">संबंधित लेख चुनें (मल्टी-सेलेक्ट)</label>
			<select name="related_posts[]" multiple size="6" class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-teal-500">
				<?php foreach ($allPosts as $ap): if ($editing && $ap['id'] == $id) continue; ?>
					<option value="<?= (int)$ap['id'] ?>" <?= in_array($ap['id'], $relatedSelected, true) ? 'selected' : '' ?>>
						<?= e($ap['title_hi']) ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="text-xs text-gray-500 mt-1">Ctrl/Command दबाकर बहु-चयन करें।</p>
		</div>
		<div class="flex gap-3">
			<button class="btn-primary text-white px-4 py-2 rounded shadow"><?= $editing ? 'अपडेट करें' : 'सहेजें' ?></button>
			<a href="<?= e(BASE_URL) ?>/admin_dashboard.php" class="px-4 py-2 rounded border hover:bg-blue-50">रद्द करें</a>
		</div>
		<p id="formErr" class="text-sm text-red-600 hidden">कृपया आवश्यक फ़ील्ड भरें।</p>
	</form>
	<script>
		// Basic required-field check (existing)
		document.getElementById('postForm').addEventListener('submit', function(e){
			const title = this.title_hi.value.trim();
			const cat = this.category.value.trim();
			const content = this.content_hi.value.trim();
			if (!title || !cat || !content) {
				e.preventDefault();
				document.getElementById('formErr')?.classList.remove('hidden');
			}
		});

		// Live preview for selected cover image
		(function(){
			const input = document.getElementById('coverInput');
			const img = document.getElementById('coverPreview');
			const wrap = document.getElementById('coverPreviewWrap') || img?.parentElement;
			let lastUrl = null;

			if (!input || !img) return;

			input.addEventListener('change', function(){
				if (!this.files || !this.files[0]) {
					if (wrap) wrap.classList.add('hidden');
					if (img) img.removeAttribute('src');
					if (lastUrl) { URL.revokeObjectURL(lastUrl); lastUrl = null; }
					return;
				}
				const file = this.files[0];
				if (!file.type.match(/^image\//)) return;
				if (lastUrl) URL.revokeObjectURL(lastUrl);
				const url = URL.createObjectURL(file);
				lastUrl = url;
				img.src = url;
				if (wrap) wrap.classList.remove('hidden');
			});
			// Revoke URL on unload
			window.addEventListener('beforeunload', function(){
				if (lastUrl) URL.revokeObjectURL(lastUrl);
			});
		})();
	</script>
</section>
<?php include __DIR__ . '/footer.php'; ?>
