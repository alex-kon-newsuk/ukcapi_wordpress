var util = require('util');
var winston = require('winston');
var nr = require('newrelic');

var NewRelicTransport = winston.transports.NewRelicTransport = function (options) {
  this.name = 'NewRelicTransport';
  this.level = options.level || 'error';
};

util.inherits(NewRelicTransport, winston.Transport);

NewRelicTransport.prototype.log = function (level, msg, meta, callback) {
  nr.noticeError(msg, meta);
  callback(null, true);
};
