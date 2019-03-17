/* 
Author : Fidan Bajrami
Version : 1.0

JS Table Of Content 
======================
1. Mobile Menu
2. Scroll to Top

*/ 
(function($) {

    "use strict";

/*=============================================
	1. Mobile Menu
===============================================*/ 

    var mobile_div = $(".mobile-menu nav");

    mobile_div.meanmenu();

/*=============================================
	2. Scroll to Top
===============================================*/ 

    function scrolltop() {


        var wind = $(window);

        wind.on("scroll", function() {

            var scrollTop = $(window).scrollTop();

            if (scrollTop >= 500) {

                $(".scroll-top").fadeIn("slow");

            } else {

                $(".scroll-top").fadeOut("slow");
            }

        });

        $(".scroll-top").on("click", function() {

            var bodyTop = $("html, body");

            bodyTop.animate({
                scrollTop: 0
            }, 800, "easeOutCubic");
        });

    }
    scrolltop();



}(jQuery));