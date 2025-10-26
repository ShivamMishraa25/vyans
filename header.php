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

	<?php if (!empty($og) && is_array($og)): ?>
		<meta property="og:title" content="<?= e($og['title'] ?? '') ?>">
		<meta property="og:image" content="<?= e($og['image'] ?? '') ?>">
		<meta property="og:url" content="<?= e($og['url'] ?? '') ?>">
		<meta property="og:type" content="article">
		<meta name="twitter:card" content="<?= e($og['twitter_card'] ?? 'summary_large_image') ?>">
		<meta name="twitter:title" content="<?= e($og['title'] ?? '') ?>">
		<?php if (!empty($og['description'])): ?>
			<meta name="twitter:description" content="<?= e($og['description']) ?>">
		<?php endif; ?>
		<?php if (!empty($og['image'])): ?>
			<meta name="twitter:image" content="<?= e($og['image']) ?>">
		<?php endif; ?>
	<?php else: ?>
		<!-- Homepage defaults -->
		<meta property="og:title" content="The Vyans | Advocate Anshu Singh">
		<meta property="og:description" content="Explore expert articles and legal insights on education, law, and other topics. A platform by Advocate Anshu Singh.">
		<meta property="og:type" content="website">
		<meta property="og:url" content="<?= e(rtrim(BASE_URL, '/')) ?>">
		<meta property="og:image" content="https://ibb.co/DPzH5GNN">
		<meta name="twitter:card" content="summary_large_image">
		<meta name="twitter:title" content="The Vyans | Advocate Anshu Singh">
		<meta name="twitter:description" content="Explore expert articles and legal insights on education, law, and other topics. A platform by Advocate Anshu Singh.">
		<meta name="twitter:image" content="https://ibb.co/DPzH5GNN">
	<?php endif; ?>

	<link rel="icon" href="<?= e(BASE_URL) ?>/favicon.ico">
</head>
<body class="bg-gradient-to-b from-slate-50 via-white to-slate-50 text-gray-900">
	<div id="fb-root"></div>
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
				<a class="hover:underline underline-offset-4" href="<?= e(BASE_URL) ?>/news.php">ताज़ा समाचार</a>
				<a class="hover:underline underline-offset-4" href="<?= e(BASE_URL) ?>/law.php">कानून और न्याय</a>
				<a class="hover:underline underline-offset-4" href="<?= e(BASE_URL) ?>/biography.php">जीवनी लेख</a>
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

		<!-- Mobile overlay -->
		<div id="mobileOverlay" class="md:hidden hidden fixed inset-0 bg-black/50 z-40"></div>

		<!-- Mobile menu (right drawer) -->
		<div id="mobileMenu" class="md:hidden fixed inset-y-0 right-0 w-80 max-w-[85%] bg-gradient-to-b from-indigo-700 via-blue-700 to-fuchsia-700 text-white shadow-2xl border-l border-white/10 transform translate-x-full transition-all duration-300 z-50">
			<!-- Drawer header -->
			<div class="flex items-center justify-between px-4 py-4 bg-white/10 border-b border-white/10">
				<span class="font-semibold tracking-wide">द व्यान्स</span>
				<button id="navClose" class="p-2 rounded hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/40" aria-label="मेनू बंद करें">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
					</svg>
				</button>
			</div>

			<!-- Drawer links -->
			<nav class="px-2 py-3 space-y-1">
				<a class="flex items-center gap-3 px-3 py-2 mx-1 rounded-md hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30" href="<?= e(BASE_URL) ?>/index.php">
					<span class="inline-block w-2 h-2 rounded-full bg-white/70"></span> होम
				</a>
				<a class="flex items-center gap-3 px-3 py-2 mx-1 rounded-md hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30" href="<?= e(BASE_URL) ?>/articles.php">
					<span class="inline-block w-2 h-2 rounded-full bg-white/70"></span> सभी लेख
				</a>
				<a class="flex items-center gap-3 px-3 py-2 mx-1 rounded-md hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30" href="<?= e(BASE_URL) ?>/news.php">
					<span class="inline-block w-2 h-2 rounded-full bg-white/70"></span> ताज़ा समाचार
				</a>
				<a class="flex items-center gap-3 px-3 py-2 mx-1 rounded-md hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30" href="<?= e(BASE_URL) ?>/law.php">
					<span class="inline-block w-2 h-2 rounded-full bg-white/70"></span> कानून और न्याय
				</a>
				<a class="flex items-center gap-3 px-3 py-2 mx-1 rounded-md hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30" href="<?= e(BASE_URL) ?>/biography.php">
					<span class="inline-block w-2 h-2 rounded-full bg-white/70"></span> जीवनी लेख
				</a>
				<a class="flex items-center gap-3 px-3 py-2 mx-1 rounded-md hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30" href="<?= e(BASE_URL) ?>/about.php">
					<span class="inline-block w-2 h-2 rounded-full bg-white/70"></span> हमारे बारे में
				</a>
				<a class="flex items-center gap-3 px-3 py-2 mx-1 rounded-md hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30" href="<?= e(BASE_URL) ?>/contact.php">
					<span class="inline-block w-2 h-2 rounded-full bg-white/70"></span> हमसे संपर्क करें
				</a>
				<?php if (is_admin()): ?>
					<a class="flex items-center gap-3 px-3 py-2 mx-1 rounded-md hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30" href="<?= e(BASE_URL) ?>/admin_dashboard.php">
						<span class="inline-block w-2 h-2 rounded-full bg-white/70"></span> डैशबोर्ड
					</a>
					<a class="flex items-center gap-3 px-3 py-2 mx-1 rounded-md bg-white/10 hover:bg-white/20 text-red-200 focus:outline-none focus:ring-2 focus:ring-white/30" href="<?= e(BASE_URL) ?>/logout.php">
						<span class="inline-block w-2 h-2 rounded-full bg-red-200/80"></span> लॉगआउट
					</a>
				<?php else: ?>
					<a class="flex items-center gap-3 px-3 py-2 mx-1 rounded-md hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30" href="<?= e(BASE_URL) ?>/admin_login.php">
						<span class="inline-block w-2 h-2 rounded-full bg-white/70"></span> लॉगिन
					</a>
				<?php endif; ?>
			</nav>
		</div>

		<script>
			// Right drawer open/close
			(function(){
				var btn = document.getElementById('navToggle');
				var menu = document.getElementById('mobileMenu');
				var overlay = document.getElementById('mobileOverlay');
				var closeBtn = document.getElementById('navClose');

				if (!btn || !menu || !overlay) return;

				function openMenu(){
					menu.classList.remove('translate-x-full');
					overlay.classList.remove('hidden');
					btn.setAttribute('aria-expanded','true');
				}
				function closeMenu(){
					menu.classList.add('translate-x-full');
					overlay.classList.add('hidden');
					btn.setAttribute('aria-expanded','false');
				}

				btn.addEventListener('click', function(){
					var isClosed = menu.classList.contains('translate-x-full');
					isClosed ? openMenu() : closeMenu();
				});
				overlay.addEventListener('click', closeMenu);
				if (closeBtn) closeBtn.addEventListener('click', closeMenu);
			})();
		</script>
	</header>
	<main class="min-h-[60vh]">
