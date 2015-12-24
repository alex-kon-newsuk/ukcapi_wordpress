/**
 * Get the content of selected article from WSJ
 *
 * @param document_root Reference to the current document context
 *
 * @returns Object
 */
function getArticleContent( document_root ) 
{
  var articleObj = {},
      articleId = document_root.querySelector('meta[name="article.id"]'),
      articleTitle = document_root.querySelector('meta[name="article.headline"]'),
      articleSummary = document_root.querySelector('meta[name="article.summary"]'),
      articleUrl = document_root.querySelector('link[rel="canonical"]'),
      articleTags = document_root.querySelector('meta[name="keywords"]'),
      articlePubDate = document_root.querySelector('meta[name="article.published"]'),
      articleAuthor = document_root.querySelector('meta[name="author"]'),
      contentType = document_root.querySelector('meta[name="page.content.type"]'),
      i;

  if (articleId !== null) 
  {
    // get the keywords, split and prefix them
    var keywords = articleTags.content.split(",", 5);
    for (i = 0; i < keywords.length; i++) 
    {
      keywords[i] = 'origin-' + keywords[i].replace(/\s/g, '-');
    }
    keywords = keywords.join(",");

    articleObj.id = articleId.content;
    articleObj.originUrl = articleUrl.href;
    articleObj.title = articleTitle.content;
    articleObj.summary = articleSummary.content;
    articleObj.pubDate = articlePubDate.content;
    if (contentType.content === 'slideshow') 
    {
		articleObj.author = 'WSJ';
    } 
    else 
    {
		articleObj.author = articleAuthor.content;
    }

    articleObj.keywords = keywords;
    articleObj.sourcePublication = 'WSJ';
    articleObj.sourceCms = 'http://online.wsj.com/xml/djml/' + articleId.content + '.xml';

    articleObj.action = 'picker_hook';

    // required parameters for News UK content
    articleObj.ascTnlCookie = '';
    articleObj.liveFyreTokenCookie = '';
    // required parameters for NYPost content
    articleObj.nyp = null;
    // required parameter for News AU content
    articleObj.nau = null;
  } 
  else 
  {
    articleObj.error = '<p>The Article Picker can not detect a valid article format. <br/><br/> Please navigate to appropriate page.</p>';
  }

  return articleObj;
}

/**
 * Send the article object to the main extension file
 */
chrome.runtime.sendMessage({
  action: "getSource",
  articleData: getArticleContent( document )
});

