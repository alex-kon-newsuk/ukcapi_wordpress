var async = require('async');
var nr = require('newrelic');
var request = require('request');
var _ = require('lodash');

var logger = require('./logger');

module.exports = {
  updateCollections: function(endpoint, collections) {
    // For every parsed collection, check if it already exists.
    // If not, create a new collection. Otherwise, update the existing one.
    async.eachSeries(collections, function(collection, cb) {
      nr.incrementMetric('Custom/collection_update/count');
      request(endpoint + collection.friendlyId, function(err, res, body) {
        var method = request.post;
        var opts = {
          url: endpoint,
          json: collection
        };

        if (!err && res.statusCode === 200) {
          // Collection already exists, just update if changed
          var resObj = _.omit(JSON.parse(body), ['id']); // omit Mongo-added attributes
          if (!_.isEqual(collection, resObj)) {
            method = request.put;
            opts.url = endpoint + collection.friendlyId;
          } else {
            nr.incrementMetric('Custom/collection_update/success');
            logger.info('No change to collection, skipping', { collection: collection.friendlyId });
            return cb();
          }
        }

        method(opts, function(err, res, body) {
          if (!err && res.statusCode >= 200 && res.statusCode < 300) {
            nr.incrementMetric('Custom/collection_update/success');
            logger.info('Successfully updated collection', { collection: collection.friendlyId });
          } else {
            nr.incrementMetric('Custom/collection_update/failure');
            logger.error('Failed to update collection', { collection: collection.friendlyId });
          }
          cb();
        });
      });
    });
  }
};
