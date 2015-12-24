var async = require('async');
var express = require('express');
var path = require('path');
var router = express.Router();
var validator = require('validator');
var _ = require('lodash');

var Collection = require('../models/collection');
var Article = require('../models/article');

// TODO: Factor out shared error handling logic from GET, POST, PUT, PATCH (and DELETE?)

// TODO: Implement `context` parameter as described in:
//       https://newscorpproduct.atlassian.net/wiki/display/CLOUD/Collection+API+design

router.param('id', function(req, res, next, id) {
  // Determine if looking up by Mongo ID or friendly ID
  var query = Collection.findById(id);
  if (!validator.isMongoId(id)) {
    query = Collection.findOne({ friendlyId: id });
  }

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

router.get('/', function(req, res) {
  var query = Collection.find({});
  if (req.query.pubId) {
    query.where({ pubId: req.query.pubId });
  }

  var tags = _.isString(req.query.tag) ? req.query.tag.split(',') : [];
  if (tags.length === 1) {
    query.where({ tags: tags[0] });
  } else if (tags.length > 1) {
    query.where('tags').all(tags);
  }

  query.exec(function(err, collections) {
    if (err) return res.status(500).end();
    if (!collections) return res.status(404).end();
    return res.json(collections);
  });
});

router.get('/:id/xml', function(req, res)
{
  res.setHeader('Content-Type', 'text/xml');
  var response = req.collection.toJSON();
  var collectionId = req.collection.id;
  var collectionName = req.collection.name;

  if (req.query.includeData)
  {
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
        ], function(err, results)
        {
          // Note: Content data ids will collide across types with undefined behavior
          //       (e.g., article and some-other-unimplemented type)

          res.write('<collection id="'+ collectionId + '" >');
          var idsToData = _.extend.apply(null, [{}].concat(results));
          _.each(response.contents, function(content)
          {
            if(idsToData[content.id] && idsToData[content.id]._doc && idsToData[content.id]._doc.content)
            {
              var art = JSON.parse(idsToData[content.id]._doc.content);
              res.write('<item><id>' + content.id + '</id>');
              res.write('<origin_url>' + art.url + '</origin_url>');
              res.write('<headline>' + art.headline + '</headline>');
              res.write('<body>' + art.headline + '</body>');
              res.write('<images>' + art.headline + '</images>');
              res.write('<pubDate>' + art.publishedAt + '</pubDate>');
              res.write('<title>' + art.headline + '</title></item>');
            }
          });

           res.write('</collection>')
          return res.status(200).end();
          //return res.json(response);
        });
  }
  else
  {
    return res.json(response);
  }
});

router.get('/:id', function(req, res) {
  var response = req.collection.toJSON();

  if (req.query.includeData)
  {
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
    ], function(err, results)
    {
        // Note: Content data ids will collide across types with undefined behavior
        //       (e.g., article and some-other-unimplemented type)
        var idsToData = _.extend.apply(null, [{}].concat(results));
        _.each(response.contents, function(content) {
          content.data = idsToData[content.id];
        });
        return res.json(response);
    });
  }
  else
  {
    return res.json(response);
  }
});

router.post('/', function(req, res) {
  var unsavedCollection = new Collection(req.body);
  unsavedCollection.save(function(err, collection) {
    if (err || !collection) {
      switch (err.name) {
      case 'ValidationError':
        return res.status(400).json({ errors: err.errors });
      case 'MongoError':
        if (err.code === 11000 || err.code === 11001) { // duplicate key
          return res.status(403).end();
        }
        /* falls through */
      default:
        return res.status(500).end();
      }
    }

    var resourceUrl = path.join(req.baseUrl, req.path, collection.id).replace(/\\/g, '/');
    res.status(201).header('Location', resourceUrl).json(collection);
  });
});

router.put('/:id', function(req, res) {
  var collection = req.collection;

  // Overwrite all schema fields on found collection
  // TODO: Is there a way to inspect the fields instead? Or overwrite more directly?
  //       Look into https://www.npmjs.com/package/mongoose-multi-set
  _.each(['name', 'friendlyId', 'pubId', 'tags', 'contents', 'metadata'], function (field) {
    collection[field] = req.body[field];
  });

  collection.save(function(err, updatedCollection) {
    if (err || !updatedCollection) {
      switch (err.name) {
      case 'ValidationError':
        return res.status(400).json({ errors: err.errors });
      case 'MongoError':
        if (err.code === 11000 || err.code === 11001) { // duplicate key
          return res.status(403).end();
        }
        /* falls through */
      default:
        return res.status(500).end();
      }
    }

    var resourceUrl = path.join(req.baseUrl, updatedCollection.id).replace(/\\/g, '/');
    res.status(200).header('Location', resourceUrl).json(updatedCollection);
  });
});

router.patch('/:id', function(req, res) {
  var collection = req.collection;

  // Merge with existing resource
  // TODO: Inspect overridable properties from schema + see use in router.put
  _.extend(collection, _.pick(req.body, ['name', 'friendlyId', 'pubId', 'tags', 'contents', 'metadata']));

  collection.save(function(err, updatedCollection) {
    if (err || !updatedCollection) {
      switch (err.name) {
      case 'ValidationError':
        return res.status(400).json({ errors: err.errors });
      case 'MongoError':
        if (err.code === 11000 || err.code === 11001) { // duplicate key
          return res.status(403).end();
        }
        /* falls through */
      default:
        return res.status(500).end();
      }
    }

    var resourceUrl = path.join(req.baseUrl, updatedCollection.id).replace(/\\/g, '/');
    res.status(200).header('Location', resourceUrl).json(updatedCollection);
  });
});

router.delete('/:id', function(req, res) {
  req.collection.remove(function(err) {
    if (err) {
      switch (err.name) {
      case 'CastError':
        return res.status(400).end();
      default:
        return res.status(500).end();
      }
    }

    res.status(204).end();
  });
});

module.exports = router;
