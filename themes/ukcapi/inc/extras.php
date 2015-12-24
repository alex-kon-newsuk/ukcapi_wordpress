<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package Mansion
 */



/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * @param array $args Configuration arguments.
 * @return array
 */
function ms_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'ms_page_menu_args' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function ms_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	return $classes;
}
add_filter( 'body_class', 'ms_body_classes' );

/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
function ms_wp_title( $title, $sep ) {
	if ( is_feed() ) {
		return $title;
	}

	global $page, $paged;

	// Add the blog name
	$title .= get_bloginfo( 'name', 'display' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title .= " $sep $site_description";
	}

	// Add a page number if necessary:
	if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
		$title .= " $sep " . sprintf( __( 'Page %s', 'ms' ), max( $paged, $page ) );
	}

	return $title;
}
add_filter( 'wp_title', 'ms_wp_title', 10, 2 );

/**
 * Sets the authordata global when viewing an author archive.
 *
 * This provides backwards compatibility with
 * http://core.trac.wordpress.org/changeset/25574
 *
 * It removes the need to call the_post() and rewind_posts() in an author
 * template to print information about the author.
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @return void
 */
function ms_setup_author() {
	global $wp_query;

	if ( $wp_query->is_author() && isset( $wp_query->post ) ) {
		$GLOBALS['authordata'] = get_userdata( $wp_query->post->post_author );
	}
}
add_action( 'wp', 'ms_setup_author' );

/**
 * Hide the admin bar.
 */
add_filter('show_admin_bar', '__return_false');

/**
 * Replace the default WP logo on login form.
 */
function ms_login_logo() { ?>
    <style type="text/css">
        .login h1 a {
            height:184px;
        }

        .login label {
            color: #fff;
        }

        .login form {
            background:#E41616;
        }

        body.login div#login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/img/sun_logo.png);
            background-size: 320px 250px;
            width: 100%;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'ms_login_logo' );

/**
 * Checks if a particular user has a role.
 * Returns true if a match was found.
 *
 * @param string $role Role name.
 * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
 * @return bool
 */
function ms_check_user_role( $role, $user_id = null ) {
    if ( is_numeric( $user_id ) )
        $user = get_userdata( $user_id );
    else
        $user = wp_get_current_user();

    if ( empty( $user ) )
        return false;

    return in_array( $role, (array) $user->roles );
}

/**
 * Customize the WP default admin toolbar
 *
 * @param $wp_toolbar
 */
function ms_custom_admin_toolbar($wp_toolbar) {
    $wp_toolbar->remove_node('view-site');

    $wp_toolbar->add_menu( array(
        'parent' => 'site-name',
        'id'     => 'view-acapi-feeds-json',
        'title'  => __( 'View Article Feed AU-CAPI (JSON)' ),
        'meta'  => [ 'target' => '_new' ],
        'href'   => 'http://cdn.newsapi.com.au/dev-int/content/v2/?api_key=87865c9kf5wncy5vappm4tjv&query=categoryPaths.path:"/display/thesun.co.uk/news/testing/"',
    ) );

    $wp_toolbar->add_menu( array(
        'parent' => 'site-name',
        'id'     => 'view-acapi-feeds-xml',
        'meta'  => [ 'target' => '_new' ],
        'title'  => __( 'View Article Feed AU-CAPI (XML)' ),
        'href'   => 'http://wordpress.ukcapi.codejam.events:8080/content-service/feeds/categories/testnews',
    ) );

    $wp_toolbar->add_menu( array(
        'parent' => 'site-name',
        'id'     => 'view-mpp-curator',
        'title'  => __( 'Goto MPP Curator' ),
        'meta'  => [ 'target' => '_new' ],
        'href'   => 'http://pub.dev.aus-editions-curator-front.virginia.nc01.onservo.com/publications',
    ) );

}
add_action('admin_bar_menu', 'ms_custom_admin_toolbar', 999);

