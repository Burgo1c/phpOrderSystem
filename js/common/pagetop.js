jQuery(function() {
	var showFlug = false;
	var topBtn = jQuery('#page-top');	
	topBtn.css('bottom', '-120px');
	jQuery(window).scroll(function () {
		if (jQuery(this).scrollTop() > 100) {
			if (showFlug == false) {
				showFlug = true;
				topBtn.stop().animate({'bottom' : '20px'}, 200); 
			}
		} else {
			if (showFlug) {
				showFlug = false;
				topBtn.stop().animate({'bottom' : '-120px'}, 200); 
			}
		}
	});
	//スクロールしてトップ
    topBtn.click(function () {
		jQuery('body,html').animate({
			scrollTop: 0
		}, 500);
		return false;
    });
});
