//----------------------------------------------------------------------
//
// Render feed based on collections or queries across collections
//
//----------------------------------------------------------------------
var util = require('../util/utility');
var async = require('async');
var express = require('express');
var path = require('path');
var router = express.Router();
var validator = require('validator');
var _ = require('lodash');
var config = require('../config');
var Collection = require('../models/collection');
var Article = require('../models/article');

//------------------------------------------------------------
//
//
//------------------------------------------------------------
router.param('name', function(req, res, next, name)
{
  var query = Collection.findOne({ name: name });

  query.exec(function(err, collection) {
    if (err) {
      switch (err.name) {
      case 'CastError':
        return res.status(404).end();
      default:
        return res.status(500).end();
      }
    }
    if (!collection) return res.status(404).end();

    req.collection = collection;
    next();
  });
});

//------------------------------------------------------------
//
//
//------------------------------------------------------------
router.get('/', function(req, res)
{
  if(req.query.fmt === 'json')
  {
    res.setHeader('Content-Type', 'application/json');
    var feedObject = {};
    feedObject.description = 'MPP CAPI Feeds';

    var query = Collection.find({});

    query.exec(function(err, collections)
    {
      if (err) return res.status(500).end();

      if (!collections) return res.status(404).end();

      feedObject.items = [];
      feedObject.items = _.map(collections, function (col) {
        return {
          id: col.id,
          artcount: col.contents.length,
          link: '/feeds/' + col.name,
          //absoluteUrl: req.headers.host + config['api-root-path'] + 'feeds/' + col.name,
          title: col.name
        };
      });
      res.json(feedObject);
      return res.status(200).end();
    });
  }
  else if(req.query.fmt === 'html')
  {
    res.setHeader('Content-Type', 'text/html');
    var query = Collection.find({});
    query.exec(function(err, collections)
    {
      if (err) return res.status(500).end();

      if (!collections) return res.status(404).end();

      res.write('<html><body>');
      res.write('<h1>');
      res.write('Available CAPI Feeds');
      res.write('</h1>');
      _.each(collections, function(col)
      {
        var link = 'http://'+req.headers.host+config['api-root-path']+'feeds/' + col.name;
        var relativeLink = '/feeds/' + col.name;
        res.write('<p>Name: ' + col.name + '  Article Count (' + col.contents.length + ')<br/>');
        res.write('<p><a href="' + link + '?fmt=xml"  target="_new">XML Version (' + link + '?fmt=xml' + ')</a>');
        res.write('<p><a href="' + link + '?fmt=json" target="_new">JSON Version (' + link + '?fmt=json' + ')</a>');
        res.write('<p><a href="' + link + '?fmt=html" target="_new">HTML Version (' + link + '?fmt=html' + ')</a>');

        res.write('<p><a href="' + relativeLink + '?fmt=xml"  target="_new">Relative XML Version (' + relativeLink + '?fmt=xml' + ')</a>');
        res.write('<p><a href="' + relativeLink + '?fmt=json" target="_new">Relative JSON Version (' + relativeLink + '?fmt=json' + ')</a>');
        res.write('<p><a href="' + relativeLink + '?fmt=html" target="_new">Relative HTML Version (' + relativeLink + '?fmt=html' + ')</a>');

        res.write('<hr/>')
      });

      res.write('</body></html>')
      return res.status(200).end();
    });
  }
  else
  {
    res.setHeader('Content-Type', 'text/xml');
    var query = Collection.find({});
    query.exec(function(err, collections)
    {
      if (err) return res.status(500).end();

      if (!collections) return res.status(404).end();

      res.write('<rss>');
      res.write('<channel>MPP CAPI Feeds</channel>');
      _.each(collections, function(col)
      {
        res.write('<item><guid>' + col.id + '</guid>');
        res.write('<artcount>'+col.contents.length+'</artcount>');
        res.write('<link type="absolute">http://'+req.headers.host+config['api-root-path']+'feeds/' + col.name + '</link>');
        res.write('<link type="relative">/feeds/' + col.name + '</link>');
        res.write('<title>' + col.name + '</title></item>');
      });
      res.write('</rss>')
      return res.status(200).end();
    });
  }

});

