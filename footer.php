<?php $success = isset($_GET['contact_success']) && $_GET['contact_success'] == '1'; ?>
	</main>
	<footer class="bg-gray-900 text-gray-100 mt-12">
		<div class="max-w-6xl mx-auto px-4 py-10 grid md:grid-cols-2 gap-6">
			<div>
				<h3 class="text-xl font-semibold mb-3">हमारे बारे में</h3>
				<p class="text-sm text-gray-300">व्यान्स न्यूज़ पर आपको मिलती हैं ताज़ा, सटीक और संतुलित हिंदी खबरें। हमारी टीम आपको महत्वपूर्ण विषयों पर गहन विश्लेषण और विश्वसनीय जानकारी प्रदान करती है।</p>
			</div>
			<div>
				<h3 class="text-xl font-semibold mb-3">हमसे संपर्क करें</h3>
				<?php if ($success): ?>
					<p class="mb-3 text-green-400">धन्यवाद! आपका संदेश सफलतापूर्वक भेज दिया गया है।</p>
				<?php endif; ?>
				<form id="contactForm" class="space-y-3" method="post" action="<?= e(BASE_URL) ?>/contact_handler.php" novalidate>
					<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
					<input type="hidden" name="redirect_to" value="<?= e(current_url()) ?>">
					<div>
						<input name="name" type="text" placeholder="आपका नाम" required class="w-full px-3 py-2 rounded bg-gray-800 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
					</div>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
						<input name="email" type="email" placeholder="ईमेल" required class="px-3 py-2 rounded bg-gray-800 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
						<input name="phone" type="tel" placeholder="मोबाइल (वैकल्पिक)" class="px-3 py-2 rounded bg-gray-800 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
					</div>
					<div>
						<textarea name="message" rows="3" placeholder="संदेश लिखें..." required class="w-full px-3 py-2 rounded bg-gray-800 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
					</div>
					<button type="submit" class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded font-semibold">भेजें</button>
					<p id="contactError" class="text-red-400 text-sm hidden mt-2">कृपया सभी आवश्यक फ़ील्ड सही से भरें।</p>
				</form>
				<script>
					document.getElementById('contactForm').addEventListener('submit', function (e) {
						const f = e.target;
						let ok = true;
						['name', 'email', 'message'].forEach(n => {
							const el = f.querySelector('[name="'+n+'"]');
							if (!el || !el.value.trim()) ok = false;
						});
						const email = f.querySelector('[name="email"]').value.trim();
						ok = ok && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
						if (!ok) {
							e.preventDefault();
							document.getElementById('contactError').classList.remove('hidden');
						}
					});
				</script>
			</div>
		</div>
		<div class="text-center text-xs text-gray-400 py-4 border-t border-gray-800">© <?= date('Y') ?> व्यान्स न्यूज़</div>
	</footer>
</body>
</html>
