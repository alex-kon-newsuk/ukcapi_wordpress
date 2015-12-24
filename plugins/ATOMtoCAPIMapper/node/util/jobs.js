var Agenda = require('agenda');
var _ = require('lodash');

var logger = require('./logger');
var config = require('../config');

switch (config.env) {
case 'test':
  logger.warn('Running agenda with no real DB, all agenda operations will no-op');

  // Since we don't have a real MongoDB, replace all methods with no-ops
  Agenda.prototype = _.mapValues(Agenda.prototype, function(f) { return function() {}; });
  module.exports = new Agenda();
  break;
default:
  module.exports = (new Agenda()).database(config.mongodb);
}
