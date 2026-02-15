// Public JavaScript
jQuery(document).ready(function($) {
    // Add smooth scrolling
    $(document).on('click', 'a[href^="#"]', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if(target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
});
