<?php
/**
 * Mansion Theme article meta information.
 *
 * @author Radimir Bitsov
 *
 * @package Mansion
 */

/**
 * Display image caption for the selected article.
 *
 * @global $post WordPress post object
 *
 * @return void
 */
function ms_the_post_thumbnail_caption() {
    global $post;

    $thumbnail_id = get_post_thumbnail_id($post->ID);
    $metaProperties = get_post_meta($post->ID);
    $args = array(
        'post_type' => 'attachment',
        'post_status' => null,
        'post_parent' => $post->ID,
        'include'  => $thumbnail_id
    );

    $thumbnail_image = get_posts($args);

    if ($thumbnail_image && isset($thumbnail_image[0]))
    {
        echo '<figcaption>'.$thumbnail_image[0]->post_excerpt.'</figcaption>';
        echo '<p><figcaption>image: <b>'.$metaProperties['imageCredit'][0].'</b></figcaption></p>';
    }
}

/**
 * Get all tags by selected category.
 * Important! Prefix of the db tables should be changed appropriately. e.g. ms_post
 *
 * @param string $category_name
 * @param string $taxonomy_term
 * @global $wpdb
 *
 * @return array $tags
 */
function ms_get_category_tags($category_name, $taxonomy_term) {
    global $wpdb;
    $tbl_prefix = $wpdb->prefix;

    $tags = $wpdb->get_results("
		SELECT DISTINCT terms2.term_id as tag_id, terms2.name as tag_name, null as tag_link
		FROM
			". $tbl_prefix ."posts as p1
			LEFT JOIN ". $tbl_prefix ."term_relationships as r1 ON p1.ID = r1.object_ID
			LEFT JOIN ". $tbl_prefix ."term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
			LEFT JOIN ". $tbl_prefix ."terms as terms1 ON t1.term_id = terms1.term_id,

			". $tbl_prefix ."posts as p2
			LEFT JOIN ". $tbl_prefix ."term_relationships as r2 ON p2.ID = r2.object_ID
			LEFT JOIN ". $tbl_prefix ."term_taxonomy as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
			LEFT JOIN ". $tbl_prefix ."terms as terms2 ON t2.term_id = terms2.term_id
		WHERE
			t1.taxonomy = 'category' AND p1.post_status = 'publish' AND terms1.name = '" . $category_name . "' AND
			t2.taxonomy ='" . $taxonomy_term . "' AND p2.post_status = 'publish'
			AND p1.ID = p2.ID
		ORDER by tag_name
	");
    $count = 0;
    foreach ($tags as $tag) {
        $tags[$count]->tag_link = get_tag_link($tag->tag_id);
        $count++;
    }
    return $tags;
}

/**
 * Query articles by selected tag.
 *
 * @param string $taxonomy
 * @global WP_Query $wp_query WordPress Query object.
 *
 * @return void
 */
function ms_ajax_filter_get_articles( $taxonomy ) {

    // Verify nonce
    if( !isset( $_POST['ms_afa_nonce'] ) || !wp_verify_nonce( $_POST['ms_afa_nonce'], 'ms_afa_nonce' ) )
        die('Permission denied');

    $taxonomy = $_POST['taxonomy'];
    $category = $_POST['category'];
    $taxonomy_term = $_POST['taxonomy_term'];

    // WP Query
    $args = array(
        $taxonomy_term => $taxonomy,
        'post_type' => 'articles',
        'category_name'  => $category,
        'posts_per_page' => -1,
    );

    // If taxonomy is not set, remove key from array and get all posts
    if( !$taxonomy ) {
        unset( $args['tag'] );
    }

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) : ?>
        <ul class="article-list">
            <!-- the loop -->
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <li class="article-item clear" data-article-id="<?php echo get_the_ID(); ?>">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a class="alignleft" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            <?php the_post_thumbnail( array(100, 100) ); ?>
                        </a>
                    <?php elseif (get_post_meta(get_the_ID(), 'isGallery', true)): ?>
                        <a class="alignleft" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            <img src="<?php echo get_bloginfo('template_url'); ?>/img/gallery_img_placehold.gif" width="100" height="100" alt="Gallery" />
                        </a>
                    <?php else: ?>
                        <a class="alignleft" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            <img src="<?php echo get_bloginfo('template_url'); ?>/img/no_img_placehold.gif" width="100" height="100" alt="No image" />
                        </a>
                    <?php endif; ?>
                    <div class="article-body">
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="entry-content">
                            <?php
                            $content = get_the_content();
                            $trimmed_content = wp_trim_words( $content, 18, '<a class="read-more" href="'. get_permalink() .'">... Read More</a>' );
                            echo $trimmed_content;
                            ?>
                        </p>
                        <?php if (get_post_meta(get_the_ID(), 'byLine', true)): ?>
                            <p class="by-line">by: <span><?php echo get_post_meta(get_the_ID(), 'byLine', true); ?></span></p>
                        <?php endif; ?>
                        <?php
                        $taxonomies = array(
                            'location_tag',
                            'lifestyle_tag',
                            'featured_tag'
                        );
                        $result_terms = array();

                        foreach ( $taxonomies as $taxonomy ) {
                            $terms = get_the_terms(get_the_ID(), $taxonomy );
                            if ( $terms && ! is_wp_error( $terms ) ) {
                                foreach ( $terms as $term ) {
                                    $result_terms[] = $term->name;
                                }
                            }
                        }
                        if ( !empty($result_terms) ) : ?>
                            <div class="grid">
                                <div class="grid__col grid__col--4-of-5">
                                    <div class="tags-wrap">
                                        <ul class="tags">

                                            <?php foreach ($result_terms as $result_term) :
                                                ?>
                                                <li><?php echo $result_term; ?></li>
                                            <?php endforeach; ?>

                                        </ul>
                                    </div>
                                </div>
                                <div class="grid__col grid__col--1-of-5">
                                    <button class="item-tags-ctrl">View All Tags</button>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="grid">
                            <div class="grid__col grid__col--1-of-5">
                                <p class="article-type">Type: <span>Article</span></p>
                                <p class="source-flag">
                                    <?php if (get_post_meta(get_the_ID(), 'sourcePub', true)): ?>
                                        <a href="<?php echo get_post_meta(get_the_ID(), 'originUrl', true); ?>">
                                            <?php
                                            $sourcePub = get_post_meta(get_the_ID(), 'sourcePub', true);

                                            if ($sourcePub === 'WSJ') {
                                                echo "<img src='" . get_bloginfo( 'template_directory' ) . '/img/wsj_logo.png' . "' alt='Source Logo' />";
                                            } elseif($sourcePub === 'NEWSAUS') {
                                                echo "<img src='" . get_bloginfo( 'template_directory' ) . '/img/newsaus_logo.png' . "' alt='Source Logo' />";
                                            } elseif($sourcePub === 'NEWSUK') {
                                                echo "<img src='" . get_bloginfo( 'template_directory' ) . '/img/newsuk_logo.png' . "' alt='Source Logo' />";
                                            } elseif($sourcePub === 'NYPOST') {
                                                echo "<img src='" . get_bloginfo( 'template_directory' ) . '/img/nypost_logo.png' . "' alt='Source Logo' />";
                                            }
                                            ?>
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="grid__col grid__col--2-of-5">
                                <?php if (has_category('published-live')): ?>
                                    <button data-env="live" style="color: rgb(255, 255, 255); border-color: rgb(49, 125, 199); background-color: rgb(49, 125, 199);">Re-publish to live</button>
                                <?php else: ?>
                                    <button data-env="live">Publish Live</button>
                                <?php endif; ?>
                                <?php if (has_category('published-staging')): ?>
                                    <button data-env="staging" style="color: rgb(255, 255, 255); border-color: rgb(49, 125, 199); background-color: rgb(49, 125, 199);">Re-publish to staging</button>
                                <?php else: ?>
                                    <button data-env="staging">Publish Staging</button>
                                <?php endif; ?>
                            </div>
                            <div class="grid__col grid__col--2-of-5 article-update">
                                <?php if (get_post_meta(get_the_ID(), 'LastPublishedLive', true)): ?>
                                    <p>Last on: <span><?php echo get_post_meta(get_the_ID(), 'LastPublishedLive', true); ?></span></p>
                                <?php else: ?>
                                    <p>Last on: <span>Not Published</span></p>
                                <?php endif; ?>
                                <?php if (get_post_meta(get_the_ID(), 'LastPublishedStaging', true)): ?>
                                    <p>Last on: <span><?php echo get_post_meta(get_the_ID(), 'LastPublishedStaging', true); ?></span></p>
                                <?php else: ?>
                                    <p>Last on: <span>Not Published</span></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endwhile; ?>
            <!-- end of the loop -->
            <?php wp_reset_postdata(); ?>
        </ul>
    <?php else: ?>
        <p>No posts found</p>
    <?php endif;

    die();
}

add_action('wp_ajax_filter_articles', 'ms_ajax_filter_get_articles');
add_action('wp_ajax_nopriv_filter_articles', 'ms_ajax_filter_get_articles');