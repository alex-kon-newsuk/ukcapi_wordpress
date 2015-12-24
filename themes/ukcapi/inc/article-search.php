<?php
/**
 * Created by PhpStorm.
 * User: cleavesp
 * Date: 3/30/15
 * Time: 10:20 AM
 */

function ms_ajax_search_articles()
{
    global $api_access_keys;
    $target_env = $_POST['env'];
    $article_search_type = $_POST['searchtype'];
    $search_term = $_POST['searchterm'];
    $results_order_by = $_POST['orderby'];

    $args = array(
        'post_type' => $article_search_type,
        's' => $search_term,
        'posts_per_page' => -1
    );
    $the_query = new WP_Query($args);
    $output = array();
    if ($the_query->have_posts())
    {
        while ($the_query->have_posts())
        {
            $the_query->the_post();
            //$html_item = ms_format_content_item_html(get_the_ID());
            //$html_item = esc_html($html_item);
            $image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID() ) );
            $content_categories = wp_get_post_categories( get_the_ID() );
            $isCuratedLink = false;

            foreach($content_categories as $c)
            {
                $cat = get_category( $c );
                if($cat->slug == 'curatedlink')
                {
                    $isCuratedLink = true;
                    break;
                }
            }

            array_push($output, array(
                'title' => get_the_title(),
                'body' => wp_trim_words( get_the_content(), 18),
                'byline' => get_post_meta(get_the_ID(), 'byLine', true),
                'sourcePubName' => get_post_meta(get_the_ID(), 'sourcePublicationName', true),
                'articlePlacementId' => get_post_meta(get_the_ID(), 'articlePlacementId_en_' . $target_env, true),

                'origin_url' => get_post_meta(get_the_ID(), 'originUrl', true),
                'sourceId' => get_post_meta(get_the_ID(), 'sourceId', true),
                'lastPublished' => get_post_meta(get_the_ID(), 'LastPublished' . $target_env, true),
                'thumbnail' => $image_attributes[0],
                'editlink'  => get_admin_url(null, '/post.php?post=' . get_the_ID() .'&action=edit'),
                'mansionid' => get_the_ID(),
                'previewLink' => get_permalink( get_the_ID() ),
                'wordpress_post_id' => get_the_ID(),
                'externalid' => get_post_meta(get_the_ID(), 'sourceId', true),

                'curatedLinkId' => get_post_meta(get_the_ID(), 'curated_link_id_' . $target_env, true),
                'quote' => get_post_meta(get_the_ID(), 'Quote', true),
                'headline' => get_post_meta(get_the_ID(), 'Headline', true),
                'sub_headline' => get_post_meta(get_the_ID(), 'Subtitle', true),
                'action_url' => get_post_meta(get_the_ID(), 'Url', true),
                'credit' => get_post_meta(get_the_ID(), 'credit', true),
                'isCuratedLink' => $isCuratedLink
            ));
        }
    }
    $response = array(
        'res'       => $output,
        'httpCode'  => '200',
    );
    $json = json_encode($response);
    echo($json);
    die();
}

add_action('wp_ajax_search_articles', 'ms_ajax_search_articles');
add_action('wp_ajax_nopriv_search_articles', 'ms_ajax_search_articles');