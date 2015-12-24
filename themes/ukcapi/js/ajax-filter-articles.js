/**
 * Created by rbit on 8/19/14.
 */

(function($) {
  $('.tag-filter').on('click', function(event) {
    var taggedArticles,
        articleStatus = $(this).parents('.article-tags').data('status'),
        taxonomyTerm = '',
        selectedTaxonomy = $(this).attr('title'),
        defaultBgColor = '#85A8CA',
        taxonomyPrefix = selectedTaxonomy.split('-')[1];

    $(this).closest('.grid__col').find('.tags-wrap').children().css('background-color', defaultBgColor);
    $(this).css('background-color', '#CA8585');

    if (typeof $(this).attr('data-tag-all') !== 'undefined') {
      selectedTaxonomy = '';
    }
    switch (taxonomyPrefix) {
      case 'ml':
        taxonomyTerm = 'location_tag';
        break;
      case 'ls':
        taxonomyTerm = 'lifestyle_tag';
        break;
      case 'fe':
        taxonomyTerm = 'featured_tag';
        break;
    }
    switch (articleStatus) {
      case 'draft':
        taggedArticles = $('.tagged-articles-draft');
        taxonomyTerm = 'origin_tag';
        break;
      case 'ready':
        taggedArticles = $('.tagged-articles-ready');
        break;
    }

    // Prevent default action - opening tag page
    if (event.preventDefault) {
      event.preventDefault();
    } else {
      event.returnValue = false;
    }

    // After user click on tag, fade out list of articles
    taggedArticles.fadeOut();

    var data = {
      action: 'filter_articles', // function to execute
      ms_afa_nonce: ms_afa_vars.ms_afa_nonce, // wp_nonce
      taxonomy: selectedTaxonomy, // selected tag
      taxonomy_term: taxonomyTerm, // taxonomy term
      category: articleStatus
    };

    $.post( ms_afa_vars.ajax_url, data, function(response) {

      if (response) {
        // Display articles on page
        taggedArticles.html(response);
        // Restore div visibility
        taggedArticles.fadeIn();
      }
    });
  });
  
  // date query radio buttons
  $('input[type=radio][name=date_query]').click(function(evt) {
    $(this).parents('form').submit();
  });
})(jQuery);