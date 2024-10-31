/** Mystical Companion Custom Scripts **/

jQuery(document).ready(function($){
	
	$('.mystical_eo_iconlist li').on('click', function() {
		var icon = $(this).find('span').attr('class');
		
		$(this).parents('ul').next('input[type="hidden"]').val(icon);
		$(this).parents('ul').find('li').removeClass('active');
		$(this).addClass('active');
	});

});