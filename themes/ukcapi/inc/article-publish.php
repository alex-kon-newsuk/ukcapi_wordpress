<?php
/**
 * Mansion Theme article publish.
 *
 * @author Radimir Bitsov
 *
 * @package Mansion
 */

/**
 * Publish request WP hook
 *
 * @return object
 */
function ms_publish_article($external_articleId, $wp_articleId, $target_env, $target_locale)
{
//    $publish_prod_cat = get_category_by_slug('published-production');
//    $publish_prod_cat_id = $publish_prod_cat->term_id;
//
//    $publish_live_cat = get_category_by_slug('published-live');
//    $publish_live_cat_id = $publish_live_cat->term_id;
//
//    $publish_staging_cat = get_category_by_slug('published-staging');
//    $publish_staging_cat_id = $publish_staging_cat->term_id;

    $curatedLink = get_category_by_slug('curatedlink');

    foreach((get_the_category( $wp_articleId )) as $category)
    {
        if ($category->cat_name == $curatedLink->name)
        {
            $isCuratedLink = true;
                break;
        }
    }

    $article = get_post($wp_articleId);
    $article_meta = get_post_meta($wp_articleId);

    $today = date('F j, Y');

    $formatted_article_body = wpautop($article->post_content);
    $image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id( $article->ID ) );
    $curatedLinkId = null;
    if($isCuratedLink == true)
    {
        $curatedLinkId = $article_meta['curated_link_id_' . $target_env][0];

        //
        // Url field must never be empty for a link
        //
        if(empty($article_meta['Url'][0]))
        {
            $article_meta['Url'][0] = 'http://www.mansionglobal.com';
        }

        $article_data = array(
            'curated_link' => array(
                'url'               => $article_meta['Url'][0],
                'title'             => $article->post_title,
                'headline'          => $article_meta['Headline'][0],
                'sub_headline'      => $article_meta['Subtitle'][0],
                'quote'             => $article_meta['Quote'][0],
                'credit'            => $article_meta['credit'][0],
                //'image'             => array('url' => $image_attributes[0]),
                'language_isocode'  => $target_locale
            )
        );
    }
    else if ($article_meta['hasGallery'][0] == 'true')
    {
        $topGalleryItemsCount = $article_meta['galleryItemsNumber'][0];
        $bottomGalleryItemsCount = isset($article_meta['bottomGalleryItemsNumber']) ? $article_meta['bottomGalleryItemsNumber'][0] : 0;

        if ($topGalleryItemsCount > 0)
        {
            $header_media = array('position' => 'header', 'media' => array());
            for ($gidx = 0; $gidx < $topGalleryItemsCount; $gidx++)
            {
                if(isset($article_meta['gallery_video_' . $gidx . '_url']))
                {
                    array_push($header_media['media'],
                        array(
                            'media_type' => 'video',
                            'image_caption' => $article_meta['gallery_caption_' . $gidx][0],
                            'image_credit' => $article_meta['gallery_img_' . $gidx . '_credit'][0],
                            'image_small_url' => $article_meta['gallery_img_' . $gidx][0],
                            'image_medium_url' => $article_meta['gallery_img_' . $gidx][0],
                            'image_large_url' => $article_meta['gallery_img_' . $gidx][0],
                            'action_url' => $article_meta['gallery_video_' . $gidx . '_url'][0],
                        ));
                }
                else if(isset($article_meta['gallery_infographic_url_' . $gidx]) && isset($article_meta['gallery_action_url_' . $gidx]))
                {
                     array_push($header_media['media'],
                         array(
                             'media_type' => 'image',
                             'image_caption' => $article_meta['gallery_caption_' . $gidx][0],
                             'image_credit' => $article_meta['gallery_img_' . $gidx . '_credit'][0],
                             'image_small_url' => $article_meta['gallery_img_' . $gidx][0],
                             'image_medium_url' => $article_meta['gallery_img_' . $gidx][0],
                             'image_large_url' => $article_meta['gallery_img_' . $gidx][0],
                             'action_url' => $article_meta['gallery_infographic_url_' . $gidx][0],
                         ));
                }
                else
                {
                    array_push($header_media['media'],
                        array(
                            'media_type' => 'image',
                            'image_caption' => $article_meta['gallery_caption_' . $gidx][0],
                            'image_credit' => $article_meta['gallery_img_' . $gidx . '_credit'][0],
                            'image_small_url' => $article_meta['gallery_img_' . $gidx][0],
                            'image_medium_url' => $article_meta['gallery_img_' . $gidx][0],
                            'image_large_url' => $article_meta['gallery_img_' . $gidx][0],
                        ));
                }
            }
        }

        $tmp = array_reverse($header_media['media']);
        $header_media['media'] = $tmp;

        if ($bottomGalleryItemsCount > 0)
        {
            $inline_media = array('position' => 'inline', 'media' => array());
            for ($gidx = 0; $gidx < $bottomGalleryItemsCount; $gidx++)
            {
                if(isset($article_meta['bottomGallery_video_' . $gidx . '_url']))
                {
                    array_push($inline_media['media'],
                        array(
                            'media_type' => 'video',
                            'image_caption' => $article_meta['bottomGallery_caption_' . $gidx][0],
                            'image_credit' => $article_meta['bottomGallery_img_' . $gidx . '_credit'][0],
                            'image_small_url' => $article_meta['bottomGallery_img_' . $gidx][0],
                            'image_medium_url' => $article_meta['bottomGallery_img_' . $gidx][0],
                            'image_large_url' => $article_meta['bottomGallery_img_' . $gidx][0],
                            'action_url' => $article_meta['bottomGallery_video_' . $gidx . '_url'][0],
                        ));
                }
                else
                {
                    array_push($inline_media['media'],
                        array(
                            'media_type' => 'image',
                            'image_caption' => $article_meta['bottomGallery_caption_' . $gidx][0],
                            'image_credit' => $article_meta['bottomGallery_img_' . $gidx . '_credit'][0],
                            'image_small_url' => $article_meta['bottomGallery_img_' . $gidx][0],
                            'image_medium_url' => $article_meta['bottomGallery_img_' . $gidx][0],
                            'image_large_url' => $article_meta['bottomGallery_img_' . $gidx][0],
                        ));
                }
            }
        }

        $article_data = array(
            'article' => array(
                'body_html' => $formatted_article_body,
                'body_prologue' => isset($article_meta['bodyPrologue']) ? wpautop($article_meta['bodyPrologue'][0])  : '',
                'article_media' => array($header_media, $inline_media),
                'body_footer' => isset($article_meta['bodyFooter']) ? wpautop($article_meta['bodyFooter'][0]) : '',
                'byline' => $article_meta['byLine'][0],
                'credit' => $article_meta['sourcePub'][0],
                'credit_url' => $article_meta['originUrl'][0],
                'external_id' => $external_articleId,
                'intro_long' => isset($article_meta['intro_long']) ? $article_meta['intro_long'][0] : '',
                'intro_short' => $article_meta['subtitle'][0],
                'language_isocode' => $target_locale, // ... to do .... $target_locale
                'published_at' => $article_meta['pubDate'][0],
                'title_tiny' => $article_meta['titleTinyForm'][0],
                'title_long' => $article_meta['titleLongForm'][0],
                'title_short' => $article_meta['titleShortForm'][0]
            )
        );
    }
    else
    {
        $image_media = array('position' => 'header', 'media' => array());
        array_push($image_media['media'],
                    array(
                        'media_type' => 'image',
                        'image_caption' => $article_meta['imageCaption'][0],
                        'image_credit' => $article_meta['imageCredit'][0],
                        'image_small_url' => ($article_meta['imageSmall'][0] ? $article_meta['imageSmall'][0] : null),
                        'image_medium_url' => ($article_meta['imageMedium'][0] ? $article_meta['imageMedium'][0] : null),
                        'image_large_url' => ($article_meta['imageHero'][0] ? $article_meta['imageHero'][0] : null)
                    ));

        $article_data = array(
            'article' => array(
                'body_html' => $formatted_article_body,
                'body_prologue' => isset($article_meta['bodyPrologue']) ? wpautop($article_meta['bodyPrologue'][0])  : '',
                'article_media' => array($image_media),
                'body_footer' => isset($article_meta['bodyFooter']) ? wpautop($article_meta['bodyFooter'][0]) : '',
                'byline' => $article_meta['byLine'][0],
                'credit' => $article_meta['sourcePub'][0],
                'credit_url' => $article_meta['originUrl'][0],
                'external_id' => $external_articleId,
//                'image_caption' => $article_meta['imageCaption'][0],
//                'image_credit' => $article_meta['imageCredit'][0],
//                'image_small_url' => $article_meta['imageSmall'][0],
//                'image_medium_url' => $article_meta['imageMedium'][0],
//                'image_large_url' => $article_meta['imageHero'][0],
                'intro_long' => isset($article_meta['intro_long']) ? $article_meta['intro_long'][0] : '',
                'intro_short' => $article_meta['subtitle'][0],
                'language_isocode' => $target_locale,
                'published_at' => $article_meta['pubDate'][0],
                'title_tiny' => $article_meta['titleTinyForm'][0],
                'title_long' => $article_meta['titleLongForm'][0],
                'title_short' => $article_meta['titleShortForm'][0]
            )
        );
    }
    //
    // Create or update the item on the Mansion platform. Handle both Curated links and Articles
    //
    $result = ms_upsert_content_item($article_data, $target_env, $wp_articleId,
                                     $isCuratedLink ? $curatedLinkId : $external_articleId,
                                     $isCuratedLink ? "curatedlinks" : "articles", $isCuratedLink );
    return $result;
}