//------------------------------------------------------------
//
//
//------------------------------------------------------------
router.get('/:name', function(req, res)
{
  var renderer = null;

  if(req.query.fmt === 'json')
  {
    res.setHeader('Content-Type', 'application/json');
    renderer = function(err, results)
    {
      var feedObject = {};
      feedObject.id = req.name;
      feedObject.items = [];

      var idsToData = _.extend.apply(null, [{}].concat(results));
      _.each(response.contents, function (content)
      {
        if (idsToData[content.id] && idsToData[content.id]._doc && idsToData[content.id]._doc.content)
        {
          var itemObject = {};

          var _art = JSON.parse(idsToData[content.id]._doc.content);
          var _author = !_.isEmpty(_art.authors) ? _art.authors[0].name : '';
          var _summary = !_.isEmpty(_art.deck) ? _art.deck : _art.headline;
          var _style = !_.isEmpty(_art.section) ? _art.section : 'default';
          var _bodyHtml = '';
          var _bodyLines = [];
          var _bodyText = _.reduce(_art.body, function (bodyResult, bodyItem)
          {
            if (bodyItem.tag == 'p') {
              _bodyHtml += '<p>' + bodyItem.text + '</p>';
              _bodyLines.push(bodyItem.text);
              return bodyResult + '\n' + bodyItem.text;
            }
            return bodyResult;
          }, '');

          var _sortedImages = _.sortBy(_art.summary.images, function (img)
          {
            return img.image.height * img.image.width;
          })
          var _imageToUse = _.last(_sortedImages);

          itemObject.id = content.id;
          itemObject.author = _author;
          itemObject.publishedAt = _art.publishedAt;
          itemObject.title = _art.headline;
          itemObject.summary = _summary;
          itemObject.mainImageUrl = _imageToUse.image.src;
          itemObject.language = _art.language;
          itemObject.originUrl = _art.url;
          itemObject.bodyText = _bodyText;
          itemObject.bodyHtml = _bodyHtml;
          itemObject.bodyLines = _bodyLines;
          itemObject.images = _.map(_sortedImages, function (img) {
            return {
              caption: img.caption,
              credit: img.credit,
              url: img.image.src,
              height: img.image.height,
              width: img.image.width
            };
          });
          feedObject.items.push(itemObject);
        }
      });
      res.json(feedObject);
      return res.status(200).end();
    };

  }
  else if(req.query.fmt === 'html')
  {
    res.setHeader('Content-Type', 'text/html');
    renderer = function(err, results)
    {
      res.write('<html><body>no supported</body><html>')
      return res.status(200).end();
    }
  }
  else
  {
    res.setHeader('Content-Type', 'text/xml');
    renderer = function(err, results)
    {
      res.write('<?xml version="1.0" encoding="UTF-8" ?>');
      res.write('<rss Version="2.0" xmlns:media="http://video.search.yahoo.com/mrss" id="'+ collectionId + '" >');
      res.write('<channel>');
      res.write('<guid>' + response.id + '</guid>');
      res.write('<title>' + response.name + '</title>');
      res.write('</channel>');

      var idsToData = _.extend.apply(null, [{}].concat(results));
      _.each(response.contents, function(content)
      {
        if(idsToData[content.id] && idsToData[content.id]._doc && idsToData[content.id]._doc.content)
        {
          var _art = JSON.parse(idsToData[content.id]._doc.content);
          var _author = ! _.isEmpty(_art.authors) ? _art.authors[0].name : '';
          var _summary = ! _.isEmpty(_art.deck) ? _art.deck : _art.headline;
          var _style = ! _.isEmpty(_art.section) ? _art.section : 'default';
          var _bodyHtml = '';
          var _bodyLines = [];
          var _bodyCopy = _.reduce(_art.body, function(bodyResult, bodyItem)
          {
            if(bodyItem.tag == 'p')
            {
              _bodyHtml += '<p>' + bodyItem.text + '</p>';
              var strippedBody = bodyItem.text.replace('<p>', '').replace('</p>', '');
              _bodyLines.push(strippedBody);
              return bodyResult + '\n' + strippedBody;
            }
            return bodyResult;
          }, '');

          var _sortedImages = _.sortBy(_art.summary.images, function(img)
          {
            return img.image.height * img.image.width;
          })
          var _imageToUse = _.last(_sortedImages);

          res.write('<item>');
          res.write('<language>' + _art.language + '</language>');
          res.write('<guid>' + content.id + '</guid>');
          res.write('<link><![CDATA[' + _art.url + ']]></link>');
          res.write('<title><![CDATA[' + _art.headline + ']]></title>');
          res.write('<summary><![CDATA[' + _summary + ']]></summary>');
          res.write('<description><![CDATA[' + _bodyCopy + ']]></description>');
          res.write('<media:group>');
          _.each(_bodyLines, function(line) {
            res.write('<media:description medium="text/plain">');
              res.write('<![CDATA[' + line + ']]>');
            res.write('</media:description>');
          });
          res.write('</media:group>');
          res.write('<media:content medium="html"><![CDATA[' + _bodyHtml + ']]></media:content>');
          res.write('<pubDate>' + _art.publishedAt + '</pubDate>');
          res.write('<enclosure><![CDATA[' + _imageToUse.image.src + ']]></enclosure>');
          res.write('<author><![CDATA[' + _author + ']]></author>');
          res.write('<media:thumbnail url="' + util.escapeXmlString(_imageToUse.image.src) + '" />');
          res.write('<media:group>');
          _.each(_sortedImages, function(img) {
            res.write('<media:content medium="image" url="'
            + util.escapeXmlString(img.image.src) + '" height="'
            + img.image.height + '" width="'
            + img.image.width + '" />');
          });
          res.write('</media:group>');
          res.write('<style>' + _style + '</style>');
          res.write('<data>{}</data>');
          res.write('</item>');
        }
      });
      res.write('</rss>')
      return res.status(200).end();

    }
  }

  var response = req.collection.toJSON();
  var collectionId = req.collection.id;
  var collectionName = req.collection.name;

    var typeToIds = _.chain(req.collection.contents)
        .groupBy('type')
        .mapValues(function(v) {
          return _.pluck(v, 'id');
        })
        .value();

    async.parallel(
        [
          function(callback)
          {
            var ids = typeToIds.article;
            if (ids)
            {
              Article.find({
                internalId: { $in: ids }
              },  function(err, articles)
              {
                var articleIdsToData = _.zipObject(_.pluck(articles, 'internalId'), articles);
                callback(null, articleIdsToData);
              });
            } else
            {
              callback();
            }
          }
        ], renderer);

});

module.exports = router;
