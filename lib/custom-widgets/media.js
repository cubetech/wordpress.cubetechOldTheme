(function($){
	function widg_hide_media() {
		tb_remove();
	}
	$(document).ready(function(){ 
		
		$('.widg_openmedia').live('click', function(){
			var input = $(this).parent('.widg_media').find('input[type="text"]');
			
			window.pb_medialibrary = function(html) {
				var data = c2_unserialize(html);
				if ( data.url != undefined && data.url != '' ) {
					$(input).val(data.url);
				} else {
					alert('Something went wrong...');
				};
				
				widg_hide_media();
			}
			
			window.send_to_editor = function(html) {
				imgurl = $('img', html).attr('src');
				$(input).val(imgurl);
				
				widg_hide_media();
			}
			
			if ( typeof(win) !== 'undefined' ) {
				win.send_to_editor = function(html) {
					imgurl = $('img', html).attr('src');
					$(input).val(imgurl);
					
					widg_hide_media();
				}
			};
			return true;
		});
	});
})(jQuery)