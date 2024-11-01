jQuery(document).ready(function ($) {
    $.dnt_syap.document_ready_init();
});

(function ($) {
    $.dnt_syap = {
        init: function () {
            $(document).on('click', '.dnt-archive-posts .archive-posts-nav a', function () {
                return false;
            });
        },
        document_ready_init: function () {
            $('.dnt-archive-posts').each(function () {
                var $dnt_archive_posts_root = $(this);

                // year filter
                $dnt_archive_posts_root.on('click', '.archive-posts-nav a', function () {
                    var $a_tag = $(this);

                    // update current filter year
                    $.dnt_syap.update_current_filter_year($dnt_archive_posts_root, $a_tag.parent().attr('data-year'));

                    //
                    $.dnt_syap.ajax_load_posts($dnt_archive_posts_root, function (response) {
                        $dnt_archive_posts_root.find('.posts-pagination a').remove();
                        var $posts_pagination = $dnt_archive_posts_root.find('.posts-pagination');
                        var max_page = parseInt(response.max_page);
                        //console.log(response);
                        for (var page_index = 1; page_index <= max_page; page_index++) {
                            if (page_index == 1) {
                                $posts_pagination.append('<a href="javascript:void(0);" class="current" data-page="' + page_index + '">' + page_index + '</a>');
                            } else {
                                $posts_pagination.append('<a href="javascript:void(0);" data-page="' + page_index + '">' + page_index + '</a>');
                            }
                        }
                    });
                    return false;
                });

                // pagination
                $dnt_archive_posts_root.on('click', '.posts-pagination a', function () {
                    var $a_tag = $(this);

                    // update pagination
                    $a_tag.parent().find('a').removeClass('current');
                    $a_tag.addClass('current');
                    $dnt_archive_posts_root.find('input[name="dnt_page"]').attr('value', $a_tag.attr('data-page'));

                    //
                    $.dnt_syap.ajax_load_posts($dnt_archive_posts_root);

                });
            });
        },
        update_current_filter_year: function ($dnt_archive_posts_root, year) {
            $dnt_archive_posts_root.attr('data-current-year-filter', year);
            $dnt_archive_posts_root.find('.posts-pagination input[name="dnt_page"]').attr('value', '1');
            $($dnt_archive_posts_root).find('.posts-pagination input[name="dnt_year"]').each(function () {
                $(this).attr('value', year);
            });
        },
        ajax_load_posts: function ($dnt_archive_posts_root, callback) {
            $dnt_archive_posts_root.find('.posts-list').addClass('loading');

            //
            var form_data = new FormData();
            form_data.append('test', 1);
            form_data.append('action', 'dnt_syap_load_posts');
            $dnt_archive_posts_root.find('.posts-pagination input[type="hidden"]').each(function () {
                form_data.append($(this).attr('name'), $(this).attr('value'));
            });

            //
            var $posts_list = $dnt_archive_posts_root.find('.posts-list');

            //
            $.ajax({
                url: my_ajax_object.ajax_url,
                data: form_data,
                type: 'POST',
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (response) {
                    //console.log(['success', response]);
                    if (response.success == true) {
                        $posts_list.html(response.content);
                    }
                }, error: function (response) {
                    console.log(['error', response]);
                }, complete: function (response) {
                    //console.log(['complete', response]);
                    $posts_list.removeClass('loading');

                    //
                    if (typeof callback == 'function') {
                        callback(response.responseJSON);
                    }
                }
            });


        }
    };

    $.dnt_syap.init();

})(jQuery);