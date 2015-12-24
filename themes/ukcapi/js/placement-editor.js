/**
 * Created by rbit on 9/26/14.
 */

(function($) {
  'use strict';

  var edgeEnvBaseUrl = 'http://mansion-global-edge.substantial.com/api',
      stagingEnvBaseUrl = 'http://mansion-global.substantial.com/api',
      prod2EnvBaseUrl = 'http://pub.production-web2.mansion-global.mansion.virginia.onservo.com/api',
      prodEnvBaseUrl = 'http://pub.production-web.mansion-global.mansion.virginia.onservo.com/api',
      salesdemoEnvBaseUrl = 'http://pub.salesdemo-web.mansion-global.mansion.virginia.onservo.com/api',
      usertestEnvBaseUrl = 'http://pub.usertest-web.mansion-global.mansion.virginia.onservo.com/api',
      currentEnvName = 'production',
      currentSlotsCount = 0,
      currentEnvBaseUrl = prodEnvBaseUrl,
      currentArticleType = 'articles',
      currentLocale = 'ENGLISH',
      currentLocaleIsoCode = 'en-us',
      currentLocaleIcon,
      currentPlacementGroup = 1,
      currentPlacementSlotNumber = -1,
      currentExistingPlacementId = -1;


  //------------------------------------------------------------------------------------------------------------
  //
  //
  //------------------------------------------------------------------------------------------------------------
  function loadContentGroupView(groupId)
  {
    alert(groupId);
  }


  //------------------------------------------------------------------------------------------------------------
  //
  //
  //------------------------------------------------------------------------------------------------------------
  function clearContentGroupsViews()
  {
    var placementGroupButtons = $('.main-navigation > .articles-group-tabs'),
        placementItemsWrap = $('.placement-section > .placement-items');

    placementItemsWrap.html('<span>loading ...</span>');
    placementGroupButtons.html('<span>loading ......</span>');
  }


  //------------------------------------------------------------------------------------------------------------
  //
  //
  //------------------------------------------------------------------------------------------------------------
  function getAllContentGroups(envBaseUrl)
  {
    var data = {
      action: 'get_content_groups',
      url: envBaseUrl + '/content_groups/?page_size=100',
      env: currentEnvName,
      ms_afa_nonce: ms_afa_vars.ms_afa_nonce
    };

    $.post( ms_afa_vars.ajax_url, data, function(res)
    {
      var contentGroups = JSON.parse(res);
      var i = contentGroups.content_groups[0];
      var htmlButton = '';

      for (var idx = 0 ; idx < contentGroups.content_groups.length ; idx++)
      {
        if(contentGroups.content_groups[idx].hidden == 'false')
        {
          htmlButton += '<li><button style="width:190px;border:solid 1px #808080;padding:10px;margin-left:10px;margin-right:10px;" class="placement-group-link" '
          + ' data="' + contentGroups.content_groups[idx].id + '" '
          + ' language="' + contentGroups.content_groups[idx].language + '" '
          + ' language-isocode="' + contentGroups.content_groups[idx].iso_locale + '" '
          + ' language-icon="' + contentGroups.content_groups[idx].language_icon + '" '
          + ' slotcount="' + contentGroups.content_groups[idx].position_count + '" '
          + ' contenttype="' + contentGroups.content_groups[idx].contenttype + '"   >'
          + '<b>' + contentGroups.content_groups[idx].name + '</b><br/>'
          + '<p><b>' + contentGroups.content_groups[idx].language + '</b></p>'
          + '<img style="height:40px;" src="' + contentGroups.content_groups[idx].language_icon + '" />'
          + '<p style="margin-top:0px;">' + contentGroups.content_groups[idx].contenttype + '</p> '
          //+ '<span> capacity: ' + contentGroups.content_groups[idx].position_count + '</span>'
          + '<img src="' + contentGroups.content_groups[idx].icon + '" ></button></li>';
        }
      }
      var placementItemsWrap = $('.main-navigation > .articles-group-tabs');
      placementItemsWrap.html(htmlButton);

      var elms = placementItemsWrap.find(".placement-group-link");
      for (var idx = 0 ; idx < elms.length ; idx++)
      {
        elms[idx].onclick = function()
        {
          for (var idx = 0 ; idx < elms.length ; idx++)
          {
            elms.removeClass('button-selected');
          }
          currentPlacementGroup = this.getAttribute("data");
          currentArticleType = this.getAttribute("contenttype");
          currentSlotsCount = this.getAttribute("slotcount");
          currentLocale = this.getAttribute('language');
          currentLocaleIsoCode = this.getAttribute('language-isocode');
          currentLocaleIcon = this.getAttribute('language-icon');

          $(this).addClass('button-selected');
          getContentGroup(envBaseUrl, currentPlacementGroup);
          reloadAvailableContentItems(currentArticleType);
        };
      }
      elms[0].onclick();
    });

  }


  //------------------------------------------------------------------------------------------------------------
  //
  //
  //------------------------------------------------------------------------------------------------------------
  function getContentGroup(envBaseUrl, groupId)
  {
    var data = {
      action: 'get_content_group_items',
      url: envBaseUrl + '/content_groups/' + groupId,
      env: currentEnvName,
      locale: currentLocale,
      ms_afa_nonce: ms_afa_vars.ms_afa_nonce
    };

    // '<img style="position:relative;left:-105px;top:-60px;height:40px;" src="' + contentGroups.content_groups[idx].language_icon + '" />'

    var placementItemsWrap = $('.placement-section > .placement-items'),
        groupIdField = $('#group-id'),
      groupCapacityField = $('#group-capacity-1');

    placementItemsWrap.html('<li class="article-item clear">' +
                            '<div class="loader-wrap"><span class="ajax-loader"></span></div>' +
                            '</li>');

    $.post( ms_afa_vars.ajax_url, data, function(res)
    {
      var serverOutput = JSON.parse(res);

      if (serverOutput.httpCode === 200)
      {
        groupIdField.html(currentPlacementGroup);
        var headerHtml = '<img style="vertical-align:middle;height:70px;margin-right:15px;" src="' + currentLocaleIcon  + '" />'
            + '<span style="font-size:1.8em;color:#009cd6;margin-right:15px;">' + currentEnvName + '</span>'
            + '<span style="font-size:1.8em;margin-right:25px;">' + serverOutput.unit_name + ': </span><span style="color:silver;font-size:1.8em;">'
            + serverOutput.groupFreeSlots
            + '</span> Free from <span style="color:silver;font-size:1.8em;">'
            +  serverOutput.groupCapacity + '</span>';

        groupCapacityField.html(headerHtml);
        groupCapacityField.attr('data-available-slots', serverOutput.groupFreeSlots);
        placementItemsWrap.html(serverOutput.res);
      }
      else if (serverOutput.res === 'Permission denied')
      {
        placementItemsWrap.html('<li class="article-item"><p>Permission denied</p></li>');
      }
      else
      {
        placementItemsWrap.html('<li class="article-item"><p>Something went wrong</p></li>');
      }
    });
  }

  function reloadEditorForEnvironment()
  {
    clearContentGroupsViews();
    getAllContentGroups(currentEnvBaseUrl);
  }

  function __fg()
  {
    $('.slot-select-dropdown').each(function(index)
    {
      var selection_control = $(this);
      $(this).on('change', function ()
      {
        var slotNumber = $(this).val();
        $('.article-item').each(function (index)
        {
          $(this).removeClass('highlight');
          if ($(this).attr('data-position-id') == slotNumber)
          {
            currentExistingPlacementId = $(this).attr('placement-id');
            currentPlacementSlotNumber = slotNumber;
            $(this).addClass('highlight');
           }
        });

      });
    });
  }
  //------------------------------------------------------------------------------------------------------------
  //
  //
  //------------------------------------------------------------------------------------------------------------
  function reloadAvailableContentItems(contentType)
  {
    var data = {
      action: 'search_articles',
      env: currentEnvName,
      searchterm: $('#search-title').val(),
      searchtype: contentType,
      orderby:'pubdate',
      ms_afa_nonce: ms_afa_vars.ms_afa_nonce
    };

    var contentList = $('.available-items');
    contentList.html('<div class="loader-wrap"><span class="ajax-loader"></span></div>');

    var contentHeading = $('#article-types');
    contentHeading.html('<p>&nbsp;&nbsp;&nbsp;Showing: (<b>' + data.searchtype + '</b>)&nbsp;&nbsp;&nbsp; Searched text: (' + data.searchterm + ')</p>');

    $('.search-radio-buttons').each(function(index)
    {

      if($(this).data("button-type") == contentType)
      {
        $(this).prop("checked", true);
      }
      else
      {
        $(this).prop("checked", false);
      }
    });

    $.post( ms_afa_vars.ajax_url, data, function(res)
    {
      var serverOutput = JSON.parse(res);
      var contentList = $('.available-items');
      var contentItems = '';

      for (var idx = 0 ; idx < serverOutput.res.length ; idx++)
      {
        if(contentType === 'curatedlink')
        {
          contentItems += '<li class="article-item curated-link-search-item clear">';
          contentItems += '<div class="article-body curated-link-body">';
          if(serverOutput.res[idx].thumbnail !== null || serverOutput.res[idx].thumbnail != null)
          {
            contentItems += '<img height="250" style="border:solid 1px black;" width="220" src="' +  serverOutput.res[idx].thumbnail + '" />'
          }
          contentItems += '<p style="clear:both;margin-top:8px;margin-bottom:8px;">title: <b>' +  serverOutput.res[idx].title + '</b></p>'
          contentItems += '<p style="clear:both;margin-top:8px;margin-bottom:8px;">headline: <b>' +  serverOutput.res[idx].headline + '</b></p>'
          contentItems += '<p style="clear:both;margin-top:8px;margin-bottom:8px;">sub-headline: <b>' +  serverOutput.res[idx].sub_headline + '</b></p>'
          contentItems += '<p style="clear:both;margin-top:8px;margin-bottom:8px;">credit: <b>' +  serverOutput.res[idx].credit + '</b></p>'
          contentItems += '<p style="clear:both;margin-top:8px;margin-bottom:8px;">quote: <b>' +  serverOutput.res[idx].quote + '</b></p>'
          contentItems += '<p style="clear:both;margin-top:8px;margin-bottom:8px;">action: <b>' +  serverOutput.res[idx].action_url + '</b></p>'
          contentItems += '<p style="margin-top:15px;"><a class="btn-link" target="_new" href="' +  serverOutput.res[idx].editlink + '">Edit in CMS (' + serverOutput.res[idx].mansionid + ')</a>'
          //contentItems += '<a class="btn-link" style="margin-top:15px;" target="_new" href="' +  serverOutput.res[idx].previewLink + '">Preview in CMS</a>'
          //contentItems += '&nbsp;<button class="btn-icon publish-pl-item" data-is-curatedlink="' + serverOutput.res[idx].isCuratedLink + '" data-external-id="' + serverOutput.res[idx].externalid + '"  data-mansion-id="' + serverOutput.res[idx].mansionid + '" >Re-Publish Card</button>'
          contentItems += '&nbsp;<span style="padding:5px;border:1px solid #808080;background-color:#808080;"><button class="btn-icon add-pl-item" data-is-curatedlink="' + serverOutput.res[idx].isCuratedLink + '" data-external-id="' + serverOutput.res[idx].externalid + '"  data-mansion-id="' + serverOutput.res[idx].mansionid + '" >Put Card in Slot</button>'

          contentItems += '<select class="slot-select-dropdown" id="slot-id-to-use">'
          contentItems += '<option disabled selected>-select-</option>'
          for(var i = 1 ; i <= currentSlotsCount ; i++)
          {
            contentItems += '<option  value="' + i + '">slot ' + i + '</option>'
          }
          contentItems += '</select></span>'

          // contentItems += '<span style="float:right;font-size:2.3em;">' + serverOutput.res[idx].mansionid +  '</span></p>'
          contentItems += '<br/>Placement Id<span style="float:right;font-size:1em;">[' + serverOutput.res[idx].articlePlacementId +  ']</span>';
          contentItems += '<br/>Curated Link Id<span style="float:right;font-size:1em;">[' + serverOutput.res[idx].curatedLinkId +  ']</span>';
        }
        else
        {
          contentItems += '<li class="article-item clear">';
          contentItems += '<div class="article-body"><a class="alignleft"><div class="article-searched-item-thumbnail" ><img src="' +  serverOutput.res[idx].thumbnail + '" /></div></a>'
          contentItems += '<p><span class="article-searched-item-title"' +  serverOutput.res[idx].previewLink + '">' +  serverOutput.res[idx].title + '</span></p>'
          //contentItems += '<p>' +  serverOutput.res[idx].body + '</p>'
          contentItems += '<p class="article-searched-item-head">' +  serverOutput.res[idx].sourcePubName + '</p>'
          contentItems += '<p class="article-searched-item-head">' +  serverOutput.res[idx].byline + '</p>'
          contentItems += '<p class="article-searched-item-head">' +  serverOutput.res[idx].lastPublished + '</p>'
          contentItems += '<p class="article-searched-item-toolbar"><a class="btn-link" target="_new" href="' +  serverOutput.res[idx].editlink + '">Edit in CMS</a>'
          contentItems += '<a class="btn-link" target="_new" href="' +  serverOutput.res[idx].previewLink + '">Preview in CMS</a>'
          contentItems += '<a class="btn-link" target="_new" href="' +  serverOutput.res[idx].origin_url + '">View Original</a></p>'
          contentItems += '<p><button class="btn-icon publish-pl-item" data-is-curatedlink="' + serverOutput.res[idx].isCuratedLink + '" data-external-id="' + serverOutput.res[idx].externalid + '"  data-mansion-id="' + serverOutput.res[idx].mansionid + '"       >Re-Publish</button>'
          contentItems += '<span style="padding:8px;border:1px solid #808080;background-color:black;"><button class="btn-icon add-pl-item" data-is-curatedlink="' + serverOutput.res[idx].isCuratedLink + '" data-external-id="' + serverOutput.res[idx].externalid + '"  data-mansion-id="' + serverOutput.res[idx].mansionid + '"       >Place in Slot</button>'
          contentItems += '<select class="slot-select-dropdown" id="slot-id-to-use">'
          contentItems += '<option disabled selected>-select-</option>'
          for(var i = 1 ; i <= currentSlotsCount ; i++)
          {
            contentItems += '<option  value="' + i + '">slot ' + i + '</option>'
          }
          contentItems += '</select></span><span><button data="' + idx  + '" class="btn-icon article-searched-item-more">+</button></span></p>'
          contentItems += '<div style="display:none;" id="more-info-' + idx + '">'
          contentItems += '<div style="font-family:arial;font-size:0.8em;" class"more-info-item">WordPress Post Id:' + serverOutput.res[idx].mansionid +  '</div>'
          contentItems += '<div style="font-family:arial;font-size:0.8em;" class"more-info-item">Env Placement Id: ' + serverOutput.res[idx].articlePlacementId +  '</div>';

          if(serverOutput.res[idx].byline == '' || serverOutput.res[idx].byline === '') {
            contentItems += '<div  style="font-family:arial;font-size:0.8em;color:red;" class"more-info-item more-info-item-error">Byline Empty</div>';
          }
          if(serverOutput.res[idx].sourcePubName == '' || serverOutput.res[idx].sourcePubName === '') {
            contentItems += '<div  style="font-family:arial;font-size:0.8em;color:red;" class"more-info-item more-info-item-error">Source Pub Name Empty</div>';
          }

          contentItems += '</div>'
        }
        contentItems += '</div></li>';
      }
      contentList.html(contentItems);
      __fg();
    });
  }

  //------------------------------------------------------------------------------------------------------------
  //
  //
  //------------------------------------------------------------------------------------------------------------
  $('#search-title').on('change', function()
  {
    reloadAvailableContentItems(currentArticleType);
  });

  //------------------------------------------------------------------------------------------------------------
  //
  //
  //------------------------------------------------------------------------------------------------------------
  $('#search-title').on('change', function()
  {
    reloadAvailableContentItems(currentArticleType);
  });


  //------------------------------------------------------------------------------------------------------------
  //
  //
  //------------------------------------------------------------------------------------------------------------
  $('.search-radio-buttons').on('change', function()
  {
    currentArticleType = $(this).attr('data-button-type');
    reloadAvailableContentItems(currentArticleType);
  });


  //------------------------------------------------------------------------------------------------------------
  //
  //
  //------------------------------------------------------------------------------------------------------------
  $('.locale-radio-buttons').on('click', function()
  {
    var _this = $(this),
        publishedArticlesWrap = _this.parents().find('.available-items'),
        data = {};

    $('.locale-radio-buttons-container').each(function(index)
    {
      $(this).removeClass("locale-selected");
    });

  });

  //------------------------------------------------------------------------------------------------------------
  //
  //
  //------------------------------------------------------------------------------------------------------------
  $(document).on('click', '.article-searched-item-more', function()
  {
    var _this = $(this);
    var infoDetailsId = this.getAttribute("data");
    var moreIdPanelClass = '#more-info-' + infoDetailsId;
    $(moreIdPanelClass).each(function(index)
    {
      $(this).toggle();
    });
  });


  //------------------------------------------------------------------------------------------------------------
  //
  //
  //------------------------------------------------------------------------------------------------------------
  $('.env-radio-buttons').on('click', function()
  {
    var _this = $(this),
        publishedArticlesWrap = _this.parents().find('.available-items'),
        placementItemsWrap = $('.placement-section > .placement-items'),
        selectedEnvText = $('#selected-env-name'),
        data = {};

    $('.env-radio-buttons-container').each(function(index)
    {
      $(this).removeClass("env-selected");
    });

    var parentSpan = _this.parent();
    $(parentSpan).addClass("env-selected");
    var env = this.getAttribute("data");
    var envUrl = this.getAttribute("data-url");
    currentEnvName = env;
    currentEnvBaseUrl = envUrl;
    selectedEnvText.html(env);
    reloadEditorForEnvironment();
   });

  //------------------------------------------------------------------------------------------------------------
  //
  // Remove article placement
  //
  //------------------------------------------------------------------------------------------------------------
  function removeArticlePlacement(placementData, mansionArticleRef)
  {
    $.post( ms_afa_vars.ajax_url, placementData, function(res)
    {
      var serverOutput = JSON.parse(res);

      if (serverOutput.httpCode === 204 || serverOutput.httpCode === 200)
      {
        mansionArticleRef.find('button.add-pl-item').removeClass('placed').text('Add to slot').removeAttr('disabled');
        getContentGroup(currentEnvBaseUrl, currentPlacementGroup);
      }
    });
  }


  //------------------------------------------------------------------------------------------------------------
  //
  // Add article placement
  //
  //------------------------------------------------------------------------------------------------------------
  function addArticlePlacement(placementData, placementsContainer, mansionArticleStatus)
  {
    $.post( ms_afa_vars.ajax_url, placementData, function(res)
    {
      var serverOutput = JSON.parse(res);

      if (serverOutput.res === 'Created' || serverOutput.httpCode === 201 )
      {
        //
        // We refresh content group list and also available content items
        //
        getContentGroup(currentEnvBaseUrl, currentPlacementGroup);
        reloadAvailableContentItems(currentArticleType);

        mansionArticleStatus.text('Placed').addClass('placed');
      }
      else if (serverOutput.res === 'Permission denied')
      {
        placementsContainer.html('<li class="article-item"><p>Permission denied</p></li>');
      }
      else
      {
        placementsContainer.html('<li class="article-item"><p>Something went wrong</p></li>');
      }
    });
  }


  //------------------------------------------------------------------------------------------------------------
  //
  // Click handler for removing placement item
  //
  //------------------------------------------------------------------------------------------------------------
  $(document).on('click', '.remove-pl-item', function()
  {
    var _this = $(this),
        placementItemsWrap = $('.placement-section > .placement-items'),
        placementSlotNumber = $('#slot-id-to-use'),
        mansionArticleRef = $('.available-items').find('li[data-article-id="' + _this.data('mansion-id') + '"]'),
        data = {
          action: 'delete_content_group_item',
          url: currentEnvBaseUrl + '/content_placements/' + _this.data('placement-id'),
          env: currentEnvName,
          ms_afa_nonce: ms_afa_vars.ms_afa_nonce,
          mansionId: _this.data('mansion-id')
        };

    placementItemsWrap.html('<li class="article-item clear">' +
      '<div class="loader-wrap"><span class="ajax-loader"></span></div>' +
      '</li>');

    removeArticlePlacement(data, mansionArticleRef);
  });

  //------------------------------------------------------------------------------------------------------------
  //
  // Click handler for removing placement item
  //
  //------------------------------------------------------------------------------------------------------------
  $(document).on('click', '#env-options-btn', function()
  {
    $('#env-options').toggle();
  });

  //------------------------------------------------------------------------------------------------------------
  //
  // Click handler for removing placement item
  //
  //------------------------------------------------------------------------------------------------------------
  $(document).on('focusin', '#search-title', function()
  {
    $(this).val('');
  });


  //------------------------------------------------------------------------------------------------------------
  //
  // Click handler for updating item
  //
  //------------------------------------------------------------------------------------------------------------
  $(document).on('click', '.publish-pl-item', function()
  {
    var _this = $(this),
        data = {
          action: 'update_content_group_item',
          env: currentEnvName,
          ms_afa_nonce: ms_afa_vars.ms_afa_nonce,
          externalId: _this.data('external-id'),
          mansionId: _this.data('mansion-id'),
          isCuratedLink: _this.data('is-curatedlink'),
          locale_isocode: currentLocaleIsoCode
        };

    $.post( ms_afa_vars.ajax_url, data, function(res)
    {
      var serverOutput = JSON.parse(res);
      getContentGroup(currentEnvBaseUrl, currentPlacementGroup);
    });
  });

  //------------------------------------------------------------------------------------------------------------
  //
  // Click handler for adding placement item
  //
  //------------------------------------------------------------------------------------------------------------
  $(document).on('click', '.add-pl-item', function()
  {
    var _this = $(this),
        placementItemsWrap = $('.placement-section > .placement-items'),
        groupAvailableSlots = parseInt($('#group-capacity-1').attr('data-available-slots')),
        data = {
          action: 'add_content_group_item',
          env: currentEnvName,
          url: currentEnvBaseUrl + '/content_placements',
          ms_afa_nonce: ms_afa_vars.ms_afa_nonce,
          placementGroupId: currentPlacementGroup,
          externalId: _this.data('external-id'),
          locale: currentLocale,
          locale_isocode: currentLocaleIsoCode,
          targetSlot: currentPlacementSlotNumber,
          existingPlacementId: currentExistingPlacementId,
          mansionId: _this.data('mansion-id'),
          isCuratedLink: _this.data('is-curatedlink')
        };

    if (groupAvailableSlots === 0 || currentExistingPlacementId != null)
    {
      var lastPlacement = $('.placement-items > .article-item').last().find('button.remove-pl-item'),
          mansionArticleRef = $('.available-items').find('li[data-article-id="' + lastPlacement.data('mansion-id') + '"]'),
          itemToBeDeleted = {
            action: 'delete_content_group_item',
            url: currentEnvBaseUrl + '/content_placements/' + currentExistingPlacementId,
            env: currentEnvName,
            ms_afa_nonce: ms_afa_vars.ms_afa_nonce,
            mansionId: lastPlacement.data('mansion-id')
          };

      placementItemsWrap.html('<li class="article-item clear">' +
        '<div class="loader-wrap"><span class="ajax-loader"></span></div>' +
        '</li>');

      $.post( ms_afa_vars.ajax_url, itemToBeDeleted, function(res) {
        var serverOutput = JSON.parse(res);

        if (serverOutput.httpCode === 204 || serverOutput.httpCode === 200) {
          mansionArticleRef.find('button.add-pl-item').removeClass('placed').text('Add to slot').removeAttr('disabled');
          addArticlePlacement(data, placementItemsWrap, _this);
        }
      });
    }
    else
    {
      placementItemsWrap.html('<li class="article-item clear">' +
        '<div class="loader-wrap"><span class="ajax-loader"></span></div>' +
        '</li>');

      addArticlePlacement(data, placementItemsWrap, _this);
    }
  });

  reloadEditorForEnvironment();

}(jQuery));
