/**
 * Created by rbit on 11/10/14.
 */

/**
 * Get the content of selected article from NY Post
 *
 * @param document_root Reference to the current document context
 *
 * @returns Object
 */
function getArticleContentNyPost( document_root ) {
  // Define required variables in case of content changes
  var articleObj = {},
    articleBody = document_root.querySelectorAll('.entry-content > p'),
    articleMeta = JSON.parse(document_root.querySelector('meta[name="parsely-page"]').content),
    i;

  // Create the article object, which will be send to the CMS
  if (articleMeta.post_id !== null ) {
    // get the keywords, split and prefix them
    var keywords = document_root.querySelector('meta[name="sailthru.tags"]').content.split(",", 5);
    for (i = 0; i < keywords.length; i++) {
      keywords[i] = 'origin-' + keywords[i].replace(/\s/g, '-');
    }

    articleObj.id = articleMeta.post_id;
    articleObj.originUrl = articleMeta.link;
    articleObj.title = articleMeta.title;
    articleObj.summary = document_root.querySelector('meta[property="og:description"]').content;
    articleObj.pubDate = articleMeta.pub_date;
    articleObj.author = articleMeta.author[0];
    articleObj.keywords = keywords.join(",");
    articleObj.sourcePublication = 'NYPOST';
    articleObj.sourceCms = articleMeta.link + '/feed';

    articleObj.action = 'picker_hook';

    // additional parameters for NYPost content as there isn't any provided API mechanism
    articleObj.nyp = {};
    articleObj.nyp.body = '';
    for (i = 0; i < articleBody.length; i++) {
      articleObj.nyp.body += '<p>' + articleBody[i].innerText + '</p>';
    }
    articleObj.nyp.imgThumb = document_root.querySelector('meta[name="sailthru.image.thumb"]').content;
    articleObj.nyp.imgHero = document_root.querySelector('meta[name="sailthru.image.full"]').content;
    articleObj.nyp.imgCaption = document_root.querySelector('.wp-caption-text.featured').innerText;
    var imgCredit = document_root.querySelector('.wp-caption-text.featured span.credit').innerText;
    if (imgCredit !== undefined) {
      imgCredit = imgCredit.substring(7, imgCredit.length);
    }
    articleObj.nyp.imgCredit = imgCredit || '';

    // required parameter for News AU content
    articleObj.nau = null;
    // required parameters for News UK content
    articleObj.ascTnlCookie = '';
    articleObj.liveFyreTokenCookie = '';
  } else {
    articleObj.error = '<p>The Article Picker can not detect a valid article format. <br/><br/> Please navigate to appropriate page.</p>';
  }

  return articleObj;
}

/**
 * Send the article object to the main extension file
 */
chrome.runtime.sendMessage({
  action: "getSource",
  articleData: getArticleContentNyPost( document )
});