//-----------------------------------------------------------------------------------------------------------------------------------
//
//
//
//-----------------------------------------------------------------------------------------------------------------------------------
function ms_ajax_update_content_group_item_handler()
{
    if( !isset( $_POST['ms_afa_nonce'] ) || !wp_verify_nonce( $_POST['ms_afa_nonce'], 'ms_afa_nonce' ) )
        die('Permission denied');

    $external_articleId = $_POST['externalId'];
    $wp_articleId = $_POST['mansionId'];
    $target_env = $_POST['env'];
    $locale_iso = $_POST['locale_isocode'];
    if(empty($locale_iso))
    {
        $locale_iso = 'en-us';
    }

    $result = ms_publish_article($external_articleId, $wp_articleId, $target_env, $locale_iso);
    echo(json_encode($result));
    die();
}

add_action('wp_ajax_update_content_group_item', 'ms_ajax_update_content_group_item_handler');
add_action('wp_ajax_nopriv_update_content_group_item', 'ms_ajax_update_content_group_item_handler');

/**
 * Perform the publish request to Substantial CMS
 *
 * @param $url
 * @param $article_data
 * @param $env
 *
 * @return array
 */
function ms_send_article($url, $article_data, $env) {
    $output = array(
        'response'          => '',
        'httpCode'          => '',
        'env'               => $env,
        'lastPublished'     => ''
    );
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_PUT => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Token token="api-access-token"',
            'Content-Type: application/json'
        ),
        CURLOPT_POSTFIELDS => json_encode($article_data)
    ));

    // Send the request
    $response = curl_exec($ch);

    if (empty($response)) {
        // some kind of an error happened
        $output['response'] = 'Something went wrong!';
        $output['httpCode'] = '422';
        die(curl_error($ch));
    } else {
        // get info from the response
        $info = curl_getinfo($ch);
        curl_close($ch); // close cURL handler

        if (empty($info['http_code'])) {
            $output['response'] = 'No HTTP code was returned.';
            $output['httpCode'] = null;
            die('No HTTP code was returned');
        } else {
            $output['msg'] = json_decode($response, TRUE);
            $output['httpCode'] = $info['http_code'];
        }
    }

    // Print the date from the response
    return $output;
}

