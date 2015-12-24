<?php defined('ABSPATH') or die('Plugin file cannot be accessed directly.'); ?>

<style type="text/css">
    .hub_small_font {
        font-size: 11px;
    }

    .hub_table_head {
        font-size: 12px;
        font-weight: bold;
        background-color: #e2e2e2;
    }
</style>

<h1>CAPI Mapping</h1>

<hr>

<div class="wrap">

    <h3>News UK: ATOM Article Sample</h3>

<?php
    $atom = '<?xml version="1.0" encoding="utf-8"?>
    <entry xmlns="http://www.w3.org/2005/Atom"
           xmlns:age="http://purl.org/atompub/age/1.0"
           xmlns:dcterms="http://purl.org/dc/terms/"
           xmlns:cpi="http://xmlns.new.co.uk/types"
           xmlns:web="http://thetimes.co.uk">
        <id>https://s3-eu-west-1.amazonaws.com/thesun-atomdocs-uat/97c64f20-cb67-11e4-a202-50ac5def393a</id>
        <identifier>C04493AA-04BF-4E91-B2C4-8BD36433168F</identifier>
        <title>AnothersuccessfulRoundTrip</title>
        <cpi:shorttitle>AnothersuccessfulRoundTrip</cpi:shorttitle>
        <cpi:strapline>AnothersuccessfulRoundTrip</cpi:strapline>
        <cpi:subtitle>AnothersuccessfulRoundTrip</cpi:subtitle>
        <updated/>
        <published>2015-03-23T23:06:34Z</published>
        <cpi:editiondate>2015-03-23</cpi:editiondate>
        <cpi:publication>sun</cpi:publication>
        <cpi:times_templateid>default</cpi:times_templateid>
        <cpi:leadassetid>31bdb7c7-1339-442b-8483-319a70f5ee7d</cpi:leadassetid>
        <version>1</version>
        <region/>
        <category term="article"/>
        <cpi:times_commentsenabled>true</cpi:times_commentsenabled>
        <author>
            <name>abc</name>
        </author>
        <content type="xhtml">
            <div xmlns="http://www.w3.org/1999/xhtml">
                <p>123</p>
            </div>
        </content>
        <age:expires/>
        <cpi:status>WebFlow/Published</cpi:status>
    </entry>';

$capi_ingest_json = '{
    "content": {
    "body": "THE new Star Wars film has been handed a 12A rating due to scenes of violence, mild bad language and threat ...",
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
        "originId": "1234567",
        "originalAssetId": "1234567",
        "originalSource": "THESUN",
        "paidStatus": "NON_PREMIUM",
        "standFirst": "Under 12s will need to be accompanied by an adult to see The Force Awakens",
        "title": "Kid panic after new Star Wars movie is handed a 12A rating"
    },
    "transactionId": "transaction-thesun-12345"
}';

// Read in node map file

$map = ' {
    "language": "/article-doc/@language",
    "subsection": "/article-doc/@type",
    "source": "/article-doc/@orig-obj-src",
    "product": "/article-doc/@product",
    "url": "/article-doc/@url"
}';
$mapping = json_decode($map);

echo '<pre>';
echo htmlspecialchars($atom);
echo '</pre>';

echo '<hr/>';

echo '<h3>News UK: AU CAPI JSON Sample</h3>';

echo '<pre>';
echo $capi_ingest_json;
echo '</pre>';

echo '<hr/>';

?>

    <form id="hub_config_form" action="wp-admin/admin-post.php" method="post" style="width: 100%"
          onkeypress="return event.keyCode != 13;">
        <input type="hidden" name="action" value="save_config">

        <table>
            <tr>
                <td>Hub URL</td>
                <td><input size="60" type="text" name="defaultHubUrl" value=""></td>
            </tr>
            <tr>
                <td>Callback URL</td>
                <td><input type="text" size="60" name="callbackUrl" value=""></td>
            </tr>

            <tr>
                <td colspan="2"><input type="checkbox" name="acceptRawDelivery" >Accept Raw Delivery (check if subscribing to Hub 1.0)
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type="button"
                            onclick="hubConfigSubmitForm('#hub_config_form', '#config_status_message');">
                        Save
                    </button>
                </td>
            </tr>
        </table>

        <div id="config_status_message"></div>
    </form>

    <div id="hub_actions" >
        <hr>

        <h3>Publish</h3>

        <form id="hub_publish_form" action="wp-admin/admin-post.php" method="post" style="width: 100%">
            <input type="hidden" name="action" value="hub_publish">

            <table>
                <tr>
                    <td>Topic Name</td>
                    <td><input type="text" size="40" name="topicName"> (e.g. http://domain.co.uk/sports/football)</td>
                </tr>
                <tr>
                    <td>Content</td>
                    <td><textarea name="content" cols="59" rows="10"></textarea></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button type="button"
                                onclick="hubSubmitForm('#hub_publish_form', '#publish_status_message');">
                            Publish to Topic
                        </button>
                    </td>
                </tr>
            </table>
            <div id="publish_status_message"></div>
        </form>

        <hr>

        <h3>Subscribe</h3>

        <form id="hub_subscribe_form" action="wp-admin/admin-post.php" method="post" style="width: 100%"
              onkeypress="return event.keyCode != 13;">
            <input type="hidden" name="action" value="hub_subscribe">

            <table>
                <tr>
                    <td>Topic Name</td>
                    <td><input type="text" size="40" name="topicName"> (e.g. http://domain.co.uk/sports/football)</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button type="button"
                                onclick="hubSubmitForm('#hub_subscribe_form', '#subscribe_status_message');">
                            Subscribe to Topic
                        </button>
                    </td>
                </tr>
            </table>

            <div id="subscribe_status_message"></div>
        </form>

        <hr>

    </div>

    <hr>

    <h3>Subscriptions</h3>

    <table width="700">
        <tr class="hub_table_head">
            <td width="75%" style="font-weight: bold">Topic ARN</td>
            <td width="25%" style="font-weight: bold">Creation Date</td>
        </tr>
<!--        --><?php
//        foreach ($subscriptions as $subscription) {
//            ?>
<!--            <tr class="hub_small_font">-->
<!--                <td>--><?php //echo $subscription->topic_arn ?><!--</td>-->
<!--                <td>--><?php //echo $subscription->creation_date ?><!--</td>-->
<!--            </tr>-->
<!--            --><?php
//        }
//        ?>
    </table>

    <hr>

    <h3>Published Messages Received</h3>

    <table width="100%">
        <tr class="hub_table_head">
            <td style="font-weight: bold">ID</td>
            <td style="font-weight: bold">Topic Arn</td>
            <td style="font-weight: bold">Origin Topic Name</td>
            <td style="font-weight: bold">Content-Type</td>
            <td style="font-weight: bold">Message Date</td>
            <td style="font-weight: bold">Received Date</td>
        </tr>

    </table>
</div>