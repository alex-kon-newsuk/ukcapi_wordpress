//--------------------------------------------------------------------------------
//
//  wsj Xml parser
//
//
//
//--------------------------------------------------------------------------------

var cheerio = require('cheerio');
var moment = require('moment-timezone');
var _ = require('lodash');
var request = require('request');
var xpath = require('xpath');
var dom = require('xmldom').DOMParser;
var U = require('../util/utility');

var WSJ_MAPPINGS = {
  language: '/article-doc/@language',
  subsection: '/article-doc/@type',
  source: '/article-doc/@orig-obj-src',
  product: '/article-doc/@product',
  url: '/article-doc/@url',
  seoId: '/article-doc/@seo-id',
  publishedAt: '/article-doc/@orig-pub-date',
  updatedAt: '/article-doc/@major-revision-date',
  section: '/article-doc/@section',
  headline: '/article-doc/article/article-body/headline/main-hed',
  deck: '/article-doc/article/article-body/headline/deck',
  flashline: '/article-doc/@display-name'
};

var DATE_KEYS = ['publishedAt', 'updatedAt'];
var DECO_TYPES = [
  'deco summary',
  'mobiledeco',
  'mobile vid of the day'
];

module.exports = {

    processor: function(body, res, callback)
    {
      console.log('processing: ', body);

      var requestData =
      {
        url: body.cmsapixml,
        qs:
        {
          time: +new Date()
        },
        method: 'GET',
        headers:
        {
          'Custom-Header': 'Custom Value'
        }
      };

      request(requestData, function (error, response, responseBody)
      {
          if (error || response.statusCode !== 200)
          {
            return res.status(400).end();
          }
          else
          {
            var result = {};
            var $ = cheerio.load(responseBody, { xmlMode: true });

            // Pick basic values out of the DJML
            _.merge(result, extractBasic($, WSJ_MAPPINGS));

            // Get ID
            var id = null;
            var idRaw = query($, '/article-doc/@id');
            if (idRaw) {
              id = idRaw.split('.')[0];
            }
            result.id = id;

            // need the original value here ... to do
            result.externalId = body.externalid;

            // Extract self codes
            result.selfCodes = extractSelfCodes($);

            // Extract summaries
            result.summary = extractSummaries($);

            // Extract summary images
            result.summary.images = extractSummaryImages($);

            // Extract authors
            result.authors = extractAuthors($);

            // Extract article
            result.body = parseBody($('article-doc article article-body'), getReferences($('article-doc article article-header')), true);

            // Parse date keys
            parseDates(result, DATE_KEYS);

            // Is this a deco?
            result.isDeco = false;
            if (result.subsection) {
              result.isDeco = _.some(DECO_TYPES, function(t) {
                return result.subsection.toLowerCase().indexOf(t) === 0;
              });
            }

            callback( result );
          }
      });

    }
};

// Parsing/extraction helpers
function extractBasic($, mappings) {
  var result = {};
  for (var key in mappings) {
    result[key] = query($, mappings[key]) || null;
  }
  return result;
}

function extractSummaryImages($) {
  var images = [];
  var $images = $('article-doc summary alt-summary-body[page="Images"] media');
  for (var i = 0; i < $images.length; i++) {
    images.push(parseMedia($images.eq(i)));
  }
  return images;
}

function extractSelfCodes($) {
  var result = [];
  var $codes = $('article-doc meta-data self-codes code');
  $codes.each(function() {
    result.push(_.merge({
      value: $(this).text() || null
    }, this.attribs));
  });
  return result;
}

function extractSummaries($) {
  var result = { alts: [] };
  var refs = getReferences($('article-doc summary summary-header'));

  // Get main summary
  var $summary = $('article-doc summary summary-body');
  result.body = parseBody($summary, refs);

  var $alts = $('article-doc summary alt-summary-body[page!="Images"]');
  $alts.each(function() {
    var page = _.filter($(this).attr('page').split('|'), function(str) { return str.length > 0; })[0];
    result.alts.push({
      page: page,
      body: parseBody($(this), refs)
    });
  });

  return result;
}

function extractAuthors($) {
  var result = [];
  var $authors = $('article-doc article article-body byline phrase');
  $authors.each(function() {
    result.push({
      name: $(this).text(),
      topicId: $(this).attr('topicid')
    });
  });
  return result;
}

function parseBody($body, refs, isArticleBody) {
  var body = [];
  var $children = $body.children();
  for (var i = 0; i < $children.length; i++) {
    var tag = $children[i].name.toLowerCase();
    var method = null;

    if (tag === 'headline') {
      method = parseHeadline;
    } else if (tag === 'media') {
      method = parseMedia;
    } else if (tag === 'list') {
      method = parseList;
    } else if (tag === 'inset') {
      method = parseInset;
    } else if (tag === 'dateline') {
      // Skip dateline for now (not sure what it's used for)
    } else if (tag === 'temporary-info') {
      // Ignore temporary-info for now
    } else { // p, byline, tagline, headers, etc
      method = parseParagraph;
    }

    if (method) {
      var parsed = method($children.eq(i), refs);
      parsed['@'] = getAttributes($children.eq(i)); // include original attributes
      body.push(parsed);
    }
  }

  // Headline and byline elements are overloaded content/metadata tags.
  // Don't include them in the article body, since we already extract
  // these two elements as true metadata values.
  if (isArticleBody) {
    body = _.reject(body, function(tag) {
      return tag.tag === 'headline' || tag.tag === 'byline';
    });
  }
  return body;
}

