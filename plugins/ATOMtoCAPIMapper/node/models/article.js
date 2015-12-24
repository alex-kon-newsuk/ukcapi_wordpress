var mongoose = require('mongoose');
var logger = require('../util/logger');
var jobs = require('../util/jobs');

var Subscription = require('./subscription');

var articleSchema = new mongoose.Schema({
  content: {
    type: String,
    required: true
  },
  internalId: {
    type: String,
    unique: true,
    index: true
  },
  externalId: {
    type: String,
    unique: true,
    index: true
  }
});

articleSchema.post('save', function(doc)
{


  Subscription.find({
    topic: 'article',
    match: [doc.internalId]
  }, function(err, subs) {
    if (err) {
      logger.error('Could not fetch matching Subscriptions', { type: 'article', id: doc.id, error: err });
      return;
    }

    for (var i = 0; i < subs.length; i++) {
      var data = {
        type: 'article',
        id: doc.internalId,
        subscription: subs[i].toJSON()
      };
      jobs.now('subscription webhook', data);
    }
  });
});

module.exports = mongoose.model('Article', articleSchema);