//-----------------------------------------------------------------------------------------------------------------------------------
//
// CuratedLink: $content_item_id == external content_id in platform for curated link object
// Article:     $content_item_id == external sourceId of article in originating platform (e.g. WSJ or WP for created articles)
//
//-----------------------------------------------------------------------------------------------------------------------------------
function ms_upsert_content_item($article_data, $env, $wp_post_id, $content_item_id, $article_type, $isCuratedLink)
{
    $output = array(
        'response'          => '',
        'httpCode'          => '',
        'env'               => $env,
        'lastPublished'     => ''
    );

    global $api_access_keys;
    $api_access_key = $api_access_keys[strtolower($env)];
    global $api_paths;
    $url = $api_paths[strtolower($env)][$article_type];
    $ch = null;

    //$url = "http://localhost:8089/api/curated_links/";

    //$curl_log = fopen("/tmp/curl.txt", 'w');
    //
    // Try and GET the article/curatedlink by id, we might not be able to rely on our internal custom
    // properties to indicate if the article exists on the target env
    //
    if(!empty($content_item_id))
    {
        $getUrl = $api_paths[strtolower($env)]['query_' . $article_type] . $content_item_id;
        $ch = curl_init($getUrl);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Authorization: '. $api_access_key,
                'Content-Type: application/json'
            )
        ));
        $response = curl_exec($ch);
        $r = curl_getinfo($ch);
    }

    //
    // We have no article/curatedlink id or our GET failed so assume we must create it
    // on the target env
    //
    if(empty($content_item_id) || $r['http_code'] == 404 || $r['http_code'] == 500 || $r['http_code'] == 503 || $r['http_code'] == 401)
    {
        if(isset($ch))
            curl_close($ch);

        $ch = curl_init($url);
        if($isCuratedLink)
        {
            if(isset($wp_post_id))
            {
                $f = get_attached_file(get_post_thumbnail_id($wp_post_id));
                if ($f)
                {
                    $mime_boundary = 'MANSION-GLOBAL----' . md5(time()) . '-------';
                    $multpart_payload = create_multipart_post_message($wp_post_id, $article_data, $mime_boundary);
                    $post_data = $multpart_payload['http']['content'];
                    curl_setopt_array($ch, array(
                        CURLOPT_URL => $url,
                        CURLOPT_POST => TRUE,
                        CURLOPT_RETURNTRANSFER => TRUE,
                        CURLOPT_HTTPHEADER => array(
                            'Authorization: ' . $api_access_key,
                            'Content-Type: multipart/form-data; boundary=' . $mime_boundary
                        ),
                        CURLOPT_POSTFIELDS => $post_data
                    ));
                }
                else
                {
                    curl_setopt_array($ch, array(
                        CURLOPT_URL => $url,
                        CURLOPT_POST => TRUE,
                        CURLOPT_RETURNTRANSFER => TRUE,
                        CURLOPT_HTTPHEADER => array(
                            'Authorization: ' . $api_access_key,
                            'Content-Type: application/json'
                        ),
                        CURLOPT_POSTFIELDS => json_encode($article_data)
                    ));
                }
            }
        }
        else
        {


            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: ' . $api_access_key,
                    'Content-Type: application/json'
                ),
                CURLOPT_POSTFIELDS => json_encode($article_data)
            ));
        }

    }
    else if($r['http_code'] == 200)
    {
        $ch = curl_init();

        //unset($article_data['article']['external_id']);
        if($isCuratedLink)
        {
            if(isset($wp_post_id))
            {
//                curl_setopt_array($ch, array(
//                    CURLOPT_URL => $getUrl,
//                    CURLOPT_CUSTOMREQUEST => "DELETE",
//                    CURLOPT_RETURNTRANSFER => TRUE,
//                    CURLOPT_HTTPHEADER => array(
//                        'Authorization: ' . $api_access_key )));
//                $response = curl_exec($ch);
//                curl_close($ch);
//                $ch = curl_init();

                $f = get_attached_file(get_post_thumbnail_id($wp_post_id));
                if ($f)
                {
                    $mime_boundary = 'MANSION-GLOBAL----' . md5(time()) . '-------';
                    $multpart_payload = create_multipart_post_message($wp_post_id, $article_data, $mime_boundary);
                    $post_data = $multpart_payload['http']['content'];
                    curl_setopt_array($ch, array(
                        CURLOPT_URL => $url,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_RETURNTRANSFER => TRUE,
                        CURLOPT_HTTPHEADER => array(
                            'Authorization: ' . $api_access_key,
                            'Content-Type: multipart/form-data; boundary=' . $mime_boundary
                        ),
                        CURLOPT_POSTFIELDS => $post_data
                    ));
                }
                else
                {
                    curl_setopt_array($ch, array(
                        CURLOPT_URL => $url,
                        CURLOPT_POST => TRUE,
                        CURLOPT_RETURNTRANSFER => TRUE,
                        CURLOPT_HTTPHEADER => array(
                            'Authorization: ' . $api_access_key,
                            'Content-Type: application/json'
                        ),
                        CURLOPT_POSTFIELDS => json_encode($article_data)
                    ));
                }
            }
        }
        else
        {
            $xx = json_encode($article_data);

            curl_setopt_array($ch, array(
                CURLOPT_URL => $url . $content_item_id,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: ' . $api_access_key,
                    'Content-Type: application/json'
                ),
                CURLOPT_POSTFIELDS => json_encode($article_data)
            ));
        }
    }
    else
    {
        echo json_encode($r);
        die();
    }

    $response = curl_exec($ch);
    $e = curl_errno($ch);
    $info = curl_getinfo($ch);

    curl_close($ch); // close cURL handler

