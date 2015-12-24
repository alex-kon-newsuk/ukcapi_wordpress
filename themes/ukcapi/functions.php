<?php
/**
 * Mansion functions and definitions
 *
 * @author Radimir Bitsov
 *
 * @package Mansion
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 960; /* pixels */
}

if ( ! function_exists( 'ms_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function ms_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Mansion, use a find and replace
	 * to change 'ms' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'ms', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'ms' ),
	) );
	
	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );

	/*
	 * Enable support for Post Formats.
	 * See http://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside', 'image', 'video', 'quote', 'link'
	) );

	// Setup the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'ms_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );
}
endif; // ms_setup
add_action( 'after_setup_theme', 'ms_setup' );

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function ms_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'ms' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', 'ms_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function ms_scripts()
{
    wp_enqueue_style( 'ms-font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );

    wp_enqueue_style( 'ms-style', get_stylesheet_uri() );

	wp_enqueue_script( 'ms-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20120206', true );

	wp_enqueue_script( 'ms-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20130115', true );

    wp_enqueue_script( 'ms-responsive-slides', get_template_directory_uri() . '/js/responsiveslides.min.js', array('jquery'), '20130115', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
    {
		wp_enqueue_script( 'comment-reply' );
	}

//    wp_register_script('ms_ajax_filter_articles', get_template_directory_uri() . '/js/ajax-filter-articles.js', array('jquery'), null, true);
//    wp_enqueue_script('ms_ajax_filter_articles');
//
//    wp_register_script('ms_ajax_publish_article', get_template_directory_uri() . '/js/ajax-publish-article.js', array('jquery'), null, true);
//    wp_enqueue_script('ms_ajax_publish_article');
//
//    wp_enqueue_script( 'ms-placement-editor', get_template_directory_uri() . '/js/placement-editor.js', array('jquery'), '20140925', true );
//
//    wp_enqueue_script( 'ms-ajax-realtor-editor', get_template_directory_uri() . '/js/ajax-realtor-editor.js', array('jquery'), null, true );
//
//    wp_enqueue_script( 'ms-utils', get_template_directory_uri() . '/js/utils.js', array('jquery'), '20140825', true );
//
//    wp_localize_script( 'ms_ajax_filter_articles', 'ms_afa_vars', array(
//            'ms_afa_nonce' => wp_create_nonce( 'ms_afa_nonce' ),
//            'ajax_url' => admin_url( 'admin-ajax.php' ),
//            'template_url' => get_bloginfo('template_url')
//        )
//    );

//    wp_localize_script( 'ms_ajax_publish_article', 'ms_pa_vars', array(
//            'ms_pa_nonce' => wp_create_nonce( 'ms_pa_nonce' ),
//            'ajax_url' => admin_url( 'admin-ajax.php' )
//        )
//    );
//
//    wp_localize_script( 'ms-ajax-realtor-editor', 'ms_re_vars', array(
//            'ms_re_nonce' => wp_create_nonce( 'ms_re_nonce' ),
//            'ajax_url' => admin_url( 'admin-ajax.php' )
//        )
//    );
}
add_action( 'wp_enqueue_scripts', 'ms_scripts' );

/**
 * Implement the Custom Header feature.
 */
//require get_template_directory() . '/inc/custom-header.php';


// require get_template_directory() . '/inc/vendor/aws/aws-sdk-php/src/Aws/S3/S3Client.php';


function my_formatter($content)
{
    $new_content = '';
    $pattern_full = '{(\[raw\].*?\[/raw\])}is';
    $pattern_contents = '{\[raw\](.*?)\[/raw\]}is';
    $pieces = preg_split($pattern_full, $content, -1, PREG_SPLIT_DELIM_CAPTURE);

    foreach ($pieces as $piece) {
        if (preg_match($pattern_contents, $piece, $matches)) {
            $new_content .= $matches[1];
        } else {
            $new_content .= wptexturize(wpautop($piece));
        }
    }

    return $new_content;
}

add_filter('the_content', 'wpautop');
//remove_filter('the_content', 'wptexturize');
//add_filter('the_content', 'my_formatter', 99);

/**
 * Custom template tags for this theme.
 */

require get_template_directory() . '/inc/custom-globals.php';


require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Load custom taxonomies definition file.
 */
require get_template_directory() . '/inc/custom-taxonomies.php';

/**
 * Load custom post definition file.
 */
require get_template_directory() . '/inc/custom-post-type.php';


require get_template_directory() . '/inc/article-meta.php';


require get_template_directory() . '/inc/content-item.php';


require get_template_directory() . '/inc/mpp-capi-publish.php';

//----------------------------------------------------------------------------------------------------
function ms_set_user_editor_metaboxes($user_id=NULL)
{
    $p = get_post();
    if(isset($p))
    {

    }
    // These are the metakeys we will need to update
    $meta_key['order'] = 'meta-box-order_post';
    $meta_key['hidden'] = 'metaboxhidden_post';
    // So this can be used without hooking into user_register
//    if ( ! $user_id)
//        $user_id = get_current_user_id();
//
//    // Set the default order if it has not been set yet
//    if ( ! get_user_meta( $user_id, $meta_key['order'], true) )
//    {
//        $meta_value = array(
//            'side' => 'submitdiv,formatdiv,categorydiv,postimagediv',
//            'normal' => 'postexcerpt,tagsdiv-post_tag,postcustom, commentstatusdiv,commentsdiv,trackbacksdiv,slugdiv,authordiv,revisionsdiv',
//            'advanced' => '',
//        );
//        update_user_meta( $user_id, $meta_key['order'], $meta_value );
//    }
//
//    // Set the default hiddens if it has not been set yet
//    if ( ! get_user_meta( $user_id, $meta_key['hidden'], true) )
//    {
//        $meta_value = array('postcustom','trackbacksdiv','commentstatusdiv','commentsdiv','slugdiv','authordiv','revisionsdiv');
//        update_user_meta( $user_id, $meta_key['hidden'], $meta_value );
//    }
}
add_action('admin_init', 'ms_set_user_editor_metaboxes');


