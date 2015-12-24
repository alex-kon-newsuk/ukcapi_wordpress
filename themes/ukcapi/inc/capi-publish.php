<?php

function ms_format_article_for_capi($postId)
{

    $article = get_post($postId);
    $article_meta = get_post_meta($postId);

    $today = date('F j, Y');

    $formatted_article_body = wpautop($article->post_content);
    $image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id( $article->ID ) );
    $curatedLinkId = null;

    $capiPayload = array(
        'article' => array(
            'body_html' => $formatted_article_body,
            'body_prologue' => isset($article_meta['bodyPrologue']) ? wpautop($article_meta['bodyPrologue'][0])  : '',
            'body_footer' => isset($article_meta['bodyFooter']) ? wpautop($article_meta['bodyFooter'][0]) : '',
            'byline' => $article_meta['byLine'][0],
            'credit' => $article_meta['sourcePub'][0],
            'credit_url' => $article_meta['originUrl'][0],
            'external_id' => '',
            'intro_long' => isset($article_meta['intro_long']) ? $article_meta['intro_long'][0] : '',
            'intro_short' => $article_meta['subtitle'][0],
            'language_isocode' => '', // ... to do .... $target_locale
            'published_at' => $article_meta['pubDate'][0],
            'title_tiny' => $article_meta['titleTinyForm'][0],
            'title_long' => $article_meta['titleLongForm'][0],
            'title_short' => $article_meta['titleShortForm'][0]
        )
    );

    // Print the date from the response

    return $capiPayload;
}

function ms_send_to_capi($url, $capi_data, $env)
{
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
        CURLOPT_POSTFIELDS => json_encode($capi_data)
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

function ms_process_capi_update($postId)
{
    $payload = ms_format_article_for_capi($postId);
    $response = ms_send_to_capi(null, $payload, 'codejam');
    return;
}

