<?php require_once __DIR__ . '/config.php'; require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="hi">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>द व्यान्स</title>
	<!-- Tailwind via official CDN with plugins; fallback to local CSS if CDN fails -->
	<script>
		window.tailwind = window.tailwind || {};
		window.tailwind.config = {
			theme: {
				extend: {
					colors: {
						primary: '#2563eb',   // blue-600
						accent: '#f59e0b',    // amber-600
						danger: '#dc2626',    // red-600
						fuchsia: '#c026d3',   // fuchsia-600
						teal: '#14b8a6',      // teal-500
						indigo: '#4f46e5'
					}
				}
			}
		};
	</script>
	<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
	<script>
		// Fallback to local CSS if Tailwind CDN doesn't load
		setTimeout(function () {
			if (!window.tailwind) {
				var l = document.createElement('link');
				l.rel = 'stylesheet';
				l.href = '<?= e(BASE_URL) ?>/style.css';
				document.head.appendChild(l);
				console.warn('Tailwind CDN लोड नहीं हुआ, स्थानीय CSS उपयोग में।');
			}
		}, 800);
	</script>
	<noscript><link rel="stylesheet" href="<?= e(BASE_URL) ?>/style.css"></noscript>
	<style>
		/* Helpers used site-wide (also mirrored in style.css as fallback) */
		.top-ribbon{height:6px;background:linear-gradient(90deg,#ef4444,#2563eb,#c026d3,#f59e0b,#14b8a6)}
		.header-grad{background:linear-gradient(90deg,#1e3a8a 0%,#2563eb 50%,#7c3aed 100%);color:#fff}
		.footer-grad{background:linear-gradient(135deg,#0f172a 0%,#1e293b 30%,#1d4ed8 100%);color:#e5e7eb}
		.btn-primary{background:linear-gradient(90deg,#2563eb 0%,#7c3aed 50%,#c026d3 100%);color:#fff}
		.btn-primary:hover{filter:brightness(.95)}
		.badge{display:inline-block;border-radius:.375rem;padding:.125rem .5rem;font-size:.75rem}
		.badge-accent{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa}
		.chip{display:inline-block;background:#eef2ff;color:#3730a3;border-radius:9999px;padding:.125rem .5rem;font-size:.75rem}
	</style>
	<meta name="description" content="ताज़ा हिंदी खबरें, ब्लॉग और विशेष आलेख - व्यान्स न्यूज़।">
	<?php if (!empty($metaKeywords)): ?>
		<meta name="keywords" content="<?= e($metaKeywords) ?>">
	<?php endif; ?>
	<link rel="icon" href="<?= e(BASE_URL) ?>/favicon.ico">
</head>
<body class="bg-gradient-to-b from-slate-50 via-white to-slate-50 text-gray-900">
	<div class="top-ribbon"></div>
	<header class="header-grad shadow sticky top-0 z-40">
		<div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
			<a href="<?= e(BASE_URL) ?>/" class="text-2xl font-bold drop-shadow">द व्यान्स</a>

			<!-- Mobile hamburger -->
			<button id="navToggle"
				class="md:hidden inline-flex items-center justify-center p-2 rounded bg-white/10 hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50"
				aria-controls="mobileMenu" aria-expanded="false" aria-label="मेनू खोलें">
				<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
				</svg>
			</button>

			<!-- Desktop nav -->
			<nav class="hidden md:flex space-x-4">
				<a class="hover:underline underline-offset-4" href="<?= e(BASE_URL) ?>/index.php">होम</a>
				<a class="hover:underline underline-offset-4" href="<?= e(BASE_URL) ?>/articles.php">सभी लेख</a>
				<a class="hover:underline underline-offset-4" href="<?= e(BASE_URL) ?>/about.php">हमारे बारे में</a>
				<a class="hover:underline underline-offset-4" href="<?= e(BASE_URL) ?>/contact.php">हमसे संपर्क करें</a>
				<?php if (is_admin()): ?>
					<a class="px-2 py-1 rounded bg-white/10 hover:bg-white/20" href="<?= e(BASE_URL) ?>/admin_dashboard.php">डैशबोर्ड</a>
					<a class="px-2 py-1 rounded bg-red-600/90 hover:bg-red-700 text-white" href="<?= e(BASE_URL) ?>/logout.php">लॉगआउट</a>
				<?php else: ?>
					<a class="px-2 py-1 rounded bg-white/10 hover:bg-white/20" href="<?= e(BASE_URL) ?>/admin_login.php">लॉगिन</a>
				<?php endif; ?>
			</nav>
		</div>

		<!-- Mobile menu (collapsible) -->
		<div id="mobileMenu" class="md:hidden hidden px-4 pb-4">
			<div class="bg-white/10 rounded shadow divide-y divide-white/10">
				<a class="block px-4 py-2 hover:bg-white/20" href="<?= e(BASE_URL) ?>/index.php">होम</a>
				<a class="block px-4 py-2 hover:bg-white/20" href="<?= e(BASE_URL) ?>/articles.php">सभी लेख</a>
				<a class="block px-4 py-2 hover:bg-white/20" href="<?= e(BASE_URL) ?>/about.php">हमारे बारे में</a>
				<a class="block px-4 py-2 hover:bg-white/20" href="<?= e(BASE_URL) ?>/contact.php">हमसे संपर्क करें</a>
				<?php if (is_admin()): ?>
					<a class="block px-4 py-2 hover:bg-white/20" href="<?= e(BASE_URL) ?>/admin_dashboard.php">डैशबोर्ड</a>
					<a class="block px-4 py-2 hover:bg-white/20 text-red-200" href="<?= e(BASE_URL) ?>/logout.php">लॉगआउट</a>
				<?php else: ?>
					<a class="block px-4 py-2 hover:bg-white/20" href="<?= e(BASE_URL) ?>/admin_login.php">लॉगिन</a>
				<?php endif; ?>
			</div>
		</div>

		<script>
			// Toggle mobile menu
			(function(){
				var btn = document.getElementById('navToggle');
				var menu = document.getElementById('mobileMenu');
				if (!btn || !menu) return;
				btn.addEventListener('click', function(){
					var isHidden = menu.classList.contains('hidden');
					menu.classList.toggle('hidden');
					btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
				});
			})();
		</script>
	</header>
	<main class="min-h-[60vh]">