//----------------------------------------------------------------------------------------------------
function ms_custom_field_list_size()
{
    return 200;
}
add_filter('postmeta_form_limit', 'ms_custom_field_list_size');

//----------------------------------------------------------------------------------------------------
function ms_set_default_meta($post_ID)
{
    $p = get_post($post_ID);

    switch($p->post_type)
    {
        case 'articles':

            $x = get_post_meta($post_ID, 'titleLongForm', true);
            if(empty($x))
                add_post_meta($post_ID,'titleLongForm','Long form title (desktop multi line format)',true);

            $x = get_post_meta($post_ID, 'titleShortForm', true);
            if(empty($x))
                add_post_meta($post_ID,'titleShortForm','Main title (normal section headline or list title)',true);

            $x = get_post_meta($post_ID, 'titleTinyForm', true);
            if(empty($x))
                add_post_meta($post_ID,'titleTinyForm','Ultra short form of title (mobile side bars lists etc)',true);

            $x = get_post_meta($post_ID, 'pubDate', true);
            if(empty($x))
                add_post_meta($post_ID,'pubDate',$p->post_date, true);

            $x = get_post_meta($post_ID, 'subtitle', true);
            if(empty($x))
                add_post_meta($post_ID,'subtitle','Deck/Standfirst (first summary line of article)',true);

            $x = get_post_meta($post_ID, 'imageCaption', true);
            if(empty($x))
                add_post_meta($post_ID,'imageCaption','caption...', true);

            $x = get_post_meta($post_ID, 'imageCredit', true);
            if(empty($x))
                add_post_meta($post_ID,'imageCredit','caption...', true);

            $x = get_post_meta($post_ID, 'imageCredit', true);
            if(empty($x))
                add_post_meta($post_ID,'imageCredit','caption...', true);

            $x = get_post_meta($post_ID, 'hasGallery', true);
            if(empty($x))
                add_post_meta($post_ID,'hasGallery','false',true);

            $x = get_post_meta($post_ID, 'sourcePublicationName', true);
            if(empty($x))
                add_post_meta($post_ID,'sourcePublicationName','TheSun',true);

            $x = get_post_meta($post_ID, 'sourcePub', true);
            if(empty($x))
                add_post_meta($post_ID,'sourcePub','TheSun',true);

            $x = get_post_meta($post_ID, 'imageHero', true);
            if(empty($x))
                add_post_meta($post_ID,'imageHero','TheSun',true);

            $x = get_post_meta($post_ID, 'sourceId', true);
            if(empty($x))
                add_post_meta($post_ID,'sourceId',$post_ID,true);

            $x = get_post_meta($post_ID, 'byLine', true);
            if(empty($x) || is_null($x))
            {
                $all = get_user_meta( $p->post_author );
                $byLine = $all["first_name"][0] . " " . $all["last_name"][0];
                update_post_meta($post_ID, 'byLine', $byLine, true);
            }

            if(empty($x))
                add_post_meta($post_ID,'byLine',get_the_author(),true);

            $x = get_post_meta($post_ID, 'sourceCms', true);
            if(empty($x))
                add_post_meta($post_ID,'sourceCms',get_permalink( $post_ID ),true);

            // Send to CAPI
            //
            // ms_process_capi_update($post_ID);

            break;

        case 'curatedlink':
//            $x = get_post_meta($post_ID, 'Url', true);
//            if(empty($x))
//                add_post_meta($post_ID,'Url','...',true);
//
//            $x = get_post_meta($post_ID, 'Headline', true);
//            if(empty($x))
//                add_post_meta($post_ID,'Headline','...',true);
//
//            $x = get_post_meta($post_ID, 'Subtitle', true);
//            if(empty($x))
//                add_post_meta($post_ID,'Subtitle','...',true);
//
//            $x = get_post_meta($post_ID, 'Quote', true);
//            if(empty($x))
//                add_post_meta($post_ID,'Quote','...',true);
//
//            $x = get_post_meta($post_ID, 'credit', true);
//            if(empty($x))
//                add_post_meta($post_ID,'credit','...',true);
//
//            wp_set_object_terms( $post_ID, 'Curatedlink', 'category');

            break;

        default:
            break;
    }
    $default_meta = '100'; // value

    return $post_ID;
}
add_action('wp_insert_post','ms_set_default_meta');


//----------------------------------------------------------------------------------------------------
function ms_add_custom_field_on_init($post_ID)
{
    global $wpdb;
    if(!wp_is_post_revision($post_ID))
    {
        //add_post_meta($post_ID, 'field-name', 'custom value', true);
    }
}
add_action('publish_curatedlink', 'ms_add_custom_field_on_init');
add_action('publish_article',     'ms_add_custom_field_on_init');
add_action('publish_post',        'ms_add_custom_field_on_init');