/**
 * Change the login logo link referencing the home page.
 */
function ms_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'ms_login_logo_url' );

/**
 * Change the login logo link title.
 */
function ms_login_logo_url_title() {
    return 'UK CAPI CMS';
}
add_filter( 'login_headertitle', 'ms_login_logo_url_title' );

function ms_login_logo_url_text() {
    return 'The Sun CAPI CMS';
}
add_filter('login_headertitle', 'ms_login_logo_url_text');

function my_logincustomCSSfile() {
    wp_enqueue_style('login-styles', get_template_directory_uri() . '/login/login_styles.css');
}
add_action('login_enqueue_scripts', 'my_logincustomCSSfile');


/**
 * Remove default post menu from the admin page.
 */
function ms_custom_admin_menu()
{
    remove_menu_page('edit.php');

    //add_menu_page('Evergreens','Evergreen','evergreen', 'edit.php');
    //add_menu_page('Article Templates','Article Templates','templates', 'edit.php');

    if (ms_check_user_role('editor'))
    {
        remove_menu_page( 'edit.php?post_type=page' );    //Pages
        remove_menu_page( 'edit-comments.php' );          //Comments
        remove_menu_page( 'themes.php' );                 //Appearance
        remove_menu_page( 'plugins.php' );                //Plugins
        remove_menu_page( 'users.php' );                  //Users
        remove_menu_page( 'tools.php' );                  //Tools
        remove_menu_page( 'options-general.php' );        //Settings
    }
}
add_action('admin_menu', 'ms_custom_admin_menu');

/**
 * Move Featured Image box at the top of the edit sidebar menu
 */
function ms_articles_image_box()
{
    remove_meta_box( 'postimagediv', 'articles', 'side' );
    add_meta_box('postimagediv', __('Featured Image'), 'post_thumbnail_meta_box', 'articles', 'side', 'high');
}

add_action('do_meta_boxes', 'ms_articles_image_box');



/**
 * ms_add_date_query function.
 * 
 * @access public
 * @param mixed $post_args
 * @return void
 */
function ms_add_date_query($post_args) {
  // add date params if there
  if (isset($_GET['date_query'])) {
    $date_query = $_GET['date_query'];
    switch ($date_query) {
      case 'last-week' :  $post_args['date_query'] = array('after' => '1 week ago'); break;
      case 'last-month' : $post_args['date_query'] = array('after' => '30 days ago'); break;
      case '30-60' :      $post_args['date_query'] = array('before' => '30 days ago', 'after' => '60 days ago'); break;
      case '60+' :        $post_args['date_query'] = array('before' => '60 days ago'); break;
      case 'all' :        break;
    }
  }
  
  return $post_args;
}

/**
 * ms_output_pagination function.
 * 
 * @access public
 * @param mixed $the_query
 * @return void
 */
function ms_output_pagination($the_query) {
      global $paged, $page;
      
      $param = is_home() ? '?page=' : '?paged=';
      $the_page = is_home() ? $page : $paged;

      if ($the_query->max_num_pages > 1) :?>
        <?php $the_page = $the_page == 0 ? 1 : $the_page; ?>
          <p class="page-nav">
            Pages: 
          <?php
            if ($the_page > 1) : ?>
              <a href="<?php echo $param . ($the_page -1); //prev link ?>">&laquo;</a>
          <?php endif;
          for ($i=1;$i<=$the_query->max_num_pages;$i++) :
                $is_current = ($the_page==$i); ?>
              <a href="<?php echo $param . $i; ?>" <?php echo $is_current ? 'class="selected"':'';?>><?php echo $is_current?'[ ':''; ?><?php echo $i;?><?php echo $is_current?' ]':''; ?></a>
              <?php
          endfor;
          if ($the_page < $the_query->max_num_pages) :?>
              <a href="<?php echo $param . ($the_page + 1); //next link ?>">&raquo;</a>
    <?php endif; ?>
          </p>
    <?php endif;
}