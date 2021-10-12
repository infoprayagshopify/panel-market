$(window).load(function(){
  //Active menu
   
        var activeLinks = location.pathname;
  		
  		if (activeLinks == '/'){
         	
		  $(".home-link").addClass('active');
        }else{
          $('nav a[href^="/' + location.pathname.split("/")[1] + '"]').closest('li').addClass('active');
        }
});

$(function() {
  var loc = window.location.href; // returns the full URL
  if(/massorder/.test(loc)) {
    $(".home-link").addClass('active');
  }
});


$(document).ready(function () {
    var letCollapseWidth = false,
        paddingValue = 30,
        sumWidth = $('.navbar-right-block').width() + $('.navbar-left-block').width() + $('.navbar-brand').width() + paddingValue;

    $(window).on('resize', function () {
        navbarResizerFunc();
    });

    var navbarResizerFunc = function navbarResizerFunc() {
        if (sumWidth <= $(window).width()) {
            if (letCollapseWidth && letCollapseWidth <= $(window).width()) {
                $('#navbar').addClass('navbar-collapse');
                $('#navbar').removeClass('navbar-collapsed');
                $('nav').removeClass('navbar-collapsed-before');
                letCollapseWidth = false;
            }
        } else {
            $('#navbar').removeClass('navbar-collapse');
            $('#navbar').addClass('navbar-collapsed');
            $('nav').addClass('navbar-collapsed-before');
            letCollapseWidth = $(window).width();
        }
    };

    if ($(window).width() >= 768) {
        navbarResizerFunc();
    }
  
  	//Changed Header class on signup page
  	$(function() {
      var loc = window.location.href; // returns the full URL
      if(/signup/.test(loc)) {
        $('header').addClass('bg-blue');
      }
      if(/services/.test(loc)) {
        $('header').addClass('bg-blue');
      }
      if(/faq/.test(loc)) {
        $('header').addClass('bg-blue');
      }
      if(/api/.test(loc)) {
        $('header').addClass('bg-blue');
      }
      if(/terms/.test(loc)) {
        $('header').addClass('bg-blue');
      }
      if(/resetpassword/.test(loc)) {
        $('header').addClass('bg-blue');
      }
    });
  
  (function ($) {
    "use strict";
    // Auto-scroll
    $('#testimonials').carousel({
      interval: 5000
    });

    // Control buttons
    $('.next').click(function () {
      $('.carousel').carousel('next');
      return false;
    });
    $('.prev').click(function () {
      $('.carousel').carousel('prev');
      return false;
    });

    // On carousel scroll
    $("#testimonials").on("slide.bs.carousel", function (e) {
      var $e = $(e.relatedTarget);
      var idx = $e.index();
      var itemsPerSlide = 3;
      var totalItems = $(".carousel-item").length;
      if (idx >= totalItems - (itemsPerSlide - 1)) {
        var it = itemsPerSlide -
            (totalItems - idx);
        for (var i = 0; i < it; i++) {
          // append slides to end 
          if (e.direction == "left") {
            $(
              ".carousel-item").eq(i).appendTo(".carousel-inner");
          } else {
            $(".carousel-item").eq(0).appendTo(".carousel-inner");
          }
        }
      }
    });
  })
  (jQuery);
  
  
  
  
      
});

$(window).on('load resize', function () {
  var divHeight = $('.wave img').height(); 
  $('.wave').css('margin-top', -divHeight/1.4);
  $('.top-banner').css('padding-bottom', divHeight/1.4);
  $('.compare-panel').css('margin-bottom', divHeight/2.50);
  $('footer').css('margin-top', divHeight/4);
});

$(window).scroll(function(){
  var scroll = $(window).scrollTop();
  if (scroll >= 500) {
    $('header').addClass('position-fixed');
  }else{
    $('header').removeClass('position-fixed');
  }

});


(function ($) {
  $(function () {
    $(document).off('click.bs.tab.data-api', '[data-hover="tab"]');
    $(document).on('mouseenter.bs.tab.data-api', '[data-hover="tab"]', function () {
      $(this).tab('show');
    });
  });
})(jQuery);