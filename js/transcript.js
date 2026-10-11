/*
 * Collapsible transcript, for the "styled" transcript display (ceo_the_transcript()).
 *
 * The transcript is printed expanded, so it stays readable without JavaScript. This
 * collapses each one and makes its title link toggle it open and closed again. Loaded in
 * the footer, so every transcript on the page is already there.
 */
(function () {
	var titles = document.querySelectorAll('.transcript-border a.transcript-title');
	for (var i = 0; i < titles.length; i++) {
		(function (title) {
			var content = title.nextElementSibling;
			if (!content || content.id != 'transcript-content') return;
			content.style.height = '1px';
			title.setAttribute('aria-expanded', 'false');
			title.addEventListener('click', function (event) {
				event.preventDefault();
				var open = (content.style.height != 'auto');
				content.style.height = open ? 'auto' : '1px';
				title.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
		})(titles[i]);
	}
})();
