(function($){
	$(document).ready(function () {
		if ($('.datepicker-me').length > 0) {
			$('.datepicker-me').datepicker({
				dateFormat: 'yy-mm-dd'
			});
		}
	});
})(jQuery)