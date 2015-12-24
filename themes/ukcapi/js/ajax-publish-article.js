/**
 * Created by rbit on 10/2/14.
 */

(function($){
  'use strict';

  $(document).on('click', '.article-body button[data-env]', function() {
    var _this = $(this);

    _this.html('<span class="ajax-loader"></span>');

    var data = {
      action: 'publish_article',
      target_env: _this.data('env'),
      ms_pa_nonce: ms_pa_vars.ms_pa_nonce, // wp_nonce,
      article_id: _this.parent().parent().parent().parent().data('article-id')
    };

    $.post( ms_pa_vars.ajax_url, data, function(response) {
      if (response) {

        var output = JSON.parse(response);

        if (output.httpCode === 201)
        {
          if (output.env === 'Production')
          {
            _this.text('Re-publish to Production').css({'background-color': '#317DC7', color: '#fff', 'border-color': '#317DC7'});
            _this.parent().parent().find('.article-update p:nth-child(1)').html('Last on: <span>' + output.lastPublished + '</span>');
          }
          else if (output.env === 'live')
          {
            _this.text('Re-publish to Staging').css({'background-color': '#317DC7', color: '#fff', 'border-color': '#317DC7'});
            _this.parent().parent().find('.article-update p:nth-child(2)').html('Last on: <span>' + output.lastPublished + '</span>');
          }
          else
          {
            _this.text('Re-publish to Edge').css({'background-color': '#317DC7', color: '#fff', 'border-color': '#317DC7'});
            _this.parent().parent().find('.article-update p:nth-child(3)').html('Last on: <span>' + output.lastPublished + '</span>')
          }
        }
        else
        {
          _this.text('Error! Try again').css({'background-color': '#d85151', color: '#fff', 'border-color': '#d85151'});
        }
      }
    });
  });
})(jQuery);