// <headline>
function parseHeadline($el) {
  return {
    tag: $el[0].name.toLowerCase(),
    main: $el.find('main-hed').text() || null,
    deck: $el.find('deck').text() || null,
    flashline: $el.find('flashline > link').text() || null
  };
}

// <p>
function parseParagraph($el, refs) {
  return {
    tag: $el[0].name.toLowerCase(),
    text: processHtml($el.html(), refs)
  };
}

// <media>
function parseMedia($el) {
  var media = {
    tag: $el[0].name.toLowerCase(),
    image: parseImage($el.find('image')),
    credit: $el.find('media-credit').text() || null,
    caption: $el.find('media-caption').text() || null,
    alts: []
  };

  var $alts = $el.find('alt-image');
  for (var i = 0; i < $alts.length; i++) {
    media.alts.push(parseImage($alts.eq(i)));
  }
  return media;
}

// <image>
function parseImage($el) {
  var image = _.pick(getAttributes($el), ['alternate-text', 'height', 'width', 'src-id']);
  image.src = image['src-id'];
  delete image['src-id'];
  return image;
}

// <list>
function parseList($el, refs) {
  var list = {
    tag: $el[0].name.toLowerCase(),
    ordered: false,
    items: []
  };

  var attr = getAttributes($el);
  if (attr && attr.type === 'ordered') {
    list.ordered = true;
  }

  var $items = $el.find('list-item');
  for (var i = 0; i < $items.length; i++) {
    list.items.push(processHtml($items.eq(i).html(), refs));
  }

  return list;
}

// <inset>
function parseInset($el, refs) {
  var result = {
    tag: $el[0].name.toLowerCase()
  };
  var type = $el.attr('type');

  if (type && type.toLowerCase() === 'slideshowembed') {
    result.embed = $el.find('p').text();
  } else {
    result.body = parseBody($el, refs);
  }

  return result;
}

function processHtml(html, refs) {
  var $ = cheerio.load(html, { xmlMode: true });

  // <highlight>
  var $highlights = $('highlight');
  $highlights.each(function() {
    var type = $(this).attr('type');
    if (type && type.toLowerCase() === 'bold') {
      $(this).replaceWith('<strong>' + $(this).html() + '</strong>');
    } else {
      $(this).replaceWith('<em>' + $(this).html() + '</em>');
    }
  });

  // <link>
  var $links = $('link');
  $links.each(function() {
    var $a = $('<a>');

    var icon = $(this).attr('icon');
    if (icon && icon.toLowerCase() !== 'none') {
      $a.attr('data-icon', icon.toLowerCase());
    }

    var type = $(this).attr('type');
    if (type) {
      $a.attr('data-type', type.toLowerCase());
    }

    var href = refs[$(this).attr('linkend')];
    if (href) {
      var url = type && type.toLowerCase() === 'intradoc' ? '#' : '';
      $a.attr('href', url + href.value);
    }

    $a.html($(this).html());
    $(this).replaceWith($a);
  });

  // <break>
  var $breaks = $('break');
  $breaks.each(function() {
    $(this).replaceWith('<br>');
  });

  return $.html().trim();
}

// Util methods
function getReferences($el) {
  var refs = {};
  var $namelocs = $el.find('nameloc');
  for (var i = 0; i < $namelocs.length; i++) {
    var $name = $namelocs.eq(i);
    var type = $name.find('nmlist').attr('nametype');
    refs[$name.attr('id')] = {
      type: type ? type.toLowerCase() : null,
      value: $name.find('nmlist').text()
    };
  }
  return refs;
}

function getAttributes($el) {
  return _.mapValues($el[0].attribs, function(attr, key) {
    if (key === 'id' || key === 'src-id') {
      return attr; // don't mess with particular attributes
    } else {
      return attr ? attr.toLowerCase() : null;
    }
  });
}

function query($, queryStr) {
  var path;
  var p1 = queryStr.split('@');
  var p2 = p1[0].split('/');
  path = p2.join(' ');
  if (p1.length > 1) {
    return $(path).attr(p1[1]);
  } else {
    return $(path).text();
  }
}

function parseDates(obj, keys) {
  for (var i = 0; i < keys.length; i++) {
    if (obj[keys[i]]) {
      // Assume dates in DJML are in NYC time
      obj[keys[i]] = moment.tz(obj[keys[i]], 'America/New_York').toISOString();
    }
  }
}