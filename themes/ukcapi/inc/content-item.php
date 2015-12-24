<?php
/**
 * Created by PhpStorm.
 * User: cleavesp
 * Date: 3/30/15
 * Time: 3:13 PM
 */

function ms_format_content_item_html($post_id)
{
    $output = '<li class=\'article-item clear\'>';

    if ( has_post_thumbnail() )
    {
        $output .= '<a class=\'alignleft\' href=\'' . the_permalink() . '\' title=\'' . the_title_attribute() . '\'>';
        $output .= get_the_post_thumbnail($post_id);
    }
    $output .= '<div class=\'article-body\'><h3><a href=\'' . the_permalink() . '\'>' . the_title() .'</a></h3>';
    $output .= '<p class=\'entry-content\'>';
    $trimmed_content = wp_trim_words( get_the_content(), 18, '<a class=\'read-more\' href=\''. get_permalink() .'\'>... Read More</a>' );
    $output .=  $trimmed_content;
    $output .= '</p>';

    if (get_post_meta(get_the_ID(), 'sourcePub', true))
    {
        $output .= '<div class=\'source-flag\'>';
        $output .= '<a href=\'' . get_post_meta(get_the_ID(), 'originUrl', true) . '\'></a></div>';

        $sourcePub = get_post_meta(get_the_ID(), 'sourcePub', true);

        if ($sourcePub === 'WSJ')
        {
            $output .=  '<img src=\'' . get_bloginfo( 'template_directory' ) . '/img/wsj_logo.png' . '\' alt=\'Source Logo\' />';
        }
        elseif($sourcePub === 'NEWSAUS')
        {
            $output .=  '<img src=\'' . get_bloginfo( 'template_directory' ) . '/img/newsaus_logo.png' . '\' alt=\'Source Logo\' />';
        }
        elseif($sourcePub === 'NEWSUK')
        {
            $output .=  '<img src=\'' . get_bloginfo( 'template_directory' ) . '/img/newsuk_logo.png' . '\' alt=\'Source Logo\' />';
        }
        elseif($sourcePub === 'NYPOST')
        {
            $output .=  '<img src=\'' . get_bloginfo( 'template_directory' ) . '/img/nypost_logo.png' . '\' alt=\'Source Logo\' />';
        }
    }

    $output .=  '</li>';
    return $output;
}