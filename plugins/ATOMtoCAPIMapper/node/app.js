//
// Name:      Content mapper / Microservice
// Synopsis:  This node service performs three tasks
//            1) Receives NewsUK ATOM files and maps them according to predefined XPATH rules to
//               the AU JSON CAPI format ready for ingest
//            2) It places these transformed messages on the UK Hub so they can be forwarded to the AU CAPI
//            3) It receives the transformed messages and writes them to the AU CAPI document ingest API
//
//            The service therefore subscribes to two topics
//            http://news.co.uk/thesun_capi
//            http://news.co.uk/thesun_capi/json

var express = require('express');
var path = require('path');
var bodyParser = require('body-parser');
var app = express();
var config = require('./config');

//
// The Hub only ever sends text/plain MIME type messages
// We will use the routes
//
app.use(function(req, res, next) {

  var contentType = req.headers['content-type'] || '',
      mime = contentType.split(';')[0];

  req.mime = mime;
  if (mime != 'text/plain')
  {
    req.xmlBody = false;
    req.jsonBody = false;
    req.textBody = true;
    return next();
  }

  var data = '';
  req.setEncoding('utf8');
  req.on('data', function(chunk) {
    data += chunk;
  });
  req.on('end', function() {
    req.rawBody = data;
    next();
  });
});

var router = express.Router();
app.use(config['api-root-path'], router);

//
// Hub subscription management: currently just captures
// the subscription confirmation message from the Hub/SNS so we can confirm.
// This is a one time action.
//
router.use('/subscriptions', require('./routes/subscriptions'));

//
// Proxy D-CAPI and A-CAPI
//
router.use('/feeds', require('./routes/capifeeds'));

//
// Health and accessibility check
//
app.get('/ping', function(req, res) {
  res.send('ATOM Mapper microservice running: ok');
});

// catch 404 and forward to error handler
app.use(function(req, res, next) {
  res.status(404).end();
});

// error handlers
app.use(function(err, req, res, next) {
  //console.log(err.stack);
  res.status(err.status || 500).end();
});

module.exports = app;
