var express = require('express');
var router = express.Router();
var path = require('path');
var morgan = require('morgan')
var xpath = require('xpath');
var DOMParser = require('xmldom').DOMParser;
var XMLSerializer = require('xmldom').XMLSerializer;

var request = require('request');
var U = require('../util/utility');
var _ = require('lodash');

//
// to be placed in requre(...) block
//
var CAPI_ATOM_SCALAR_MAPPINGS = {
    identifier: '/atom:entry/atom:identifier/text()',
    published:  '/atom:entry/atom:published/text()',
    author:     '/atom:entry/atom:author/atom:name/text()',
    title:      '/atom:entry/atom:title/text()'
};

//
//
//
function ATOMtoCAPI(messageDataJson)
{
    return {
        "content": {
            "body": messageDataJson.content,
            "byline": messageDataJson.author,
            "categoryPaths": [
                {
                    "path": "/display/thesun.co.uk/news/testing",
                    "type": "display"
                }
            ],
            "commentsTotal": 0,
            "contentType": "NEWS_STORY",
            "dateLive": messageDataJson.published,
            "dateOriginUpdated": messageDataJson.published,
            "origin": "THESUN",
            "originId": messageDataJson.identifier,
            "originalAssetId": "1234567890",
            "originalSource": "THESUN",
            "paidStatus": "NON_PREMIUM",
            "standFirst": messageDataJson.title,
            "title": messageDataJson.title
        },
        "transactionId": "transaction-thesun-" + messageDataJson.identifier
    };
}

//
//
//
function CAPItoMPP()
{
    return {
        "content": {
            "body": "<p>THE new Star Wars film has been handed a 12A rating due to scenes of violence, mild bad language and threat.</p><p>It means kids under 12 hoping to see The Force Awakens will need to be accompanied by an adult.</p><p>The British Board of Film Classification said it has frequent scenes of moderate violence, including use of blasters, lightsabers, and dogfights between spaceships.</p><p>It also has occasional scenes of moderate threat, including characters interrogated using 'the Force' and being held at lightsaber-point.</p>",
            "byline": "",
            "categoryPaths": [
                {
                    "path": "/display/thesun.co.uk/showbiz/",
                    "type": "display"
                },
                {
                    "path": "/taxonomy/thesun.co.uk/starwars/",
                    "type": "taxonomy"
                }
            ],
            "commentsTotal": 0,
            "contentType": "NEWS_STORY",
            "dateLive": "2015-12-08T18:00:00.000Z",
            "dateOriginUpdated": "2015-12-08T18:00:00.000Z",
            "origin": "THESUN",
            "originId": "0000000002",
            "originalAssetId": "1234567",
            "originalSource": "THESUN",
            "paidStatus": "NON_PREMIUM",
            "standFirst": "Under 12s will need to be accompanied by an adult to see The Force Awakens",
            "title": "UK Article sent to AU Hosted CAPI"
        },
        "transactionId": "transaction-thesun-12345"
    };
}

router.post('/article-atom', function(req, res)
{
    var isATOM = req.rawBody.indexOf("http://www.w3.org/2005/Atom") > -1;

    if(isATOM == false)
    {
        console.log('------------------------- REJECTED - No Atom namespace found -----------------------');
        return res.status(200).end();
    }

    console.log('------------------------- ATOM -----------------------');
    console.log(req.rawBody);
    console.log('------------------------- E N D -----------------------');

    var bodyXmlDom = null;
    try
    {
        bodyXmlDom = new DOMParser().parseFromString(req.rawBody);
        if(bodyXmlDom == null || bodyXmlDom == undefined)
        {
            console.log("Rejecting .. bodyXmlDom == null .. Not XML !");
            return res.status(200).end();
        }

        var xmlTree = null;
        var contentNode = xpath.useNamespaces({"atom": "http://www.w3.org/2005/Atom"})("/atom:entry/atom:content/*", bodyXmlDom)[0];
        var szr = new XMLSerializer();
        xmlTree = szr.serializeToString(contentNode);
        var CAPIJson = {
            "content":xmlTree
        };
        _.merge(CAPIJson, U.extractPathValueFromXml(bodyXmlDom, {"atom": "http://www.w3.org/2005/Atom"}, CAPI_ATOM_SCALAR_MAPPINGS));
        console.log("Processing ATOM file format to AU CAPI format");

        console.log("Posting converted JSON to Hub");
        var CAPIBody = ATOMtoCAPI(CAPIJson);
        console.log(JSON.stringify(CAPIBody));

        request({
                url: "http://nu-hub-uat.elb.cps-dev.ntch.co.uk/publish",
                method: "POST",
                headers:
                {
                    "Content-Type": 'application/json',
                    "X-Hub-Dst":    'http://news.co.uk/thesun_capi/json'
                },
                json: true,
                body: CAPIBody
            },
            function (error, response, body)
            {
                console.log('Dispatching JSON to hub (http://news.co.uk/thesun_capi/json): ' + response.statusCode);
                return res.end();
            });

    }
    catch(e)
    {
        console.log("Rejecting ... Not XML !");
        return res.status(200).end();
    }

    return res.status(200).end();
});

//
//
//
router.post('/article-json', function(req, res)
{
    var isATOM = req.rawBody.indexOf("http://www.w3.org/2005/Atom") > -1;

    if(isATOM == true)
    {
        console.log('------------------------- REJECTED - Atom detected -----------------------');
        return res.status(200).end();
    }

    console.log('************************* JSON *************************');
    console.log(req.rawBody);

    request({
            url: "http://content.ingest.newsapi.com.au/dev-int/document/v1/",
            method: 'POST',
            headers:
            {
                'Content-Type':    'application/json',
                'X-Origin-System': 'thesun',
                'api_key':         'hu42utpqycb5jn8g3a3xgvyv'
            },
            json: true,
            body: req.rawBody
        },
        function (error, response, body)
        {
            console.log('Dispatching JSON to AU-CAPI document ingest API: ' + response.statusCode);
            console.log('************************* E N D ************************');
        });


    res.status(200);
});

module.exports = router;
