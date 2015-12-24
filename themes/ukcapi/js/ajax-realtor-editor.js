/**
 * Created by dsmart on 11/19/14.
 * Realtor Editor functionality
 */

(function($) {
  
  $(document).ready(function() {
    
    $('input.realtor_article_status').on('change', function(event) {
      
      var articleStatus = this.value,
          $articleItem  = $(event.target).parents('.article-item'),
          articleId     = $articleItem.data('article-id');
      
      // Prevent default action - opening tag page
      if (event.preventDefault) {
        event.preventDefault();
      } else {
        event.returnValue = false;
      }
  
      var data = {
        action: 'update_realtor_article_status', // function to execute
        ms_re_nonce: ms_re_vars.ms_re_nonce, // wp_nonce
        article_id: articleId,
        article_status: articleStatus
      };
      
      $articleItem.addClass('updating');
  
      $.post( ms_re_vars.ajax_url, data, function(response) {
  
        if (response) {
          // update updating notifier
          $articleItem.removeClass('updating');
          
          if (articleStatus == 'Allowed') {
            $articleItem.removeClass('article-denied');
            $articleItem.addClass('article-allowed');
          }
          else if (articleStatus == 'Denied') {
            $articleItem.removeClass('article-allowed');
            $articleItem.addClass('article-denied');
          }
          else {
            $articleItem.removeClass('article-allowed');
            $articleItem.removeClass('article-denied');
          }
        }
      });
    });
    
  });
})(jQuery);