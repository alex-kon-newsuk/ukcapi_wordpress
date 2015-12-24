var request = require('request');

var logger = require('./logger');

var ItpParser = require('./itp-parser');
var ContentUpdater = require('./content-updater');

var FEEDS = [
  {
    url: 'http://mobilefeeds.wsj.com/xml/rss/3_8173.xml',
    isNow: false,
    defaultSection: 'MAGAZINE'
  },
  {
    url: 'http://mobilefeeds.wsj.com/xml/rss/3_8175.xml',
    isNow: false,
    defaultSection: 'WHATS NEWS'
  },
  {
    url: 'http://mobilefeeds.wsj.com/xml/feed/v2/3_8417.rss',
    isNow: true,
    defaultSection: 'TOP NEWS'
  },
  {
    url: 'http://mobilefeeds.wsj.com/xml/feed/v2/3_8418.rss',
    isNow: true,
    defaultSection: 'WORLD'
  },
  {
    url: 'http://mobilefeeds.wsj.com/xml/feed/v2/3_8419.rss',
    isNow: true,
    defaultSection: 'US'
  },
  {
    url: 'http://mobilefeeds.wsj.com/xml/feed/v2/3_8420.rss',
    isNow: true,
    defaultSection: 'BUSINESS'
  },
  {
    url: 'http://mobilefeeds.wsj.com/xml/feed/v2/3_8421.rss',
    isNow: true,
    defaultSection: 'TECH'
  },
  {
    url: 'http://mobilefeeds.wsj.com/xml/feed/v2/3_8422.rss',
    isNow: true,
    defaultSection: 'MARKETS'
  },
  {
    url: 'http://mobilefeeds.wsj.com/xml/feed/v2/3_8423.rss',
    isNow: true,
    defaultSection: 'OPINION'
  },
  {
    url: 'http://mobilefeeds.wsj.com/xml/feed/v2/3_8424.rss',
    isNow: true,
    defaultSection: 'LIFE & CULTURE'
  },
  {
    url: 'http://mobilefeeds.wsj.com/xml/rss/3_7721.xml',
    isNow: true,
    defaultSection: 'WHATS NEWS'
  }
];

module.exports = {
  run: function(apiroot) {
    var endpoint = apiroot + '/api/v1/collections/';
    for (var i = 0; i < FEEDS.length; i++) {
      (function() { // hack to properly capture variable feed in a closure
        var feed = FEEDS[i];
        request(feed.url, function(err, resp, body) {
          if (!err && resp.statusCode === 200) {
            var collections = ItpParser.parse(body, feed.isNow, feed.defaultSection);
            ContentUpdater.updateCollections(endpoint, collections);
          } else {
            logger.error('Failed to get feed', { feed: feed.url });
          }
        });
      })();
    }
  }
};
