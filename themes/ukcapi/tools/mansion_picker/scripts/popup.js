/**
 * Created by rbit on 8/4/14.
 */

/**
 * 
 * Add event listener for 'getSource' action request
 */
chrome.runtime.onMessage.addListener(function(request) 
{
  baseUrl = 'http://editorial.mansionglobal.com/wp-admin/admin-ajax.php'; // 'http://localhost:8080/wp-admin/admin-ajax.php'; // */ 'http://www.nc-mansion-cms.local/wp-admin/admin-ajax.php';
  devUrl  = 'http://mansion.local:8888/wp-admin/admin-ajax.php'; // http://www.nc-mansion-cms.local/wp-admin/admin-ajax.php';
  prodUrl = 'http://editorial.mansionglobal.com/wp-admin/admin-ajax.php'; //
	            
  if (request.action == "getSource") 
  {
    if (request.articleData.hasOwnProperty('error')) 
    {
      message.innerHTML = request.articleData.error;
      document.querySelector('#controls').style.display = 'none';
    } 
    else 
    {
      var approveBtn = document.querySelector('#btn-approve'),
          previewBtn = document.querySelector('#btn-preview'),
          articleStringHtml = '';
          
      articleStringHtml += '<h1 style="color:#ff202020;">NewsCorp: Article Picker v7.2</h1><hr/>';
      articleStringHtml += '<p><b>Do you want to pick this article?</b></p>';

      articleStringHtml += '<p><b>ID:</b>';
      articleStringHtml += '<span>' + request.articleData.id + '</span></p>';

      articleStringHtml += '<p><b><u>Title:</u></b><br/>';
      articleStringHtml += request.articleData.title + '</p>';

      articleStringHtml += '<p><b><u>Subtitle (Deck/Stand-first):</u></b><br/>';
      articleStringHtml += request.articleData.summary + '</p>';
      articleStringHtml += '<p class="keywords"><b><u>Derived Keywords:</u></b><br/>' + request.articleData.keywords + '</p>';
      
      articleStringHtml += '<p class="keywords"><b><u>Target CMS:</u></b><br/>(' + baseUrl + ')</p>';

      message.innerHTML = articleStringHtml;

	  previewBtn.addEventListener('click', function() 
      {
	      alert('preview not supported');
	  });
	  
      approveBtn.addEventListener('click', function() 
      {
        var loader = jQuery('.loader');
        loader.show();
        message.style.visibility = "hidden";

        jQuery.ajax({
          type: 'POST',
          url: baseUrl,
          data: request.articleData
        })
	          .done(function( res ) 
	          {
	            loader.hide();
	            message.style.visibility = "visible";
	
	            var resCms = JSON.parse(res);
	            message.innerHTML = '<p>' + resCms.msg + '</p>';
	
	            if (!resCms.duplicate) 
	            {
	              setTimeout(function() { window.close(); }, 1800);
	            }
	          })
	          .fail(function(res) 
	          {
	            message.innerHTML = '<p>Hm, something went wrong.</p>';
	          });

		      this.disabled = true;
      });
    }
  }
});

/**
 * Perform tab query on window load event
 */
function onWindowLoad() 
{
  var message = document.querySelector('#message'),
      cancelBtn = document.querySelector('#btn-cancel'),
      previewBtn = document.querySelector('#btn-preview');

  cancelBtn.addEventListener('click', function() 
  {
    window.close();
  });

  previewBtn.addEventListener('click', function() 
  {
  	console.log('Picker Preview', '...');
    window.close();
  });

  chrome.tabs.query( {active: true, currentWindow: true}, function(arrayOfTabs) 
  {
    var activeTabUrl = arrayOfTabs[0].url;

    if(activeTabUrl.indexOf('online.wsj.com') >= 0 || activeTabUrl.indexOf('www.wsj.com') >= 0 || activeTabUrl.indexOf('lat.wsj.com') >= 0 ) 
    {
      processPublicationArticle( 'scripts/getArticleSrcWsj.js' );
    } 
    else if ( activeTabUrl.indexOf('news.com.au') >= 0 ) 
    {
      processPublicationArticle( 'scripts/getArticleSrcAus.js' );
    } 
    else if ( activeTabUrl.indexOf('staging-thetimes.co.uk') >= 0 || activeTabUrl.indexOf('thetimes.co.uk') >= 0 ) 
    {
      processPublicationArticle( 'scripts/getArticleSrcUk.js' );
    } 
    else if ( activeTabUrl.indexOf('nypost.com/') >= 0 ) 
    {
      processPublicationArticle( 'scripts/getArticleSrcNyPost.js' );
    } 
    else 
    {
      var erMsg = '<h3 style="padding:10px;">The Article Picker cannot detect a valid article</h3><hr/><p> Please navigate to an appropriate Article page within a supported publication.</p><p><u>Supported Publications:</u></p><ul>';
      erMsg = erMsg + '<li>online.wsj.com</li>';
      erMsg = erMsg + '<li>www.wsj.com</li>';
      erMsg = erMsg + '<li>lat.wsj.com</li>';
      erMsg = erMsg + '<li>news.com.au</li>';
      erMsg = erMsg + '<li>staging-thetimes.co.uk</li>';
      erMsg = erMsg + '<li>thetimes.co.uk</li>';
      erMsg = erMsg + '<li>nypost.com</li><ul>';
      message.innerHTML = erMsg;
      document.querySelector('#bdy').style.backgroundColor = '#f0f0f0';
      document.querySelector('#controls').style.display = 'none';
    }
  });
}

/**
 * Get article information depending on the source publication. The script will interrogate the page according 
 * to its well known structure. It then sends a message to the extension (i.e.this code) which is trapped and processed
 * above.
 *
 * @param script Filename of the executable script
 *
 * return void
 */
function processPublicationArticle( script ) 
{
	//
	// This code injects the script file into the tab page where it will have access to the DOM 
	//
  	chrome.tabs.executeScript(null, { file: script }, function() 
  	{
	  	//
		// If you try and inject into an extensions page or the webstore/NTP you'll get an error
		//
		if (chrome.extension.lastError) 
		{
		message.innerText = 'There was an error injecting script : \n' + chrome.extension.lastError.message;
		}
    });
}

chrome.commands.onCommand.addListener(function(command) 
{
	console.log('Picker Command:', command);
});


chrome.contextMenus.onClicked.addListener(function (info, tab)
{
	console.log('Context Command:');	
});

window.onload = onWindowLoad;

