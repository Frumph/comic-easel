/*
 * Comic Easel content warning.
 *
 * Blurs the comic on a post marked "Enable Content Warning" and lays the warning text over it.
 * The first click on the comic or the warning reveals it; after that the comic behaves as it
 * normally does, so a comic that links to the next page follows that link.
 *
 * The warning text arrives in the global contentwarningtext, set just before this script.
 */
(function () {
	if (typeof contentwarningtext === 'undefined' || contentwarningtext === '') return;

	// Comic Easel marks the comic image #g-comic. Easel-style themes print their own markup, so
	// fall back to the first image inside #comic.
	var comic = document.getElementById('g-comic');
	if (!comic) {
		var area = document.getElementById('comic');
		comic = area ? area.querySelector('img') : null;
		if (!comic) return;
		comic.id = 'g-comic';
	}

	var holder = comic.parentNode;
	var warning = document.createElement('div');
	warning.id = 'contentwarningimg';
	var text = document.createElement('p');
	text.textContent = contentwarningtext;
	warning.appendChild(text);

	comic.style.filter = 'blur(20px)';
	holder.appendChild(warning);

	function reveal(event) {
		event.preventDefault();
		comic.style.filter = '';
		if (warning.parentNode) warning.parentNode.removeChild(warning);
		comic.removeEventListener('click', reveal);
		warning.removeEventListener('click', reveal);
	}

	comic.addEventListener('click', reveal);
	warning.addEventListener('click', reveal);
})();
