jQuery(document).ready(function($){
    
    $(".casthover-hovercard-hook").hover(
  function() {
	    $pposl = $(this).offset().left;
	    $ppost = $(this).offset().top;
	    $pposl = $pposl - 50;
	    $(this).find('div.casthover-hovercard').hide().css('visibility','visible').fadeIn('fast');
	    $(this).find('div.casthover-hovercard').css('left',$pposl);
	},
	function() {
	    $(this).find('div.casthover-hovercard').fadeOut('slow', function () {
		$(this).show().css('visibility','hidden');
	    })
	}
    );
})
