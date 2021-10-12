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
});

var didScroll, lastScrollTop = 0,
    delta = 5,
    navbarHeight = $(".notAuth .navbar").outerHeight();
$(window).scroll(function () {
    didScroll = true;
});
setInterval(function () {
    if (didScroll) {
        hasScrolled();
        didScroll = false;
    }
}, 250);

function hasScrolled() {
    var scrollTop = $(this).scrollTop();
    if (Math.abs(lastScrollTop - scrollTop) <= delta) return;
    if (scrollTop > lastScrollTop && scrollTop > navbarHeight) {
        $(".notAuth .navbar").removeClass("nav-down").addClass("nav-up");
    } else if (scrollTop + $(window).height() < $(document).height()) {
        $(".notAuth .navbar").removeClass("nav-up").addClass("nav-down");
    }
    lastScrollTop = scrollTop;
}
$(window).scroll(function () {
    var scrollTop = $(window).scrollTop();
    if (scrollTop >= 500) {
        $(".notAuth .navbar").addClass("position-fixed");
    } else {
        $(".notAuth .navbar").removeClass("position-fixed");
    }
});