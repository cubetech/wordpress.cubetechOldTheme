jQuery(function($) {
	$('.farbtastic-colorpicker').each(function() {
		$(this).farbtastic('#' + $(this).attr('data-link'));
	});
});