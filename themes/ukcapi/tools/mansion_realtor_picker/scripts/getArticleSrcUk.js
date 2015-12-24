/**
 * Created by rbit on 10/14/14.
 */

/**
 * Get the content of selected article from News UK
 *
 * @param document_root Reference to the current document context
 *
 * @returns Object
 */
function getArticleContentAus( document_root ) {
  // Define required variables in case of content changes
  var articleObj = {},
    articleId =  document.querySelector('meta[name="dashboard_article_id"]'),
    articleTitle = document_root.querySelector('meta[name="dashboard_header"]'),
    articleSummary = document_root.querySelector('meta[name="description"]'),
    articleUrl = document_root.querySelector('link[rel="canonical"]'),
    articlePubDate = document_root.querySelector('meta[name="dashboard_published_date"]'),
    articleAuthor = document_root.querySelector('.f-author');

  // Create the article object, which will be send to the CMS
  if (articleId !== null ) {
    articleObj.id = articleId.content;
    articleObj.originUrl = articleUrl.href;
    articleObj.title = articleTitle.content;
    articleObj.summary = articleSummary.content;
    articleObj.pubDate = articlePubDate.content;
    articleObj.author = articleAuthor.textContent;
    articleObj.keywords = '';
    articleObj.sourcePublication = 'NEWSUK';
    articleObj.sourceCms = articleUrl.href + '/readpane=3';

    articleObj.action = 'realtor_picker_hook';

    // required parameters for News UK content
    articleObj.ascTnlCookie = document_root.cookie.replace(/(?:(?:^|.*;\s*)acs_tnl\s*\=\s*([^;]*).*$)|^.*$/, "$1");
    articleObj.liveFyreTokenCookie = document_root.cookie.replace(/(?:(?:^|.*;\s*)livefyre_token\s*\=\s*([^;]*).*$)|^.*$/, "$1");

    articleObj.nyp = null;
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
  articleData: getArticleContentAus( document )
});