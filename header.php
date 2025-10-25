<?php require_once __DIR__ . '/config.php'; require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="hi">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>व्यान्स न्यूज़</title>
	<!-- Tailwind via official CDN with plugins; fallback to local CSS if CDN fails -->
	<script>window.tailwind = window.tailwind || {}; window.tailwind.config = { theme: { extend: {} } };</script>
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
	<meta name="description" content="ताज़ा हिंदी खबरें, ब्लॉग और विशेष आलेख - व्यान्स न्यूज़।">
	<?php if (!empty($metaKeywords)): ?>
		<meta name="keywords" content="<?= e($metaKeywords) ?>">
	<?php endif; ?>
	<link rel="icon" href="<?= e(BASE_URL) ?>/favicon.ico">
</head>
<body class="bg-gray-50 text-gray-900">
	<header class="bg-white shadow sticky top-0 z-40">
		<div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
			<a href="<?= e(BASE_URL) ?>/" class="text-2xl font-bold text-indigo-700">व्यान्स न्यूज़</a>
			<nav class="space-x-4">
				<a class="hover:text-indigo-700" href="<?= e(BASE_URL) ?>/index.php">होम</a>
				<a class="hover:text-indigo-700" href="<?= e(BASE_URL) ?>/articles.php">सभी लेख</a>
				<a class="hover:text-indigo-700" href="<?= e(BASE_URL) ?>/about.php">हमारे बारे में</a>
				<a class="hover:text-indigo-700" href="<?= e(BASE_URL) ?>/contact.php">हमसे संपर्क करें</a>
				<?php if (is_admin()): ?>
					<a class="hover:text-indigo-700" href="<?= e(BASE_URL) ?>/admin_dashboard.php">डैशबोर्ड</a>
					<a class="hover:text-red-600" href="<?= e(BASE_URL) ?>/logout.php">लॉगआउट</a>
				<?php else: ?>
					<a class="hover:text-indigo-700" href="<?= e(BASE_URL) ?>/admin_login.php">लॉगिन</a>
				<?php endif; ?>
			</nav>
		</div>
	</header>
	<main class="min-h-[60vh]">
