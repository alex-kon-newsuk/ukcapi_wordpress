/*global $:false */

/**
 * Created by rbit on 8/25/14.
 */

(function($) {
  'use strict';

  $('.tags-controller').click( function(event) {
    var tagsContainer = $(this).prev(),
        tagsCtrl = $(this),
        tagsWrapH = tagsContainer.find('.tags-wrap').height();

    if (tagsContainer.hasClass('expanded')) {
      tagsContainer.animate({height: '47px'}, 600, function() {
        tagsContainer.removeClass('expanded');
        tagsCtrl.text('View all tags');
      });
    } else {
      tagsContainer.addClass('expanded');
      tagsContainer.animate({height: tagsWrapH + 24 + 'px'}, 600, function() {
        tagsContainer.addClass('expanded');
        tagsCtrl.text('Minimize');
      });
    }
  });

  // Click handler for individual article items tags controller
  $(document).on('click', '.item-tags-ctrl', function(event) {
    var tagsContainer = $(this).parent().parent().find('.tags-wrap'),
      tagsCtrl = $(this),
      tagsWrapH = tagsContainer.find('.tags').height();

    if (tagsContainer.hasClass('expanded')) {
      tagsContainer.animate({height: '25px'}, 600, function() {
        tagsContainer.removeClass('expanded');
        tagsCtrl.text('View all tags');
      });
    } else {
      tagsContainer.addClass('expanded');
      tagsContainer.animate({height: tagsWrapH + 'px'}, 600, function() {
        tagsContainer.addClass('expanded');
        tagsCtrl.text('Minimize');
      });
    }
  });

  $(".rslides").responsiveSlides({
    auto: false,             // Boolean: Animate automatically, true or false
    speed: 500,            // Integer: Speed of the transition, in milliseconds
    timeout: 4000,          // Integer: Time between slide transitions, in milliseconds
    pager: true,           // Boolean: Show pager, true or false
    nav: true,             // Boolean: Show navigation, true or false
    random: false,          // Boolean: Randomize the order of the slides, true or false
    pause: false,           // Boolean: Pause on hover, true or false
    pauseControls: true,    // Boolean: Pause when hovering controls, true or false
    prevText: "Previous",   // String: Text for the "previous" button
    nextText: "Next",       // String: Text for the "next" button
    maxwidth: "",           // Integer: Max-width of the slideshow, in pixels
    navContainer: "",       // Selector: Where controls should be appended to, default is after the 'ul'
    manualControls: "",     // Selector: Declare custom pager navigation
    namespace: "centered-btns",   // String: Change the default namespace used
    before: function(){},   // Function: Before callback
    after: function(){}     // Function: After callback
  });

  // expand / collapse drafts and blacklisted sections
  $('.btn-collapse').on('click', function() {
    var btnCollapse = $(this),
        dataTarget = $(this).attr('data-target'),
        publishedArticles = $('.article-tags[data-status="ready"]').parent(),
        collapsedEl = $('.collapsed'),
        collapsedCount = collapsedEl.length,
        collapsedTarget = collapsedEl.children('.btn-collapse').attr('data-target'),
        articleWrap = $(this).next('.articles-wrap');

    console.log(collapsedTarget);

    articleWrap.toggle('fast', function() {
      btnCollapse.parent().toggleClass('collapsed');
    });

    if (collapsedCount === 0 || collapsedCount === 2) {
      publishedArticles.width('34%');
    } else if (collapsedCount === 1) {
      if (collapsedTarget === dataTarget) {
        publishedArticles.width('18%');
      } else {
        setTimeout(function() {
          publishedArticles.width('50%');
        }, 300);
      }
    }
  });

  // article placement tab navigation
  $('.articles-group-tabs a').on('click', function(event) {
    event.preventDefault();
    $(this).parent().addClass('active');
    $(this).parent().siblings().removeClass('active');

    var tab = $(this).attr("href");
    $('.article-group-content').not(tab).css('display', 'none');
    $(tab).fadeIn();
  });

}(jQuery));