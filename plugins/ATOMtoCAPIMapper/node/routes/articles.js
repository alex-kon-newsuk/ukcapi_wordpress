var express = require('express');
var router = express.Router();
var request = require('request');
var config = require('../config');

// Content source
var DjmlArticleParser = require('../util/djml-article-parser');
var Article = require('../models/article');

var NYP_PREFIX = 'NYP_';

//--------------------------------------------------------------------------------
//
//
//--------------------------------------------------------------------------------
router.get('/:id', function(req, res, next)
{
  // HACK: NYP articles don't have an upstream source, so disable forceRefresh
  var shouldRefresh = false; //req.params.id.indexOf(NYP_PREFIX) !== 0 && req.query.forceRefresh === '1';
  var q = Article.findOne({ 'internalId': req.params.id });

  // If we know the article won't change, we can use lean
  if (!shouldRefresh)
  {
    q.lean();
  }

  q.exec(function(err, article) {

    if (err)
    {
      return res.status(500).end();
    }

    res.type('application/json');
    if (article && !shouldRefresh)
    {
      return res.send(article.content);
    }
    else
    {
      // No cached version found or forcing refresh, grab from DJML origin
      request.get(config['djml-root'] + req.params.id + '.xml', function(err, resp, body) {
        if (!err && resp.statusCode === 200)
        {
          var parsed = DjmlArticleParser.parse(body);
          var parsedStr = JSON.stringify(parsed);

          // For updates, re-use existing model
          if (article)
          {
            if (article.toJSON().content !== parsedStr)
            {
              article.content = parsedStr;
            }
          }
          else
          {
            article = new Article({ content: parsedStr, internalId: parsed.id });
          }

          if (article.isModified())
          {
            article.save(); // don't wait on this
          }
          res.send(article.content);
        }
        else
        {
          res.status(404).end();
        }
      });
    }
  });
});

module.exports = router;
