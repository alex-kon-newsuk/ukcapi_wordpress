<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Mansion
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'ms' ); ?></a>

	<header id="masthead" class="wrap site-header" role="banner">
		<div class="site-branding">
            <h1 class="site-title">
                <a href="<?php echo esc_url( home_url( '/wp-admin' ) ); ?>" rel="home">
                    <img src="<?php bloginfo('template_directory') ?>/img/sun_logo.png" alt="sun Logo" />
                    <span><?php bloginfo( 'name' ); ?></span>
                </a>
            </h1>
		</div>

		<?php if (is_user_logged_in()) : ?>
		<nav id="site-navigation" class="main-navigation" role="navigation">
			<button class="menu-toggle"><?php _e( 'Primary Menu', 'ms' ); ?></button>
			<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
		</nav><!-- #site-navigation -->
		<?php endif; ?>
	</header><!-- #masthead -->

	<div id="content" class="site-content">
