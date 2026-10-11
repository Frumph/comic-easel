/*
 * Comic Easel jump menus: the chapter dropdown and the comic list dropdown.
 *
 * Picking an entry in a select with the class ceo-jump-menu goes to the page it names, the
 * URL in the option's value. The "Select ..." and "Jump To" prompts have no page to go to.
 * Without JavaScript the form's own View button is shown instead.
 */
(function () {
	document.addEventListener('change', function (event) {
		var select = event.target;
		if (!select.classList || !select.classList.contains('ceo-jump-menu')) return;
		var url = select.value;
		if (url === '' || url === '0') return;
		document.location.href = url;
	});
})();
