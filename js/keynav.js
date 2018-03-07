jQuery(document).ready(function () {

        var alt_down = false;

        jQuery(document).keyup(function(e) {
                if(document.querySelector('#comment:focus,#author:focus,#email:focus,#url:focus,#mcspvalue:focus')) return;

                if(e.which == 18) {
                        alt_down = false;
                }
        });

	jQuery(document).keydown(function(e) {
		var url = false;

	        if(document.querySelector('#comment:focus,#author:focus,#email:focus,#url:focus,#mcspvalue:focus') || alt_down) return;

		if (e.which == 37) {  // Left arrow key code
			url = jQuery('a.comic-nav-previous').attr('href');
		} else if (e.which == 39) {  // Right arrow key code
			url = jQuery('a.comic-nav-next').attr('href');
		} else if (e.which == 18) {  // Alt key code
                        alt_down = true;
                }
		if (url) {
			window.location = url;
		}
	});

});
