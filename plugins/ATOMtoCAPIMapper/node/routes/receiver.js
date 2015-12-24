//--------------------------------------------------------------------------------
//
//  ingester.js
//
//
//
//--------------------------------------------------------------------------------
var express = require('express');
var router = express.Router();
var request = require('request');
var config = require('../config');

//--------------------------------------------------------------------------------
//
// General JSON/FORM POST content handler
//
//--------------------------------------------------------------------------------
router.post('/', function(req, res, next)
{
    //
    // externalid is the handle to external content and consists of
    // source + sourcearticleid
    //

    var process = function(res, body)
    {
        var contentIngester = null;
        if(body.sourceid)
        {
            try
            {
                var parserPath = '../parsers/' + body.sourceid;
                console.log('loading parser from:', parserPath);
                contentIngester = require('../parsers/' + body.sourceid);
            }
            catch(e)
            {
                console.log('error creating ingester from:', body.sourceid);
                console.log('exception:', e.message);
                return res.status(400).end();
            }
        }

        contentIngester.processor(body, res, function(contentObject)
        {
            //
            // Create / save away article
            //
            var article = new Article({ content: JSON.stringify(contentObject), internalId: contentObject.id, externalId: contentObject.externalId });
            article.save();

            //
            // Add article to collection
            //
            // var query = Collection.findById(req.body.collectionid);
            var query = Collection.findOne({ name: req.body.collectionid });

            query.exec(function(err, collection)
            {
                if (!err && collection)
                {
                    //_.extend(collection, _.pick(req.body, ['name', 'friendlyId', 'pubId', 'tags', 'contents', 'metadata']));
                    //
                    collection.contents.push({ id:contentObject.id, type:"article" });
                    collection.save(function (err, updatedCollection)
                    {
                        if (err || !updatedCollection)
                        {
                            switch (err.name)
                            {
                                case 'ValidationError':
                                    //return res.status(400).json({errors: err.errors});
                                case 'MongoError':
                                    if (err.code === 11000 || err.code === 11001)
                                    { // duplicate key
                                        //return res.status(403).end();
                                    }
                                default:
                            }
                        }
                    });
                }
                else
                {
                    if(!collection)
                    {
                        console.log('Article not added to requested collection: Could not locate collection from id:', req.body.collectionid);
                    }
                    switch (err.name)
                    {
                        case 'CastError':
                            //return res.status(404).end();
                        default:
                           // return res.status(500).end();
                    }
                }
            });

            var responseBody = JSON.stringify({ "msg": "Item added successfully: " + contentObject.id, "success": "true" });
            console.log('returning: ', responseBody);
            res.set('Content-Type', 'application/json');
            return res.status(200).send(responseBody);
        });
    }

    //
    // We need a source so we can parse and we don't overwrite anything with same external-id
    //
    if(req.body.sourceid)
    {
        var q = Article.findOne({ 'externalId': req.body.externalid })
               .exec(function(err, article)
               {
                    if (!err && article )
                    {
                        var responseBody = JSON.stringify({ "msg": "Item exists: ", "success": "true" });
                        console.log('returning: ', responseBody);
                        res.set('Content-Type', 'application/json');
                        return res.status(200).send(responseBody);
                    }
                    process(res, req.body);
               });
    }
    else
    {
        console.log('error empty req.body.sourceid: ', req.body.sourceid);
        return res.status(400).end();
    }

});

module.exports = router;

