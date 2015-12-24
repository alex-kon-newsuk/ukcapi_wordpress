var mongoose = require('mongoose');
var validator = require('validator');

var subscriptionSchema = new mongoose.Schema({
  topic: {
    type: String,
    required: true,
    index: true,
    enum: ['article', 'collection', 'tag']
  },
  match: {
    type: [String],
    required: true,
    index: true
  },
  clientId: {
    type: String,
    reuqired: true,
    index: true
  },
  url: {
    type: String,
    required: true,
    validate: function(val) {
      return validator.isURL(val);
    }
  }
});

subscriptionSchema.plugin(require('../util/global-schema'));

module.exports = mongoose.model('Subscription', subscriptionSchema);
