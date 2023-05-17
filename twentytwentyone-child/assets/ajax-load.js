jQuery(function($) {
    var page = 1;
    var loading = false;
    var type = '';
    var button = $('#load-more');
    var loopWrapper = $('.loop-wrapper');
    var ajaxurl = customData.ajaxurl;
    $(document).on('click', '#load-more', function(e) {
        e.preventDefault();

        if (loading) {
            return;
        }

        loading = true;
        type = button.data('type');
        button.text('Loading...');

        var data = {
            action: 'load_more_posts',
            type: type,
            page: page + 1,
        };

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.indexOf('no_more_posts') !== -1) {
                    response = response.replace('no_more_posts', '');
                    button.attr('disabled', 'disabled');
                    button.fadeOut();
                }
                if (response && response.indexOf('no_more_posts') === -1) {
                    var newPosts = $(response).hide();
                    loopWrapper.append(newPosts);
                    newPosts.fadeIn();
                    page++;
                    button.text('Load More');
                }

                loading = false;
            }
        });
    });
});