//    if(isset($curl_log))
//    {
//        fclose($curl_log);
//    }

    if (empty($info['http_code']))
    {
        $output['response'] = 'No HTTP code was returned.';
        $output['httpCode'] = null;
        die('No HTTP code was returned');
    }
    else
    {
        $output['msg'] = json_decode($response, TRUE);

        if($isCuratedLink)
        {
            if($info['http_code'] == 201)
            {
                //
                // We created a new CuratedLink object
                //
                $content_item_id = $output['msg']['id'];
                update_post_meta($wp_post_id, 'curated_link_id_' . $env, $content_item_id);
                $output['httpCode'] = 201;
            }
            else if($info['http_code'] == 204)
            {
                //
                // We updated an existing CuratedLink object
                //
                $output['msg']['id'] = $content_item_id;
                update_post_meta($wp_post_id, 'curated_link_id_' . $env, $content_item_id);
                $output['httpCode'] = 201;
            }
            else
            {
                $output['httpCode'] = 400;
            }
        }
        else
        {
            if($info['http_code'] == 204 || $info['http_code'] == 201 || $info['http_code'] == 200)
            {
                $output['httpCode'] = 201;
            }
            else
            {
                $output['httpCode'] = 400;
            }
        }

    }

    $today = date('F j, Y h:i:s A');
    update_post_meta( $wp_post_id, 'LastPublished' . $env, $today, true ) || update_post_meta( $wp_post_id, 'LastPublished' . $env, $today );
    $publish_cat = get_category_by_slug('published-' . strtolower($env));
    $publish_cat_id = $publish_cat->term_id;
    wp_set_post_categories($wp_post_id, array($publish_cat_id), TRUE);

    // Print the date from the response
    return $output;
}

