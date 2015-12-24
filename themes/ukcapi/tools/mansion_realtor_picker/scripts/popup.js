/**
 * Created by rbit on 8/4/14.
 */

/**
 * Add event listener for 'getSource' action request
 */
chrome.runtime.onMessage.addListener(function(request) {
  if (request.action == "getSource") {

    if (request.articleData.hasOwnProperty('error')) {
      message.innerHTML = request.articleData.error;
      document.querySelector('#controls').style.display = 'none';
    } else {
      var approveBtn = document.querySelector('#btn-approve'),
          articleStringHtml = '';

      articleStringHtml += '<h2>Realtor Article Picker</h2>';
      articleStringHtml += '<p>Do you want to pick this article?</p>';
      articleStringHtml += '<h1>' + request.articleData.title + '</h1>';
      articleStringHtml += '<span>' + request.articleData.id + '</span>';
      articleStringHtml += '<p>' + request.articleData.summary + '</p>';
      articleStringHtml += '<p class="keywords"><b>Keywords: </b>' + request.articleData.keywords + '</p>';

      message.innerHTML = articleStringHtml;

      approveBtn.addEventListener('click', function() {
        var loader = jQuery('.loader'),
            use_live_site = true;
            
        var baseUrl = use_live_site ? 'http://54.77.195.33/wp-admin/admin-ajax.php' : 'http://www.nc-mansion-cms.local/wp-admin/admin-ajax.php';

        loader.show();
        message.style.visibility = "hidden";

        jQuery.ajax({
          type: 'POST',
          url: baseUrl,
          data: request.articleData
        })
          .done(function( res ) {
            loader.hide();
            message.style.visibility = "visible";

            var resCms = JSON.parse(res);
            message.innerHTML = '<p>' + resCms.msg + '</p>';
            jQuery('#btn-cancel').text('Close');

            if (!resCms.duplicate) {
              setTimeout(function() {
                window.close();
              }, 1800);
            }
          })
          .fail(function(res) {
            message.innerHTML = '<p>Hmm, something went wrong. Please try again.</p>';
          });

        this.disabled = true;
      });
    }
  }
});

/**
 * Perform tab query on window load event
 */
function onWindowLoad() {
  var message = document.querySelector('#message'),
      cancelBtn = document.querySelector('#btn-cancel');

  cancelBtn.addEventListener('click', function() {
    window.close();
  });

  chrome.tabs.query( {active: true, currentWindow: true}, function(arrayOfTabs) {
    var activeTabUrl = arrayOfTabs[0].url;

    if ( activeTabUrl.indexOf('online.wsj.com') >= 0 ) {
      getArticle( 'scripts/getArticleSrcWsj.js' );
    } else if ( activeTabUrl.indexOf('news.com.au') >= 0 ) {
      getArticle( 'scripts/getArticleSrcAus.js' );
    } else if ( activeTabUrl.indexOf('staging-thetimes.co.uk') >= 0 || activeTabUrl.indexOf('thetimes.co.uk') >= 0 ) {
      getArticle( 'scripts/getArticleSrcUk.js' );
    } else if ( activeTabUrl.indexOf('nypost.com/') >= 0 ) {
      getArticle( 'scripts/getArticleSrcNyPost.js' );
    } else {
      message.innerHTML = '<p>The Realtor Article Picker can not detect a valid article format. <br/><br/> Please navigate to appropriate page.</p>';
      document.querySelector('#controls').style.display = 'none';
    }
  });
}

/**
 * Get article information depending on the source publication
 *
 * @param script Filename of the executable script
 *
 * return void
 */
function getArticle( script ) {
  chrome.tabs.executeScript(null, { file: script },
    function() {
      // If you try and inject into an extensions page or the webstore/NTP you'll get an error
      if (chrome.extension.lastError) {
        message.innerText = 'There was an error injecting script : \n' + chrome.extension.lastError.message;
      }
    });
}

window.onload = onWindowLoad;