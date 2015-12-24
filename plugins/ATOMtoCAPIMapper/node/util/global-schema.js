var _ = require('lodash');

module.exports = function(schema, options) {
  schema.method('safeSet', function(values) {
    var clone = _.omit(values, function(v, k) {
      return k.charAt(0) === '_';
    });
    this.set(clone);
    return this;
  });

  schema.set('toJSON', {
    transform: function(doc, ret, options) {
      if (typeof doc.ownerDocument !== 'function') {
        ret.id = ret._id;
        delete ret._id;
      }
      delete ret.__v;
    }
  });
};
