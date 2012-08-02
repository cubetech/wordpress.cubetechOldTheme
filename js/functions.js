jQuery(function($) {
    Cufon.replace('#navigation ul li a', { hover: true, textShadow: '#030303 0 1px 1px;', fontFamily: 'Ubuntu_bold', hover: { textShadow: '#ca3035 1px 1px 7px, #ca3035 -1px -1px 7px;' }}); 
    $('#navigation ul li.current_page_item a').addClass('active');
    Cufon.replace('#navigation ul li a.active', { hover: false, textShadow: '#030303 1px 2px 1px;' });
    $('.three-cols li:last').addClass('last');
    
    $('.ginput_container input').each(function(){
        $(this).attr('title',$(this).val());
    });
    
    $('.ginput_container textarea').each(function(){
        $(this).attr('title',$(this).val());
    });
	$('input, textarea').focus(function() {
        if(this.title==this.value) {
            this.value = '';
        }
    }).blur(function(){
        if(this.value=='') {
            this.value = this.title;
        }
    });
    $('#footer .f-cols ul li:first').addClass('first');
    $('#f-cols').children('ul').children('li:last').addClass('last');
    $(".menu-item:last").html($(".menu-item:last").html().replace("</a>&nbsp;|","</a>"));

    $('#search .field-wrapper input.field').focus(function(){
    	$(this).animate({'width': '162'});
    	$(this).parent().animate({'width': '204'}).css({'background-position': 'right bottom', 'border-color': '#a4a4a4', 'text-shadow': '#595959 0 0 5px'});
    }).blur(function(){
    	$(this).animate({'width': '56'});
    	$(this).parent().animate({'width': '66'}).css({'background-position': 'right 0', 'border-color': '#eaeaea', 'text-shadow': 'none'});
    });
    
     $('.slider').flexslider({
        slideshowSpeed: 8000,
		animationDuration: 800, 
		directionNav: true,
		controlNav: true    
    });
    $('a[href=#top]').click(function(){
        $('html, body').animate({scrollTop:0}, 'slow');
        return false;
    });
    $("p").filter( function() {
    return $.trim($(this).html()) == '';
}).remove();

});
