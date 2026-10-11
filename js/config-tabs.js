/*
 * Tab switcher for the Comics > Config screen (ceo-config.php).
 *
 * Each tab is a div#comiceasel-tab-{name} and each page a div#comiceasel-{name} inside
 * #comiceasel-options-pages. The page to open first is in that container's data-tab
 * attribute: the requested tab, or the one whose settings were just saved.
 */
(function($) {
	var showPage = function(which) {
		$('#comiceasel-options-pages > div').each(function(i) {
			$(this)[(this.id == 'comiceasel-' + which) ? 'show' : 'hide']();
		});
	};

	$('.comiceasel-tab').click(function() {
		$('#message').animate({height:"0", opacity:0, margin: 0}, 100, 'swing', function() { $(this).remove() });

		showPage(this.id.replace('comiceasel-tab-', ''));
		var myThis = this;
		$('.comiceasel-tab').each(function() {
			var isSame = (this == myThis);
			$(this).toggleClass('on', isSame).toggleClass('off', !isSame);
		});
		return false;
	});

	showPage($('#comiceasel-options-pages').attr('data-tab') || '');
}(jQuery));
