<?php $success = isset($_GET['contact_success']) && $_GET['contact_success'] == '1'; ?>
	</main>
	<footer class="footer-grad mt-12">
		<div class="max-w-6xl mx-auto px-4 py-10 grid md:grid-cols-2 gap-6">
			<div>
				<h3 class="text-xl font-semibold mb-3">हमारे बारे में</h3>
				<p class="text-sm text-gray-200">नमस्कार मै एड. अंशु जर्नलिस्ट मै लेखिका हूं, और लीगल एडवाइजर , हूं किसी भी प्रकार की हेल्प के लिए मेरे डिजिटल प्लेटफॉर्म से जुड़िए , राष्ट्र हित के लिए कदम , सच तुम तक , न बहस न बहाना , हम तहकीकात करेंगे सच लाएंगे , हम यहां रोजगार जॉब्स , स्टोरी निबंध कविताएं, न्यूज आर्टिकल जैसी पोस्ट मिलेगे|</p>
			</div>
			<div>
				<h3 class="text-xl font-semibold mb-3">हमसे संपर्क करें</h3>
				<?php if ($success): ?>
					<p class="mb-3 text-green-300">धन्यवाद! आपका संदेश सफलतापूर्वक भेज दिया गया है।</p>
				<?php endif; ?>
				<?php
					$__cur = current_url();
					$__sep = (strpos($__cur, '?') === false) ? '?' : '&';
					$__redirect_to = $__cur . $__sep . 'contact_success=1';
				?>
				<form id="contactForm" class="space-y-3" method="post" action="https://api.web3forms.com/submit" novalidate>
					<!-- Web3Forms required/optional fields -->
					<input type="hidden" name="access_key" value="1634c43e-a896-4ee8-9af5-b72dd512d366">
					<input type="hidden" name="subject" value="व्यान्स न्यूज़ - नया संदेश">
					<input type="hidden" name="redirect" value="<?= e($__redirect_to) ?>">
					<!-- Your fields -->
					<div>
						<input name="name" type="text" placeholder="आपका नाम" required class="w-full px-3 py-2 rounded bg-white/10 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
					</div>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
						<input name="email" type="email" placeholder="ईमेल" required class="px-3 py-2 rounded bg-white/10 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
						<input name="phone" type="tel" placeholder="मोबाइल (वैकल्पिक)" class="px-3 py-2 rounded bg-white/10 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-amber-500">
					</div>
					<div>
						<textarea name="message" rows="3" placeholder="संदेश लिखें..." required class="w-full px-3 py-2 rounded bg-white/10 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
					</div>
					<button type="submit" class="btn-primary px-4 py-2 rounded font-semibold shadow">भेजें</button>
					<p id="contactError" class="text-red-300 text-sm hidden mt-2">कृपया सभी आवश्यक फ़ील्ड सही से भरें।</p>
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

				<!-- Social + Email links -->
				<div class="mt-5">
					<h4 class="text-sm font-semibold mb-2 text-gray-200">सोशल/ईमेल</h4>
					<div class="flex flex-wrap gap-2">
						<!-- YouTube -->
						<a href="https://www.youtube.com/@vyanssanchar" target="_blank" rel="noopener"
						   class="inline-flex items-center gap-2 px-3 py-2 rounded bg-white ring-1 ring-red-200 text-red-600 hover:bg-red-50">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2a4 4 0 0 0-2.8-2.8C18.9 3 12 3 12 3s-6.9 0-8.7.4A4 4 0 0 0 .5 6.2 41 41 0 0 0 0 12a41 41 0 0 0 .5 5.8 4 4 0 0 0 2.8 2.8C5.1 21 12 21 12 21s6.9 0 8.7-.4a4 4 0 0 0 2.8-2.8A41 41 0 0 0 24 12a41 41 0 0 0-.5-5.8zM9.8 15.5V8.5L15.5 12l-5.7 3.5z"/></svg>
							<span>YouTube</span>
						</a>
						<!-- Facebook -->
						<a href="https://www.facebook.com/profile.php?id=61578973687947" target="_blank" rel="noopener"
						   class="inline-flex items-center gap-2 px-3 py-2 rounded bg-white ring-1 ring-blue-200 text-blue-700 hover:bg-blue-50">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06C2 17.06 5.66 21.2 10.44 22v-7.03H7.9v-2.9h2.54v-2.2c0-2.5 1.5-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.62.77-1.62 1.56v1.86h2.77l-.44 2.9h-2.33V22C18.34 21.2 22 17.06 22 12.06z"/></svg>
							<span>Facebook</span>
						</a>
						<!-- X -->
						<a href="https://x.com/Anshuga55479723" target="_blank" rel="noopener"
						   class="inline-flex items-center gap-2 px-3 py-2 rounded bg-white ring-1 ring-gray-300 text-gray-900 hover:bg-gray-50">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2H22l-7.7 8.8L23.5 22h-6.4l-5-6.5L5.4 22H2.3l8.3-9.5L.8 2h6.5l4.6 6 7-6zM17 20.4h2l-9-11-2 2.3 9 8.7z"/></svg>
							<span>X</span>
						</a>
						<!-- Instagram -->
						<a href="https://www.instagram.com/adv.anshu_journalist" target="_blank" rel="noopener"
						   class="inline-flex items-center gap-2 px-3 py-2 rounded bg-white ring-1 ring-fuchsia-200 text-fuchsia-700 hover:bg-fuchsia-50">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7zm5 3.5A5.5 5.5 0 1 1 6.5 13 5.51 5.51 0 0 1 12 7.5zm0 2A3.5 3.5 0 1 0 15.5 13 3.5 3.5 0 0 0 12 9.5zm5.75-3.25a1.25 1.25 0 1 1-1.25 1.25 1.25 1.25 0 0 1 1.25-1.25z"/></svg>
							<span>Instagram</span>
						</a>
						<!-- Email -->
						<a href="mailto:anshu@thevyans.com" target="_blank" rel="noopener"
						   class="inline-flex items-center gap-2 px-3 py-2 rounded bg-white ring-1 ring-indigo-200 text-indigo-700 hover:bg-indigo-50">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M2 6.5A2.5 2.5 0 0 1 4.5 4h15A2.5 2.5 0 0 1 22 6.5v11A2.5 2.5 0 0 1 19.5 20h-15A2.5 2.5 0 0 1 2 17.5v-11Zm2.2-.3l7.3 5.1c.3.2.7.2 1 0l7.3-5.1a.5.5 0 0 0-.3-.2H4.5a.5.5 0 0 0-.3.2ZM20 8.3l-6.5 4.5a2.5 2.5 0 0 1-2.9 0L4 8.3V17.5c0 .3.2.5.5.5h15c.3 0 .5-.2.5-.5V8.3Z"/></svg>
							<span>Email</span>
						</a>
					</div>
				</div>
			</div>
		</div>
		<div class="h-1 bg-gradient-to-r from-red-500 via-blue-600 to-fuchsia-600"></div>
		<div class="text-center text-xs text-gray-300 py-4">© <?= date('Y') ?> व्यान्स न्यूज़</div>
	</footer>

	<!-- Embeds: Instagram + Facebook SDK (client-side rendering) -->
	<!-- fb-root moved to header.php to satisfy SDK requirements -->
	<script async defer crossorigin="anonymous" src="https://connect.facebook.net/hi_IN/sdk.js#xfbml=1&version=v19.0"></script>
	<script async src="https://www.instagram.com/embed.js"></script>
</body>
</html>
