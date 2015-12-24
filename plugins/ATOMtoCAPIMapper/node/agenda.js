var logger = require('./util/logger');
var config = require('./config');
var jobs = require('./util/jobs');

var ItpUpdater = require('./util/itp-updater');
var ArticleUpdater = require('./util/article-updater');
var FuzzUpdater = require('./util/fuzz-updater');

// ITP
jobs.define('itp refresh', function(job) {
  logger.info('Running ITP refresh');
  ItpUpdater.run(config['api-root']);
});
jobs.every('1 minute', 'itp refresh');

// Article
jobs.define('article update', function(job) {
  var id = job.attrs.data.id;
  logger.info('Running article update', { id: id });
  ArticleUpdater.update(id);
});

// Subscription
jobs.define('subscription webhook', require('./util/subscription-webhook'));

// Fuzz/NYP content
jobs.define('fuzz refresh', function(job) {
  logger.info('Running Fuzz/NYP refresh');
  FuzzUpdater.run(config['api-root']);
});
jobs.every('1 minute', 'fuzz refresh');

jobs.start();
