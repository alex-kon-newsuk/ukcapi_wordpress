var request = require('request');

var config = require('../config');
var logger = require('./logger');

module.exports = function(job) {
  var data = job.attrs.data;
  var req = {
    url: data.subscription.url,
    json: data
  };

  request.post(req, function(err, res, body) {
    if (!err && res.statusCode >= 200 && res.statusCode < 300) {
      logger.info('Successfully called subscriber', { json: req });
    } else {
      logger.error('Failed to call subscriber, will try again later', { json: req });

      // After some number failures, abandon
      data._failures = data._failures ? data._failures + 1 : 1;
      var maxFailures = config['max-subscriber-failures'];
      if (maxFailures === null) {
        maxFailures = 10; // default
      }
      if (data._failures < maxFailures) {
        job.schedule(config['subscriber-retry-duration'] || 'in 10 minutes');
        job.save();
      } else {
        logger.error('Subscriber exceeded failure limit, abandoning', { json: data });
      }
    }
  });
};
