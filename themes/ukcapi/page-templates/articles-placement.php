<?php
/**
 * Template Name: Articles Placement
 *
 * @package Mansion
 */

if (!is_user_logged_in())
{
    auth_redirect();
}

get_header(); ?>

    <div id="primary" class="placement-editor wrap">
        <span><button id="env-options-btn">Settings</button><span style="margin-left:20px;">Selected Environment <b><span id="selected-env-name"></span></b></span></span>
        <div id="env-options" class="grid">
            <div class="grid__col grid__col--12-of-12 grid__col--centered">
                <div class="grid grid--no-gutter env-ctrl-box">
                    <div class="grid__col grid__col--4-of-4">
                        <div style="text-align: center;display:none;">
                            <span class="sub-title">Select Language</span>
                            <input type="radio" class="locale-radio-buttons" name="locale-buttons" checked data="en"><b>English</b></input>
                            <input type="radio" class="locale-radio-buttons" name="locale-buttons" data="zh"><b>Chinese</b></input>
                            <input type="radio" class="locale-radio-buttons" name="locale-buttons" data="es"><b>Spanish</b></input>
                        </div>
                        <br/>
                        <div style="text-align: center;">
                            <span class="sub-title">Environments</span>
                            <?php
                                global $mansion_environments;
                                foreach($mansion_environments as $env) { ?>
                                    <span class="env-radio-buttons-container <?php if($env['default'] == "true") echo('env-selected'); ?>">
                                        <input type="radio" class="env-radio-buttons" name="env-radio-buttons" <?php if($env['default'] == "true") echo('checked'); ?>
                                               data-url="<?php echo $env['homepage'] ?>/api"
                                               data="<?php echo $env['id'] ?>"><b><?php echo $env['name'] ?></b>
                                            <a target="new" href="<?php echo $env['homepage'] ?>" > view</a>
                                        </input>
                                    </span>
                                    <?php
                                }
                            ?>
