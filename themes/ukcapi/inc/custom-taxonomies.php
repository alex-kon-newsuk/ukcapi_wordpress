<?php
/**
 * Mansion Theme custom taxonomies definition.
 *
 * @author Radimir Bitsov
 *
 * @package Mansion
 */

/**
 * Register custom taxonomies - location, lifestyle and featured tags.
 *
 * @return void
 */
function ms_add_custom_taxonomies() {
    // Add new "Placement Tag" taxonomy to Articles
    register_taxonomy('placement_tag', 'article', array(
        'hierarchical'          => false,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'placement-tags' ),
        // This array of options controls the labels displayed in the WordPress Admin UI
        'labels' => array(
            'name'                       => _x( 'Placement Tags', 'taxonomy general name' ),
            'singular_name'              => _x( 'Placement Tag', 'taxonomy singular name' ),
            'search_items'               => __( 'Search Placement Tags' ),
            'popular_items'              => __( 'Popular Placement Tags' ),
            'all_items'                  => __( 'All Placement Tags' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Placement Tag' ),
            'update_item'                => __( 'Update Placement Tag' ),
            'add_new_item'               => __( 'Add New Placement Tag' ),
            'new_item_name'              => __( 'New Origin Tag Name' ),
            'add_or_remove_items'        => __( 'Add or remove placement tags' ),
            'separate_items_with_commas' => __( 'Separate placement tags with commas' ),
            'choose_from_most_used'      => __( 'Choose from the most used placement tags' ),
            'not_found'                  => __( 'No placement tags found.' ),
            'menu_name'                  => __( 'Placement Tags' ),
        ),
    ));

    // Add new "Location Tag" taxonomy to Articles
    register_taxonomy('location_tag', 'article', array(
        'hierarchical'          => false,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'location-tags' ),
        // This array of options controls the labels displayed in the WordPress Admin UI
        'labels' => array(
            'name'                       => _x( 'Location Tags', 'taxonomy general name' ),
            'singular_name'              => _x( 'Location Tag', 'taxonomy singular name' ),
            'search_items'               => __( 'Search Location Tags' ),
            'popular_items'              => __( 'Popular Location Tags' ),
            'all_items'                  => __( 'All Location Tags' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Location Tag' ),
            'update_item'                => __( 'Update Location Tag' ),
            'add_new_item'               => __( 'Add New Location Tag' ),
            'new_item_name'              => __( 'New Location Tag Name' ),
            'add_or_remove_items'        => __( 'Add or remove location tags' ),
            'separate_items_with_commas' => __( 'Separate location tags with commas' ),
            'choose_from_most_used'      => __( 'Choose from the most used location tags' ),
            'not_found'                  => __( 'No location tags found.' ),
            'menu_name'                  => __( 'Location Tags' ),
        ),
    ));

    // Add new "Lifestyle Tag" taxonomy to Articles
    register_taxonomy('lifestyle_tag', 'article', array(
        'hierarchical'          => false,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'lifestyle-tag' ),
        // This array of options controls the labels displayed in the WordPress Admin UI
        'labels' => array(
            'name'                       => _x( 'Lifestyle Tags', 'taxonomy general name' ),
            'singular_name'              => _x( 'Lifestyle Tag', 'taxonomy singular name' ),
            'search_items'               => __( 'Search Lifestyle Tags' ),
            'popular_items'              => __( 'Popular Lifestyle Tags' ),
            'all_items'                  => __( 'All Lifestyle Tags' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Lifestyle Tag' ),
            'update_item'                => __( 'Update Lifestyle Tag' ),
            'add_new_item'               => __( 'Add New Lifestyle Tag' ),
            'new_item_name'              => __( 'New Lifestyle Name' ),
            'add_or_remove_items'        => __( 'Add or remove lifestyle tags' ),
            'separate_items_with_commas' => __( 'Separate lifestyle tags with commas' ),
            'choose_from_most_used'      => __( 'Choose from the most used lifestyle tags' ),
            'not_found'                  => __( 'No lifestyle tags found.' ),
            'menu_name'                  => __( 'Lifestyle Tags' ),
        ),
    ));

    // Add new "Featured Tag" taxonomy to Articles
    register_taxonomy('featured_tag', 'article', array(
        'hierarchical'          => false,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'featured-tag' ),
        // This array of options controls the labels displayed in the WordPress Admin UI
        'labels' => array(
            'name'                       => _x( 'Featured Tags', 'taxonomy general name' ),
            'singular_name'              => _x( 'Featured Tag', 'taxonomy singular name' ),
            'search_items'               => __( 'Search Featured Tags' ),
            'popular_items'              => __( 'Popular Featured Tags' ),
            'all_items'                  => __( 'All Featured Tags' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Featured Tag' ),
            'update_item'                => __( 'Update Featured Tag' ),
            'add_new_item'               => __( 'Add New Featured Tag' ),
            'new_item_name'              => __( 'New Featured Tag Name' ),
            'add_or_remove_items'        => __( 'Add or remove featured tags' ),
            'separate_items_with_commas' => __( 'Separate featured tags with commas' ),
            'choose_from_most_used'      => __( 'Choose from the most used featured tags' ),
            'not_found'                  => __( 'No locations found.' ),
            'menu_name'                  => __( 'Featured Tags' ),
        ),
    ));
    // Add new "Origin Tag" taxonomy to Articles
    register_taxonomy('origin_tag', 'article', array(
        'hierarchical'          => false,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'origin-tags' ),
        // This array of options controls the labels displayed in the WordPress Admin UI
        'labels' => array(
            'name'                       => _x( 'Origin Tags', 'taxonomy general name' ),
            'singular_name'              => _x( 'Origin Tag', 'taxonomy singular name' ),
            'search_items'               => __( 'Search Origin Tags' ),
            'popular_items'              => __( 'Popular Origin Tags' ),
            'all_items'                  => __( 'All Origin Tags' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Origin Tag' ),
            'update_item'                => __( 'Update Origin Tag' ),
            'add_new_item'               => __( 'Add New Origin Tag' ),
            'new_item_name'              => __( 'New Origin Tag Name' ),
            'add_or_remove_items'        => __( 'Add or remove origin tags' ),
            'separate_items_with_commas' => __( 'Separate origin tags with commas' ),
            'choose_from_most_used'      => __( 'Choose from the most used origin tags' ),
            'not_found'                  => __( 'No origin tags found.' ),
            'menu_name'                  => __( 'Origin Tags' ),
        ),
    ));
}
add_action( 'init', 'ms_add_custom_taxonomies', 0 );