var _ = require('lodash');

var CONFIG = {
  _default: {
    'au-capi-ingest-post-url': 'http://content.ingest.newsapi.com.au/dev-int/document/v1/',
    'au-capi-api-key':         'hu42utpqycb5jn8g3a3xgvyv',

    'uk-hub-post-url':         'http://nu-hub-uat.elb.cps-dev.ntch.co.uk/publish',
    'uk-hub-send-topic':       'http://news.co.uk/thesun_capi/json',

    'api-root':                'http://localhost:8080',
    'api-root2':               'http://wordpress.ukcapi.codejam.events:8080',
    'api-root-path':           '/content-service'
  },
  test: {
    'api-root': 'http://localhost:3000'
  },
  development: {
    'api-root': 'http://localhost:3000'
  },
  local: {
    'api-root': 'http://localhost:3000'
  }
};

module.exports = _.merge({ env: process.env.NODE_ENV }, CONFIG._default, CONFIG[process.env.NODE_ENV]);
