<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
if (is_admin()) {
	header('Location: ' . BASE_URL . '/admin_dashboard.php');
	exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
		$error = 'अमान्य अनुरोध।';
	} else {
		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';
		if ($username && $password) {
			$stmt = $mysqli->prepare('SELECT id, username, password_hash FROM admin WHERE username = ? LIMIT 1');
			$stmt->bind_param('s', $username);
			$stmt->execute();
			$row = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			if ($row && password_verify($password, $row['password_hash'])) {
				$_SESSION['admin_id'] = (int)$row['id'];
				$_SESSION['admin_username'] = $row['username'];
				header('Location: ' . BASE_URL . '/admin_dashboard.php');
				exit;
			}
			$error = 'गलत उपयोगकर्ता नाम या पासवर्ड।';
		} else {
			$error = 'कृपया विवरण भरें।';
		}
	}
}
?>
<?php $pageTitle = 'लॉगिन'; include __DIR__ . '/header.php'; ?>
<section class="max-w-md mx-auto px-4 py-12">
	<h1 class="text-2xl font-bold mb-4">एडमिन लॉगिन</h1>
	<?php if ($error): ?><div class="bg-red-50 text-red-700 px-3 py-2 rounded mb-3"><?= e($error) ?></div><?php endif; ?>
	<form id="loginForm" method="post" class="bg-white rounded shadow p-4 space-y-3" novalidate>
		<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
		<input type="text" name="username" placeholder="उपयोगकर्ता नाम" required class="w-full px-3 py-2 border rounded">
		<input type="password" name="password" placeholder="पासवर्ड" required class="w-full px-3 py-2 border rounded">
		<button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">लॉगिन</button>
		<p id="loginErr" class="text-sm text-red-600 hidden">कृपया दोनों फ़ील्ड भरें।</p>
	</form>
	<script>
		document.getElementById('loginForm').addEventListener('submit', function(e){
			const u = this.username.value.trim();
			const p = this.password.value.trim();
			if (!u || !p) {
				e.preventDefault();
				document.getElementById('loginErr').classList.remove('hidden');
			}
		});
	</script>
</section>
<?php include __DIR__ . '/footer.php'; ?>
