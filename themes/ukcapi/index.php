<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Mansion
 */

define('MS_POSTS_PER_PAGE', -1);

if (!is_user_logged_in()) {
    auth_redirect();
}

get_header(); ?>

	<div id="primary" class="wrap">
        <h1 class="main-title">The Sun CMS / CAPI</h1>

	</div><!-- #primary -->

<?php get_footer(); ?>
