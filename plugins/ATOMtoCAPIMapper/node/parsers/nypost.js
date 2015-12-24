//--------------------------------------------------------------------------------
//
//  nypost Xml parser
//
//
//
//--------------------------------------------------------------------------------

var cheerio = require('cheerio');
var moment = require('moment-timezone');
var request = require('request');
var _ = require('lodash');
var xpath = require('xpath');
var dom = require('xmldom').DOMParser;
var U = require('../util/utility');

// scalar mappings from XML text() to object.property.value
var NYPOST_RSS_SCALAR_MAPPINGS = {
  url: '//item/link/text()',
  publishedAt: '//item/pubDate/text()',
  updatedAt: '//item/pubDate/text()',
  headline: '//item/title/text()',
  body: '//item/description/text()'
};

// discrete queries for retrieving specific properties to build into complex properties
var NYPOST_RSS_DISCRETE_MAPPINGS = {
    authors: "//item/*[local-name(.)='creator' and namespace-uri(.)='dc'/"
};

module.exports = {

    processor: function(body, res, callback)
    {
        console.log('processing: ', body);

        var requestData =
        {
              url: body.cmsapixml,
              qs:
              {
                  time: +new Date()
              },
              method: 'GET',
              headers:
              {
                'Custom-Header': 'Custom Value'
              }
        };

        request(requestData, function (error, response, responseBody)
        {
            if (error || response.statusCode !== 200)
            {
                return res.status(400).end();
            }
            else
            {
                var result = {};
                var bodyXmlDom = new dom().parseFromString(responseBody);
                var select = xpath.useNamespaces({"dc": "http://purl.org/dc/elements/1.1/"});

                _.merge(result, U.extractPathValueFromXml(bodyXmlDom, NYPOST_RSS_SCALAR_MAPPINGS));
                result.uniqId = U.createUUID();

                result.id = body.externalid
                result.externalId = body.externalid; //encodeURIComponent(xpath.select("//item/link/text()", bodyXmlDom).toString());;

                // No self codes for NYPost Feed item
                result.selfCodes = [];
                result.language = 'en-us';

                // authors
                var s1 = xpath.useNamespaces({"dc": "http://purl.org/dc/elements/1.1/"});
                var author = s1("//item/dc:creator/text()", bodyXmlDom);
                result.authors = [ { "name":author.toString(), "topicId": "" }];

                // Summary Object
                result.summary = {};
                result.summary.alts = [];

                // Extract summary images
                s1 = (xpath.useNamespaces({"media": "http://search.yahoo.com/mrss/"}));
                var summaryHeadline = s1("//item/media:title/text()", bodyXmlDom).toString();

                result.summary.body = [
                    {
                        "tag": "headline",
                        "main": result.headline,
                        "deck": summaryHeadline,
                        "flashline": summaryHeadline,
                        "@": {}
                    }
                ];

                // Extract summary images
                s1 = xpath.useNamespaces({"media": "http://search.yahoo.com/mrss/"});
                var summaryImage = s1("//item/media:thumbnail[@url]", bodyXmlDom);

                result.summary.images = [
                        {
                            "tag": "media",
                            "image": {
                                "alternate-text": "image",
                                "height": 0,
                                "width" : 0,
                                "src": summaryImage[0].attributes[0].nodeValue
                            },
                            "credit": "NYPost",
                            "caption": null,
                            "alts": []
                        }
                ];

                // Extract article
                var mainImage = xpath.select("//item/enclosure[@url]", bodyXmlDom);
                result.body = [];
                result.body.push({
                    "tag": "media",
                    "image": {
                        "alternate-text": "image",
                        "height": null,
                        "width": null,
                        "src": mainImage.toString()
                    },
                    "credit": "Unknown",
                    "caption": result.headline,
                    "alts": []
                });

                var bodytext = xpath.select("//item/description/text()", bodyXmlDom);

                result.body.push({
                    "tag": "p",
                    "text": _.isEmpty(bodytext[0]) ? bodytext.toString() : bodytext[0].nodeValue,
                    "@": {}
                });

                callback( result );
            }
        });
    }
};

