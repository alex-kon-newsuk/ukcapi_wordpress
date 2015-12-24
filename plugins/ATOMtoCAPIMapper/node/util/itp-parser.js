var cheerio = require('cheerio');
var _ = require('lodash');
var slug = require('slug');
var moment = require('moment');

var ItpParser = {
  parse: function(xml, isNow, defaultSection) {
    var result = [];
    var $ = cheerio.load(xml, { xmlMode: true });

    // If there isn't an explicit ITP key, use the last build date attribute
    // Should be in the format of WSJ-YYYYMMDD
    var match = $('wsj\\:itpkey').first().text().match(/wsj-(\d{8})/i);
    var date = match ? moment(match[1], 'YYYYMMDD') : moment(new Date($('lastBuildDate').first().text()));
    var idPrefix = isNow ? 'now-wsj' : 'itp-wsj-' + date.format('YYYY-MM-DD');

    // Use the same tags for all collections (assumes that everything is from the same day)
    var tags = isNow ? [idPrefix] : ['itp-wsj', idPrefix];

    var items = [];
    $('channel item').each(function() {
      items.push(this);
    });

    var itemMap = _.groupBy(items, function(el) {
      // For NOW feeds, assume that all articles are in the specified defaultSection
      if (isNow) {
        return defaultSection;
      } else {
        return $(el).find('wsj\\:section').text() || defaultSection;
      }
    });

    for (var section in itemMap) {
      var collection = {
        pubId: 'wsj',
        friendlyId: slug(idPrefix + '-' + section).toLowerCase(),
        tags: _.map(_.flatten([tags, slug(section)]), function(tag) { return tag.toLowerCase(); }),
        name: section
      };
      collection.contents = _.map(itemMap[section], function(item) {
        return {
          type: 'article',
          id: $(item).find('guid').text()
        };
      });
      result.push(collection);
    }

    return result;
  }
};

module.exports = ItpParser;
