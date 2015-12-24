//--------------------------------------------------------------------------------
//
//  News.com.au / TheAustralian.com Xml parser
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
var AUS_SCALAR_MAPPINGS = {
    url: '//newsStory/link/text()',
    publishedAt: '//newsStory/dateLive/text()',
    updatedAt: '//newsStory/dateUpdated/text()',
    headline: '//newsStory/description/text()'
};

//$article_meta['articleTitle'] = (string)$article_xml->xpath('title')[0];
//$article_meta['titleLongForm'] = (string)$article_xml->xpath('standFirst')[0];
//$article_meta['articleSubtitle'] = (string)$article_xml->xpath('subtitle')[0];
//$article_meta['sourcePublicationName'] = (string)$article_xml->xpath('originalSource')[0];
//$article_meta['articleBody'] = $article_xml->xpath('body')[0];
//$article_meta['imageThumbnail'] = (string)$article_xml->xpath('thumbnailImage/link')[0];
//$content_type = (string)$article_xml->xpath('references/element/contentType')[0];
//if ($content_type === 'IMAGE') {
//    $article_meta['articleImg'] = (string)$article_xml->xpath('related/element/domainLinks/element/link')[0];
//    $article_meta['imageCredit'] = (string)$article_xml->xpath('related/element/authors/element')[0];
//} else {
//    $article_meta['articleImg'] = (string)$article_xml->xpath('related/element/images/element[contentType="IMAGE"]/link')[0];
//}
//$attach_id = ms_save_resized_image_to_uploads($article_nau_obj['featuredImg']);
//$article_meta['imageCaption'] = (string)$article_xml->xpath('related/element/description')[0];


// discrete queries for retrieving specific properties to build into complex properties
var AUS_MAPPINGS = {
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

                _.merge(result, U.extractPathValueFromXml(bodyXmlDom, AUS_SCALAR_MAPPINGS));
                result.uniqId = U.createUUID();
                result.id =  xpath.select("//newsStory/id/text()", bodyXmlDom).toString();
                result.externalId = body.externalid;

                // Extract body and image
                result.body = [];
                var mainImage = '';
                var relatedImages = xpath.select("//newsStory/related/element/link/text()", bodyXmlDom);

                _.forEach(relatedImages, function(n)
                {
                    console.log(n);
                    result.body.push({
                        "tag": "media",
                        "image": {
                            "alternate-text": "image",
                            "height": null,
                            "width": null,
                            "src": n.toString()
                        },
                        "credit": "Unknown",
                        "caption": result.headline,
                        "alts": []
                    });
                })

                //
                result.selfCodes = [];
                result.language = 'en-us';

                // authors
                var author = xpath.select("//newsStory/authors/element/text()", bodyXmlDom).toString();
                result.authors = [ { "name": author, "topicId": "" }];

                // Summary Object
                result.summary = {};
                result.summary.alts = [];

                // Extract summary images
                var summaryHeadline = xpath.select("//newsStory/standFirst/text()", bodyXmlDom).toString();
                var flashline = xpath.select("//newsStory/kicker/text()", bodyXmlDom).toString();
                var subtitle = xpath.select("//newsStory/subtitle/text()", bodyXmlDom).toString();
                var title = xpath.select("//newsStory/title/text()", bodyXmlDom).toString();

                result.summary.body = [
                    {
                        "tag": "headline",
                        "main": title,
                        "deck": summaryHeadline,
                        "flashline": flashline,
                        "@": {}
                    }
                ];

                // Extract summary images
                var summaryImage = xpath.select("//newsStory/thumbnailImage/link/text()", bodyXmlDom);

                result.summary.images = [
                    {
                        "tag": "media",
                        "image": {
                            "alternate-text": "image",
                            "src": summaryImage.toString()
                        },
                        "credit": "NYPost",
                        "caption": null,
                        "alts": []
                    }
                ];

                var bodytext = xpath.select("//newsStory/body/text()", bodyXmlDom).toString();
                result.body.push({
                    "tag": "p",
                    "text": bodytext,
                    "@": {}
                });

                callback( result );
            }
        });
    }
};

