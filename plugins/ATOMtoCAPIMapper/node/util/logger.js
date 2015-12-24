var winston = require('winston');

var config = require('../config');
require('./newrelic-transport');

switch (config.env) {
case 'test':
  module.exports = new (winston.Logger)({
    transports: [
      new (winston.transports.Console)({
        handleExceptions: true,
        level: 'error'
      })
    ]
  });
  break;
case 'production':
  module.exports = new (winston.Logger)({
    transports: [
      new (winston.transports.Console)({ handleExceptions: true }),
      new (winston.transports.NewRelicTransport)({})
    ]
  });
  break;
default:
  module.exports = new (winston.Logger)({
    transports: [
      new (winston.transports.Console)(),
      new (winston.transports.NewRelicTransport)({})
    ]
  });
}
