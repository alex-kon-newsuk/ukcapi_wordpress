var _ = require('lodash');
var request = require('request');
var async = require('async');
var moment = require('moment');

var logger = require('./logger');
var FuzzParser = require('./fuzz-parser');
var ContentUpdater = require('./content-updater');
var Article = require('../models/article');
var config = require('../config');

// TODO: extract into config
var FEED = config['fuzz-issues-root'];
var MANIFEST_ROOT = config['fuzz-manifest-root'];

module.exports = {
  run: function(apiroot) {
    if (!config['update-window-days']) {
      logger.error('Update window is undefined');
      return;
    }

    var endpoint = apiroot + '/api/v1/collections/';

    request({
      url: FEED
    }, function(err, resp, body) {
      if (!err && resp.statusCode === 200) {
        var result = JSON.parse(body);
        if (result.data.length > 0) {
          var updateWindow = moment().utc().startOf('day').subtract(config['update-window-days'], 'days');

          _.each(
            _.filter(result.data, function(current) {
              return moment(current.date).isAfter(updateWindow);
            }),
            function (current) {
              request(MANIFEST_ROOT + current.md5 + '_' + current.version + '.json', function(err, resp, body) {
                if (!err && resp.statusCode === 200) {
                  var issue = JSON.parse(body);

                  // Update Collections
                  var collections = FuzzParser.getCollections(issue, current.date);
                  ContentUpdater.updateCollections(endpoint, collections);

                  // Update Articles
                  // We do this directly on the DB because we have no source for articles
                  // we can directly pull from upstream (as we do with WSJ)
                  var articles = FuzzParser.getArticles(issue);
                  async.eachSeries(articles, function(article, cb) {
                    logger.info('Pulling NYP article', { id: article.id });
                    Article.findOne({ 'internalId': article.id }, function(err, a) {
                      if (err) {
                        logger.error('Could not get article for updating', { id: article.id });
                        return cb(err);
                      }

                      var jsonStr = JSON.stringify(article);
                      if (a) {
                        if (a.toJSON().content !== jsonStr) {
                          logger.info('Updating article with new content', { id: article.id });
                          a.content = jsonStr;
                        } else {
                          logger.info('No changes to article, skipping', { id: article.id });
                        }
                      } else {
                        logger.info('Article did not previously exist, creating it', { id: article.id });
                        a = new Article({ content: jsonStr, internalId: article.id });
                      }

                      if (a.isModified()) {
                        a.save(cb); // don't wait on this
                      } else {
                        cb();
                      }
                    });
                  });
                } else {
                  logger.error('Unable to read Fuzz issue', { issueId: current.id });
                }
              });
            });
        } else {
          logger.error('No issues available in feed', { feed: FEED });
        }
      } else {
        logger.error('Failed to get feed', { feed: FEED });
      }
    });
  }
};
