var request = require('request');
var logger = require('./logger');
var path = require('path');
var nr = require('newrelic');

var config = require('../config');

module.exports = {
  update: function(id, cb) {
    nr.incrementMetric('Custom/article-update/count');
    if (id) {
      var url = config['api-root'] + path.join(config['api-root-path'], 'articles', id) + '?forceRefresh=1';
      request.get(url, function(err, res, body) {
        if (!err && res.statusCode === 200) {
          nr.incrementMetric('Custom/article-update/success');
          logger.info('Successfully updated article', { id: id });
        } else {
          nr.incrementMetric('Custom/article-update/failure');
          logger.error('Could not fetch article', { id: id, url: url, error: err });
        }
        if (cb) cb();
      });
    } else {
      nr.incrementMetric('Custom/article-update/failure');
      logger.error('Could not fetch article, no ID provided');
      if (cb) cb();
    }
  }
};
