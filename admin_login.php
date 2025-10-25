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
	<h1 class="text-2xl font-bold mb-4 text-blue-800">एडमिन लॉगिन</h1>
	<?php if ($error): ?><div class="bg-red-50 text-red-700 px-3 py-2 rounded mb-3"><?= e($error) ?></div><?php endif; ?>
	<form id="loginForm" method="post" class="bg-white rounded shadow p-4 space-y-3" novalidate>
		<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
		<input type="text" name="username" placeholder="उपयोगकर्ता नाम" required class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500">
		<!-- Password with show/hide toggle -->
		<div class="relative">
			<input id="password" type="password" name="password" placeholder="पासवर्ड" required class="w-full px-3 py-2 border rounded pr-10 focus:ring-2 focus:ring-fuchsia-500">
			<button type="button" id="togglePwd" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700" aria-label="पासवर्ड दिखाएँ" aria-pressed="false">
				<!-- eye (visible) -->
				<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M2.1 12.7a1 1 0 0 1 0-.9C4 8.4 7.7 6 12 6s8 2.4 9.9 5.8a1 1 0 0 1 0 .9C20 16.6 16.3 19 12 19s-8-2.4-9.9-6.3Z" />
					<circle cx="12" cy="12.5" r="3.5" />
				</svg>
			</button>
		</div>
		<button class="btn-primary text-white px-4 py-2 rounded shadow">लॉगिन</button>
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

		// Show/Hide password toggle
		(function(){
			const pwd = document.getElementById('password');
			const btn = document.getElementById('togglePwd');
			if (!pwd || !btn) return;
			function eyeIcon(open) {
				return open
					? '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.1 12.7a1 1 0 0 1 0-.9C4 8.4 7.7 6 12 6s8 2.4 9.9 5.8a1 1 0 0 1 0 .9C20 16.6 16.3 19 12 19s-8-2.4-9.9-6.3Z" /><circle cx="12" cy="12.5" r="3.5" /></svg>'
					: '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 5.2A9.9 9.9 0 0 1 12 5c4.3 0 8 2.4 9.9 5.8a1 1 0 0 1 0 .9c-.7 1.4-1.7 2.6-2.9 3.6M6.3 6.3C4.6 7.4 3.2 9 2.1 11.8a1 1 0 0 0 0 .9C4 16.6 7.7 19 12 19c1.3 0 2.6-.2 3.7-.6M9.5 9.5a3.5 3.5 0 0 0 4.9 4.9" /></svg>';
			}
			btn.addEventListener('click', function(){
				const show = pwd.type === 'password';
				pwd.type = show ? 'text' : 'password';
				btn.setAttribute('aria-pressed', show ? 'true' : 'false');
				btn.setAttribute('aria-label', show ? 'पासवर्ड छिपाएँ' : 'पासवर्ड दिखाएँ');
				btn.innerHTML = eyeIcon(!show);
			});
		})();
	</script>
</section>
<?php include __DIR__ . '/footer.php'; ?>
