// JavaScript Document

/* Message */
$(function(){
    $(window).scroll(function(){
        $('.headerFixed').each(function(){
            var elemPos = $(this).offset().top;
            var scroll = $(window).scrollTop();
            var windowHeight = $(window).height();
            if (scroll > elemPos - windowHeight + 10){
               $(this).addClass('scrollin');
            }else {
               $(this).removeClass('scrollin');
			}
        });
    });
});