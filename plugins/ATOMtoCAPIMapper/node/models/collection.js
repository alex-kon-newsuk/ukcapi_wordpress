var mongoose = require('mongoose');

var logger = require('../util/logger');
var jobs = require('../util/jobs');

var Subscription = require('./subscription');

var collectionSchema = new mongoose.Schema(
    {
      friendlyId: { type: String, index: true, unique: true, required: true },
      pubId:      { type: String, required: true },
      name:       { type: String, required: true },
      contents: [
        {
          _id: false,
          id: { type: String, required: true },
          type: { type: String, required: true }
        }
                ],
      tags: { type: [String], index: true },
      metadata: { type: Object, required: false }
});

collectionSchema.plugin(require('../util/global-schema'));

collectionSchema.post('save', function(doc) {
  Subscription.find({
    $or: [
      {
        topic: 'collection',
        match: [doc.id]
      },
      {
        topic: 'tag',
        match: {
          $not: {
            $elemMatch: {
              $nin: doc.tags
            }
          }
        }
      }
    ]
  }, function(err, subs) {
    if (err) {
      logger.error('Could not fetch matching Subscriptions', { type: 'collection', id: doc.id, error: err });
      return;
    }

    for (var i = 0; i < subs.length; i++) {
      var data = {
        type: 'collection',
        id: doc.id,
        subscription: subs[i].toJSON()
      };
      jobs.now('subscription webhook', data);
    }
  });
});

module.exports = mongoose.model('Collection', collectionSchema);