function create_multipart_post_message($contentItemId, $jsonArgs, $mime_boundary)
{
    $eol = "\r\n";
    $data = '--' . $mime_boundary . $eol;
    $args = $jsonArgs['curated_link'];

    foreach($args as $key => $value)
    {
        if (!empty($value))
        {
            $data .= 'Content-Disposition: form-data; name="curated_link[' . $key . ']"' . $eol . $eol;
            $data .= $value . $eol;
            next($args);
            $data .= '--' . $mime_boundary . $eol;
        }
    }

//    while ($v = current($args))
//    {
//        $k = key($args);
//        $data .= 'Content-Disposition: form-data; name="curated_link[' . $k . ']"' . $eol . $eol;
//        $data .= $v . $eol;
//        next($args);
//        $data .= '--' . $mime_boundary . $eol;
//    }

    $f = get_attached_file(get_post_thumbnail_id($contentItemId));
    $imagebase64 = null;
    if ($f)
    {
//        $data .= 'Content-Disposition: form-data; name="curated_link[image]; filename="' . $image . '";' . $eol . $eol;
//        $data .= 'Content-Type: image/jpeg' . $eol . $eol;
//        $data .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
//        //$data .= $image . $eol;
//        $data .= "--" . $mime_boundary . "--" . $eol;

        $image = $f;
        $image_file = fopen($image, 'r');
        $image_data = fread($image_file, filesize($image));

        $imagebase64 = base64_encode($image_data);

        //$data .= '--' . $mime_boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="curated_link[image]"; filename="' . md5(time()) . '.jpeg"' . $eol;
        $data .= 'Content-Type: image/jpeg' . $eol . $eol;
        //$data .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
        $data .= $image_data . $eol;
        $data .= "--" . $mime_boundary . "--" . $eol;

    }

    $params = array('http' => array(
        'method' => 'POST',
        'header' => 'Content-Type: multipart/form-data; boundary=' . $mime_boundary,
        'content' => $data

    ));
    return $params;

}
