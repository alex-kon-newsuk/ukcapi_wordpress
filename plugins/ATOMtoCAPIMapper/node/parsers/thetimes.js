//--------------------------------------------------------------------------------
//
//  the-times Xml parser
//
//
//
//--------------------------------------------------------------------------------

var _ = require('lodash');
var U = require('../util/utility');

module.exports = {

    processor: function(body, res, callback)
    {
        console.log('processing: ', body);

        var result = {};
        var article = JSON.parse(body.article);

        result.uniqId = U.createUUID();
        result.id =  article.id;
        result.externalId = body.externalid;
        result.selfCodes = [];
        result.language = 'en-gb';

        // authors
        result.authors = [ { "name":article.author, "topicId": "" }];
        // Summary Object
        result.summary = {};
        result.summary.alts = [];
        result.headline = article.title;
        result.publishedAt = '';
        result.url = article.sourceCmsApi;

        result.summary.body = [
            {
                "tag": "headline",
                "main": article.title,
                "deck": article.title,
                "flashline": '',
                "@": {}
            }
        ];

        result.summary.images = [];
        result.body = [];

        _.forEach(article.images, function(n)
        {
            console.log(n);
            var imgItem = {
                "tag": "media",
                "image": {
                    "alternate-text": "image",
                    "height": null,
                    "width": null,
                    "src": n.imageYVersionUrl.toString()
                },
                "credit": n.photographer.toString(),
                "caption": n.caption.toString(),
                "alts": []
            };
            result.summary.images.push(imgItem);
            result.body.push(imgItem);
        })

        result.body.push({
            "tag": "p",
            "text": article.body,
            "@": {}
        });

        callback( result );
    }
};

