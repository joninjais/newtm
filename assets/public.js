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

/* ─── Shortcode AJAX Pagination ─── */
(function($) {
    $(document).on('click', '.newtm-shortcode-pagination .newtm-pg-btn', function() {
        var $btn  = $(this);
        var $pg   = $btn.closest('.newtm-shortcode-pagination');
        var $wrap = $pg.closest('.newtm-news-section');
        var page  = parseInt($btn.data('page'));

        if (!page || $btn.hasClass('active')) return;

        var category     = $pg.data('category');
        var limit        = $pg.data('limit');
        var titleLength  = $pg.data('title-length');
        var total        = parseInt($pg.data('total'));

        $wrap.addClass('newtm-loading');

        $.post(newtmAjax.url, {
            action:       'newtm_load_page',
            nonce:        newtmAjax.nonce,
            category:     category,
            paged:        page,
            limit:        limit,
            title_length: titleLength
        }, function(res) {
            $wrap.removeClass('newtm-loading');
            if (res.success && res.data.html) {
                var $tmp = $('<div>').html(res.data.html);
                // อัพเดต table
                $wrap.find('table').replaceWith($tmp.find('table'));
                // อัพเดต pagination
                var $newPg = $tmp.find('.newtm-shortcode-pagination');
                if ($newPg.length) {
                    $pg.replaceWith($newPg);
                } else {
                    $pg.remove();
                }
            }
        });
    });
}(jQuery));
