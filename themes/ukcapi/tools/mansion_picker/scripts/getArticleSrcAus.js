/**
 * Created by rbit on 10/8/14.
 */

/**
 * Get the content of selected article from News Aus
 *
 * @param document_root Reference to the current document context
 *
 * @returns Object
 */
function getArticleContentAus( document_root ) {
  var articleObj = {},
    articleId =  document.querySelector('link[rel="canonical"]').href.split('-').pop(),
    articleTitle = document_root.querySelector('title'),
    articleSummary = document_root.querySelector('meta[name="description"]'),
    articleUrl = document_root.querySelector('link[rel="canonical"]'),
    articleTags = document_root.querySelector('meta[name="keywords"]'),
    articlePubDate = document_root.querySelector('meta[name="article:publicationdate"]'),
    articleAuthor = document_root.querySelector('meta[name="article:author"]'),
    i;


  if (articleId !== null ) {
    // get the keywords, split and prefix them
    var keywords = articleTags.content.split(",", 5);
    for (i = 0; i < keywords.length; i++) {
      keywords[i] = 'origin-' + keywords[i].replace(/\s/g, '-');
    }
    keywords = keywords.join(",");

    articleObj.nau = {};
    articleObj.id = articleId;
    articleObj.originUrl = articleUrl.href;
    articleObj.title = articleTitle.text;
    articleObj.summary = articleSummary.content;
    articleObj.pubDate = articlePubDate.content;
    articleObj.author = articleAuthor.content;
    articleObj.keywords = keywords;
    articleObj.sourcePublication = 'NEWSAUS';
    articleObj.sourceCms = 'http://cdn.newsapi.com.au/newscontent/origin:fatwire.' + articleId + '?api_key=5gyhsa89szxhr7xksfqejegh';

    articleObj.action = 'picker_hook';

    articleObj.nau.featuredImg = document_root.querySelector('meta[property="og:image"]').content;
    // required parameters for News UK content
    articleObj.ascTnlCookie = '';
    articleObj.liveFyreTokenCookie = '';
    // required parameters for NYPost content
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
