<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;

$title_hi = $category = $content_hi = $tags = $cover_image_path = '';
$is_top_article = 0;
$slug = '';
$existingGallery = []; // Added: holder for current gallery
// New flags
$isBiography = 0;
$isNews = 0;
$isLaw = 0;

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
	// Load flags if present
	$isBiography = (int)($cur['isBiography'] ?? 0);
	$isNews      = (int)($cur['isNews'] ?? 0);
	$isLaw       = (int)($cur['isLaw'] ?? 0);

	// Added: fetch existing gallery images for this post
	$stmtEG = $mysqli->prepare('SELECT id, image_path, caption FROM post_images WHERE post_id=? ORDER BY COALESCE(sort_order, 999999), id');
	$stmtEG->bind_param('i', $id);
	$stmtEG->execute();
	$existingGallery = $stmtEG->get_result()->fetch_all(MYSQLI_ASSOC);
	$stmtEG->close();
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
		// New flags from form
		$isBiography = isset($_POST['isBiography']) ? 1 : 0;
		$isNews      = isset($_POST['isNews']) ? 1 : 0;
		$isLaw       = isset($_POST['isLaw']) ? 1 : 0;
		$selected_related = array_map('intval', $_POST['related_posts'] ?? []);
		$replaceGallery = isset($_POST['replace_gallery']); // new
		$removeIds = array_map('intval', $_POST['remove_gallery_ids'] ?? []); // new

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

		// Handle gallery images (multiple)
		$galleryNew = []; // store uploaded gallery image relative paths
		if (!empty($_FILES['gallery_images']) && is_array($_FILES['gallery_images']['name'])) {
			$uploadDirGal = __DIR__ . '/uploads/gallery';
			ensure_upload_dir($uploadDirGal);
			$allowedMimes = ['image/jpeg','image/png','image/gif','image/webp'];

			$names = $_FILES['gallery_images']['name'];
			$tmpns = $_FILES['gallery_images']['tmp_name'];
			$errs  = $_FILES['gallery_images']['error'];

			for ($i = 0, $n = count($names); $i < $n; $i++) {
				if (!isset($names[$i]) || $errs[$i] !== UPLOAD_ERR_OK) continue;
				$tmp = $tmpns[$i];
				$mime = @mime_content_type($tmp);
				if (!in_array($mime, $allowedMimes, true)) continue;

				$fname = safe_filename($names[$i]);
				$dest = $uploadDirGal . '/' . $fname;
				if (@move_uploaded_file($tmp, $dest)) {
					$galleryNew[] = 'uploads/gallery/' . $fname;
				}
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
				$stmt = $mysqli->prepare('UPDATE posts SET title_hi=?, slug=?, content_hi=?, category=?, cover_image_path=?, tags=?, is_top_article=?, isBiography=?, isNews=?, isLaw=? WHERE id=?');
				$stmt->bind_param('ssssssiiiii', $title_hi, $slug, $content_hi, $category, $cover_image_path, $tags, $is_top_article, $isBiography, $isNews, $isLaw, $id);
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
				// Remove gallery images (replace all or selected)
				if ($replaceGallery) {
					// delete all existing gallery for this post
					$stmtGetAll = $mysqli->prepare('SELECT id, image_path FROM post_images WHERE post_id=?');
					$stmtGetAll->bind_param('i', $id);
					$stmtGetAll->execute();
					$resAll = $stmtGetAll->get_result();
					while ($row = $resAll->fetch_assoc()) {
						$abs = __DIR__ . '/' . ltrim($row['image_path'], '/');
						if (is_file($abs)) { @unlink($abs); }
					}
					$stmtGetAll->close();
					$stmtDelAll = $mysqli->prepare('DELETE FROM post_images WHERE post_id=?');
					$stmtDelAll->bind_param('i', $id);
					$stmtDelAll->execute();
					$stmtDelAll->close();
				} elseif ($removeIds) {
					// delete only selected ids
					$stmtOne = $mysqli->prepare('SELECT image_path FROM post_images WHERE post_id=? AND id=? LIMIT 1');
					$stmtDel = $mysqli->prepare('DELETE FROM post_images WHERE post_id=? AND id=?');
					foreach ($removeIds as $rid) {
						$stmtOne->bind_param('ii', $id, $rid);
						$stmtOne->execute();
						$imgRow = $stmtOne->get_result()->fetch_assoc();
						if ($imgRow && !empty($imgRow['image_path'])) {
							$abs = __DIR__ . '/' . ltrim($imgRow['image_path'], '/');
							if (is_file($abs)) { @unlink($abs); }
						}
						$stmtDel->bind_param('ii', $id, $rid);
						$stmtDel->execute();
					}
					$stmtOne->close();
					$stmtDel->close();
				}
				// Insert newly uploaded gallery images (if any)
				if ($galleryNew) {
					$stmtGI = $mysqli->prepare('INSERT INTO post_images (post_id, image_path) VALUES (?, ?)');
					foreach ($galleryNew as $gp) {
						$stmtGI->bind_param('is', $id, $gp);
						$stmtGI->execute();
					}
					$stmtGI->close();
				}
				// Recalculate gallery_count to be accurate
				$stmtCnt = $mysqli->prepare('UPDATE posts p SET gallery_count = (SELECT COUNT(*) FROM post_images pi WHERE pi.post_id = ?) WHERE p.id = ?');
				$stmtCnt->bind_param('ii', $id, $id);
				$stmtCnt->execute();
				$stmtCnt->close();

				flash('msg', 'लेख अपडेट किया गया।');
			} else {
				$stmt = $mysqli->prepare('INSERT INTO posts (title_hi, slug, content_hi, category, cover_image_path, tags, is_top_article, isBiography, isNews, isLaw) VALUES (?,?,?,?,?,?,?,?,?,?)');
				$stmt->bind_param('ssssssiiii', $title_hi, $slug, $content_hi, $category, $cover_image_path, $tags, $is_top_article, $isBiography, $isNews, $isLaw);
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
				// Insert gallery for new post
				if (!empty($newId) && $galleryNew) {
					$stmtGI = $mysqli->prepare('INSERT INTO post_images (post_id, image_path) VALUES (?, ?)');
					foreach ($galleryNew as $gp) {
						$stmtGI->bind_param('is', $newId, $gp);
						$stmtGI->execute();
					}
					$stmtGI->close();
				}
				// Recalculate gallery_count for new post
				if (!empty($newId)) {
					$stmtCnt = $mysqli->prepare('UPDATE posts p SET gallery_count = (SELECT COUNT(*) FROM post_images pi WHERE pi.post_id = ?) WHERE p.id = ?');
					$stmtCnt->bind_param('ii', $newId, $newId);
					$stmtCnt->execute();
					$stmtCnt->close();
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
		<div class="flex flex-wrap items-center gap-4">
			<label class="inline-flex items-center gap-2">
				<input type="checkbox" name="is_top_article" <?= $is_top_article ? 'checked' : '' ?> class="w-4 h-4 text-blue-600">
				<span>शीर्ष लेख</span>
			</label>
			<label class="inline-flex items-center gap-2">
				<input type="checkbox" name="isBiography" <?= $isBiography ? 'checked' : '' ?> class="w-4 h-4 text-blue-600">
				<span>जीवनी लेख</span>
			</label>
			<label class="inline-flex items-center gap-2">
				<input type="checkbox" name="isLaw" <?= $isLaw ? 'checked' : '' ?> class="w-4 h-4 text-blue-600">
				<span>कानून और न्याय</span>
			</label>
			<label class="inline-flex items-center gap-2">
				<input type="checkbox" name="isNews" <?= $isNews ? 'checked' : '' ?> class="w-4 h-4 text-blue-600">
				<span>ताज़ा समाचार</span>
			</label>
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
			<label class="block text-sm mb-1 text-gray-700">गैलरी छवियाँ (एक से अधिक)</label>
			<!-- NEW: Replace entire gallery switch -->
			<label class="inline-flex items-center gap-2 mb-2">
				<input type="checkbox" name="replace_gallery" class="w-4 h-4 text-blue-600">
				<span class="text-sm text-gray-700">नई छवियाँ जोड़ने से पहले पूरी गैलरी बदलें (पुरानी हटेगी)</span>
			</label>
			<input id="galleryInput" type="file" name="gallery_images[]" accept="image/*" multiple class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-indigo-500">
			<p class="text-xs text-gray-500 mt-1">एक से अधिक छवियाँ चुनें। JPG/PNG/GIF/WEBP समर्थित।</p>

			<?php if ($editing && !empty($existingGallery)): ?>
				<div class="mt-3">
					<h4 class="text-sm font-semibold mb-2 text-gray-700">वर्तमान गैलरी</h4>
					<div class="grid grid-cols-3 gap-2">
						<?php foreach ($existingGallery as $gi): ?>
							<div class="rounded overflow-hidden border bg-white p-1">
								<a href="<?= e(img_src($gi['image_path'])) ?>" target="_blank" rel="noopener" class="block">
									<img src="<?= e(img_src($gi['image_path'])) ?>" alt="<?= e($gi['caption'] ?? $title_hi) ?>" class="w-full h-24 object-cover rounded">
								</a>
								<label class="flex items-center gap-1 mt-1 text-xs text-gray-700">
									<input type="checkbox" name="remove_gallery_ids[]" value="<?= (int)$gi['id'] ?>" class="w-4 h-4">
									<span>हटाएँ</span>
								</label>
							</div>
						<?php endforeach; ?>
					</div>
					<p class="text-xs text-gray-500 mt-1">ऊपर “हटाएँ” चुनकर चयनित छवियाँ हटाएँ या “पूरी गैलरी बदलें” चुनें।</p>
				</div>
			<?php endif; ?>

			<!-- Live preview of newly selected gallery images -->
			<div id="galleryNewPreviewWrap" class="mt-3 hidden">
				<h4 class="text-sm font-semibold mb-2 text-gray-700">नई गैलरी पूर्वावलोकन</h4>
				<div id="galleryNewPreview" class="grid grid-cols-3 gap-2"></div>
			</div>
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

		// Live preview for newly selected gallery images (multiple)
		(function(){
			const input = document.getElementById('galleryInput');
			const wrap = document.getElementById('galleryNewPreviewWrap');
			const cont = document.getElementById('galleryNewPreview');
			let urls = [];
			if (!input || !wrap || !cont) return;

			function clearPreviews() {
				urls.forEach(u => URL.revokeObjectURL(u));
				urls = [];
				cont.innerHTML = '';
			}

			input.addEventListener('change', function(){
				clearPreviews();
				if (!this.files || this.files.length === 0) {
					wrap.classList.add('hidden');
					return;
				}
				Array.from(this.files).forEach(f => {
					if (!/^image\//.test(f.type)) return;
					const u = URL.createObjectURL(f);
					urls.push(u);
					const img = document.createElement('img');
					img.src = u;
					img.alt = 'गैलरी पूर्वावलोकन';
					img.className = 'w-full h-24 object-cover rounded border';
					const holder = document.createElement('div');
					holder.className = 'rounded overflow-hidden bg-white';
					holder.appendChild(img);
					cont.appendChild(holder);
				});
				wrap.classList.toggle('hidden', cont.children.length === 0);
			});

			window.addEventListener('beforeunload', clearPreviews);
		})();
	</script>
</section>
<?php include __DIR__ . '/footer.php'; ?>
