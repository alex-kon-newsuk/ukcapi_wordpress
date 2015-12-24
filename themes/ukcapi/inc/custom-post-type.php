<?php
/**
 * Mansion Theme custom post type definition.
 *
 * @author Radimir Bitsov
 *
 * @package Mansion
 */

/**
 * Register custom post type - Article
 *
 * @return void
 */
function ms_custom_post_type() {
    $labels = array(
        'name'                => _x( 'Articles', 'Post Type General Name', 'ms' ),
        'singular_name'       => _x( 'Article', 'Post Type Singular Name', 'ms' ),
        'menu_name'           => __( 'Articles', 'ms' ),
        'parent_item_colon'   => __( 'Parent Article', 'ms' ),
        'all_items'           => __( 'All Articles', 'ms' ),
        'view_item'           => __( 'View Article', 'ms' ),
        'add_new_item'        => __( 'Add New Article', 'ms' ),
        'add_new'             => __( 'Add New', 'ms' ),
        'edit_item'           => __( 'Edit Article', 'ms' ),
        'update_item'         => __( 'Update Article', 'ms' ),
        'search_items'        => __( 'Search Article', 'ms' ),
        'not_found'           => __( 'Not Found', 'ms' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'ms' ),
    );


    $args = array(
        'label'               => __( 'articles', 'ms' ),
        'description'         => __( 'Articles collection', 'ms' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'excerpt', 'editor', 'author', 'thumbnail', 'revisions', 'custom-fields' ),
        // You can associate this CPT with a taxonomy or custom taxonomy.
        'taxonomies'          => array( 'category', 'placement_tag', 'location_tag', 'lifestyle_tag', 'featured_tag', 'origin_tag' ),
        /* A hierarchical CPT is like Pages and can have
         * parent and child items. A non-hierarchical CPT
         * is like Posts.
         */
        'rewrite'             => array('slug' => 'article'),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'page',
    );

    // Registering the Custom Post Type Articles
    register_post_type( 'articles', $args );




    $labels = array(
        'name'                => _x( 'CuratedLinks', 'Post Type General Name', 'ms' ),
        'singular_name'       => _x( 'CuratedLink', 'Post Type Singular Name', 'ms' ),
        'menu_name'           => __( 'CuratedLinks', 'ms' ),
        'parent_item_colon'   => __( 'Parent Article', 'ms' ),
        'all_items'           => __( 'All CuratedLinks', 'ms' ),
        'view_item'           => __( 'View CuratedLinks', 'ms' ),
        'add_new_item'        => __( 'Add New CuratedLinks', 'ms' ),
        'add_new'             => __( 'Add New', 'ms' ),
        'edit_item'           => __( 'Edit CuratedLinks', 'ms' ),
        'update_item'         => __( 'Update CuratedLinks', 'ms' ),
        'search_items'        => __( 'Search CuratedLinks', 'ms' ),
        'not_found'           => __( 'Not Found', 'ms' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'ms' ),
    );


    $args = array(
        'label'               => __( 'curatedlink', 'ms' ),
        'description'         => __( 'Curated Links collection', 'ms' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'thumbnail', 'title', 'author',  'custom-fields' ),
        // You can associate this CPT with a taxonomy or custom taxonomy.
        'taxonomies'          => array( 'category' ),
        /* A hierarchical CPT is like Pages and can have
         * parent and child items. A non-hierarchical CPT
         * is like Posts.
         */
        'rewrite'             => array('slug' => 'curatedlink'),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'page',
    );

    // Registering the Custom Post Type Articles
    register_post_type( 'curatedlink', $args );

}
add_action( 'init', 'ms_custom_post_type', 0 );


/**
 * update images article meta when post saved
 * 
 * @access public
 * @param mixed $meta_id
 * @param mixed $post_id
 * @param mixed $meta_key
 * @param mixed $meta_value
 * @return void
 */
function ms_updated_featured_image_postmeta($post_id) {
  // If this is just a revision, don't do anything.
	if ( wp_is_post_revision( $post_id ) )
		return;
		
  // need to be articles post type
  if ('articles' !== $_POST['post_type'])
    return;
    
  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
		
  $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
  $prev_thumbnail_id = get_post_meta($post_id, 'articleImg', true);
  
  // if we have a featured image
  if ($thumbnail_id) {
    $image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
  
    // if the image has changed
    if ($image && $thumbnail_id !== $prev_thumbnail_id) {
      $image_src = $image[0];
      
      $attach_id = ms_save_resized_image_to_uploads($image_src);
      
      // resize
      $image_hero = ms_get_image_url($attach_id, MS_IMAGE_SIZE_HERO);
      $image_med = ms_get_image_url($attach_id, MS_IMAGE_SIZE_MEDIUM);
      $image_small = ms_get_image_url($attach_id, MS_IMAGE_SIZE_SMALL);
      
      // save image
      update_post_meta($post_id, 'articleImg', $thumbnail_id);
      update_post_meta($post_id, 'imageHero', $image_hero);
      update_post_meta($post_id, 'imageMedium', $image_med);
      update_post_meta($post_id, 'imageSmall', $image_small);
    }
  }
  else {
    // no featured image, so empty image fields
    update_post_meta($post_id, 'articleImg', '');
    update_post_meta($post_id, 'imageHero', '');
    update_post_meta($post_id, 'imageMedium', '');
    update_post_meta($post_id, 'imageSmall', '');
  }
}
// add_action( 'save_post', 'ms_updated_featured_image_postmeta', 10, 4 );

function ms_save_to_capi($post_id)
{
        return;

        $output = array(
            'response'          => '',
            'httpCode'          => '',
            'env'               => $env,
            'lastPublished'     => ''
        );
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'http://content.codejam.events:3000/',
            CURLOPT_PUT => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($article_data)
        ));

        // Send the request
        $response = curl_exec($ch);

        if (empty($response))
        {
            // some kind of an error happened
            $output['response'] = 'Something went wrong!';
            $output['httpCode'] = '422';
            die(curl_error($ch));
        }
        else
        {
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

add_action( 'save_post', 'ms_save_to_capi', 10, 4 );