<!--                            <span class="env-radio-buttons-container env-selected">-->
<!--                                <input type="radio" class="env-radio-buttons" name="env-radio-buttons" checked data="edge"><b>Edge</b>-->
<!--                                <a target="new" href="http://mansion:luxuryhomes@mansion-global-edge.substantial.com//" >goto homepage</a>-->
<!--                                </input>-->
<!--                            </span>-->
<!--                            <span class="env-radio-buttons-container">-->
<!--                                <input type="radio" class="env-radio-buttons" name="env-radio-buttons" data="staging"><b>Staging</b>-->
<!--                                <a target="new" href="http://mansion:luxuryhomes@mansion-global.substantial.com//" >goto homepage</a>-->
<!--                                </input>-->
<!--                            </span>-->
<!--                            <span class="env-radio-buttons-container">-->
<!--                                <input type="radio" class="env-radio-buttons" name="env-radio-buttons" data="production"><b>Production</b>-->
<!--                                <a target="new" href="http://mansion:luxuryhomes@pub.staging.mansion-global.mansion.virginia.onservo.com" >goto homepage</a>-->
<!--                                </input>-->
<!--                            </span>-->
<!--                        <div class="onoffswitch">-->
<!--                            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="ms-onoffswitch" checked>-->
<!--                            <label class="onoffswitch-label" for="ms-onoffswitch">-->
<!--                                <span class="onoffswitch-inner"></span>-->
<!--                                <span class="onoffswitch-switch"></span>-->
<!--                            </label>-->
<!--                        </div>-->
                        </div>
                        <br/>
                    </div>
                </div>
            </div>
        </div>
        <main id="main" class="site-main" role="main">
            <div class="main-navigation">
                <ul class="articles-group-tabs">
                    Loading ...
                </ul>
            </div>
            <div id="article-group-1" class="article-group-content grid">
                <div class="grid__col grid__col--4-of-12">
                    <div id="home-articles" class="placement-section">
                        <div class="filter-ctrl">
                            <h4>Content Filter</h4>
                            <form class="search-panel">
                                <div class="toolbox-item clear">
                                    <input type="radio" class="search-radio-buttons" name="search-radio-buttons" data-button-type="articles">Show Articles</input>
                                </div>
                                <div class="toolbox-item clear">
                                    <input type="radio" class="search-radio-buttons" name="search-radio-buttons" data-button-type="curatedlink">Show Curated Links</input>
                                </div>
                                <div class="toolbox-item clear">
                                    <input size="40" type="text" id="search-title" value="" name="search-title" />
                                    <span>
                                        <a class="btn-link" style="width:150px;" href="#">Search</a>
                                    </span>
                                </div>
                            </form>
                            <br/>
                            <h3 id="article-types">...</h3>
                        </div>

                        <div id="toolbar-new">
                            <a class="btn-link" target="_new" href="<?php echo get_admin_url() ?>/post-new.php?post_type=curatedlink">New CuratedLink</a>
                            <a class="btn-link" target="_new" href="<?php echo get_admin_url() ?>/post-new.php?post_type=articles">New Article</a>
                        </div>
                        <?php
                        $args = array(
                            'post_type' => 'articles',
                            'posts_per_page' => -1
                        );
                        $the_query = new WP_Query( $args );

                        if ( $the_query->have_posts() ) : ?>

                            <ul class="article-list available-items">
                                <!-- the loop -->
                                <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                    <li class="article-item clear" data-article-id="<?php echo get_the_ID(); ?>">
                                        <?php if ( has_post_thumbnail() ) : ?>
                                            <div>
                                                <a class="alignleft" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                                    <?php the_post_thumbnail( array(80, 80) ); ?>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <a class="alignleft" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                                <img src="<?php echo get_bloginfo('template_url'); ?>/img/no_img_placehold.gif" width="80" height="80" alt="No image" />
                                            </a>
                                        <?php endif; ?>

                                        <div class="article-body">
                                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                            <p class="entry-content">
                                                <?php
                                                $content = get_the_content();
                                                $trimmed_content = wp_trim_words( $content, 15, '<a class="read-more" href="'. get_permalink() .'">... Read More</a>' );
                                                echo $trimmed_content;
                                                ?>
                                            </p>
                                            <?php if (get_post_meta(get_the_ID(), 'byLine', true)): ?>
                                                <p class="by-line">by: <span><?php echo get_post_meta(get_the_ID(), 'byLine', true); ?></span></p>
                                            <?php endif; ?>

                                            <div style="margin-top:5px;font-size:0.5em;" class="inline-toolbar">
                                            <div><b>Src ID:</b><?php echo get_post_meta(get_the_ID(), 'sourceId', true) ?></div>
                                            <div><b>WP ID:</b><?php echo get_the_ID() ?></div>
                                            <a href="<?php echo get_admin_url(null, '/post.php?post=' . get_the_ID() .'&action=edit'); ?>">Edit in CMS</a>
                                            </div>

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
                                                    <div class="grid__col grid__col--3-of-5">
                                                        <div class="tags-wrap">
                                                            <ul class="tags">

                                                                <?php foreach ($result_terms as $result_term) :
                                                                    ?>
                                                                    <li><?php echo $result_term; ?></li>
                                                                <?php endforeach; ?>

                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="grid__col grid__col--2-of-5">
                                                        <button class="item-tags-ctrl">View All Tags</button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="grid">
                                                <div class="grid__col grid__col--2-of-5 grid__col--push-3-of-5">
                                                    <?php if (get_post_meta(get_the_ID(), 'articlePlacementId', true)): ?>
                                                    <button class="btn-icon add-pl-item placed" data-external-id="<?php echo get_post_meta(get_the_ID(), 'sourceId', true); ?>"
                                                            data-mansion-id="<?php echo get_the_ID(); ?>" disabled>
                                                        Placed
                                                    </button>
                                                    <?php else: ?>
                                                    <button class="btn-icon add-pl-item" data-external-id="<?php echo get_post_meta(get_the_ID(), 'sourceId', true); ?>"
                                                        data-mansion-id="<?php echo get_the_ID(); ?>">
                                                        Add to slot
                                                    </button>
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
                            <p>No articles found</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="grid__col grid__col--8-of-12">
                    <div class="placement-section">
                        <ul class="group-info">
<!--                            <li><span>Group ID:</span> <span id="group-id"></span></li>-->
                            <li id="group-capacity-1" data-available-slots="99"><span>Capacity: </span> N/A</li>
                        </ul>
                        <ul class="article-list placement-items grid">
                            <li class="article-item clear">
                                <div class="loader-wrap">
<!--                                    <span class="ajax-loader"></span>-->
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="article-group-2" class="article-group-content grid">
                <h2>Collection</h2>
                <p style="text-align: center">TBD</p>
            </div>
            <div id="article-group-3" class="article-group-content grid">
                <h2>Markets</h2>
                <p style="text-align: center">TBD</p>
            </div>
            <div id="article-group-4" class="article-group-content grid">
                <h2>Other</h2>
                <p style="text-align: center">TBD</p>
            </div>
        </main>
    </div>

<?php get_footer(); ?>