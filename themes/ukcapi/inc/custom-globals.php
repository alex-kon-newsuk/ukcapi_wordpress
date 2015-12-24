<?php
/**
 * Created by PhpStorm.
 * User: cleavesp
 * Date: 3/27/15
 * Time: 1:46 PM
 */


global $api_access_keys;
$api_access_keys = array(   'salesdemo'     => 'Token token="841abb572fcada7ac8182e686da5feed"',
                            'usertest'      => 'Token token="06e3950b2de3fe29d43af3aa971f1764"',
                            'production'    => 'Token token="6beed63368ebbe7d273e25b1fb603151"',
                            'production-2'  => 'Token token="6beed63368ebbe7d273e25b1fb603151"') ;

http://pub.production-web.mansion-global.mansion.virginia.onservo.com

global $api_paths;
$api_paths = array( 'salesdemo'  => array( 'articles'               => 'http://pub.salesdemo-web.mansion-global.mansion.virginia.onservo.com/api/media_articles/',
                                           'query_articles'         => 'http://pub.salesdemo-web.mansion-global.mansion.virginia.onservo.com/api/articles/',
                                           'curatedlinks'           => 'http://pub.salesdemo-web.mansion-global.mansion.virginia.onservo.com/api/curated_links/',
                                           'query_curatedlinks'     => 'http://pub.salesdemo-web.mansion-global.mansion.virginia.onservo.com/api/curated_links/'),

                    'usertest'   => array( 'articles'               => 'http://pub.usertest-web.mansion-global.mansion.virginia.onservo.com/api/media_articles/',
                                           'query_articles'         => 'http://pub.usertest-web.mansion-global.mansion.virginia.onservo.com/api/articles/',
                                           'curatedlinks'           => 'http://pub.usertest-web.mansion-global.mansion.virginia.onservo.com/api/curated_links/',
                                           'query_curatedlinks'     => 'http://pub.usertest-web.mansion-global.mansion.virginia.onservo.com/api/curated_links/'),

                    'production' => array( 'articles'               => 'http://pub.production-web.mansion-global.mansion.virginia.onservo.com/api/media_articles/',
                                           'query_articles'         => 'http://pub.production-web.mansion-global.mansion.virginia.onservo.com/api/articles/',
                                           'curatedlinks'           => 'http://pub.production-web.mansion-global.mansion.virginia.onservo.com/api/curated_links/',
                                           'query_curatedlinks'     => 'http://pub.production-web.mansion-global.mansion.virginia.onservo.com/api/curated_links/'),


                    'production-2' => array('articles'               => 'http://pub.production-web2.mansion-global.mansion.virginia.onservo.com/api/media_articles/',
                                            'query_articles'         => 'http://pub.production-web2.mansion-global.mansion.virginia.onservo.com/api/articles/',
                                            'curatedlinks'           => 'http://pub.production-web2.mansion-global.mansion.virginia.onservo.com/api/curated_links/',
                                            'query_curatedlinks'     => 'http://pub.production-web2.mansion-global.mansion.virginia.onservo.com/api/curated_links/') ) ;

global $web_page_unit_names;
$web_page_unit_names = array(
                                'HOME_TOP_MARKETS'              => array('friendlyname' => 'Home Page<br/><br/>Top Markets (7 x Cards)',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'false',
                                                                         'icon'         =>  get_template_directory_uri() . '/img/home_top_markets.jpg',
                                                                         'contenttype'  => 'curatedlink'),

                                'HOME_EDITORIAL_HEADLINES'      => array('friendlyname' => 'Home Page:<br/><br/>Headlines (5 x Stories)',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'false',
                                                                         'icon'         =>  get_template_directory_uri() . '/img/home_editorial_articles.jpg',
                                                                         'contenttype'  => 'articles'),

                                'TOP_MARKETS_EDITORIAL_HEADLINES' => array('friendlyname'  => 'Top Markets:<br/><br/>Headlines (5 x Stories)',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'true',
                                                                         'icon'         => '',
                                                                         'contenttype'  => 'articles'),

                                'TOP_MARKETS_NEW_YORK_EDITORIAL_HEADLINES' => array('friendlyname' => 'Top Markets New York:<br/><br/>Headlines (5 x Stories)',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'false',
                                                                         'icon'         => '',
                                                                         'contenttype'  => 'articles'),

                                'TOP_MARKETS_MIAMI_EDITORIAL_HEADLINES' => array('friendlyname' => 'Top Markets Miami:<br/><br/>Headlines (5 x Stories)',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'false',
                                                                         'icon'         => '',
                                                                         'contenttype'  => 'articles'),

                                'TOP_MARKETS_LONDON_EDITORIAL_HEADLINES' => array('friendlyname' => 'Top Markets London:<br/><br/>Headlines (5 x Stories)',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'false',
                                                                         'icon'         => '',
                                                                         'contenttype'  => 'articles'),

                                'TOP_MARKETS_SAN_FRANCISCO_EDITORIAL_HEADLINES' => array('friendlyname' => 'Top Markets San Francisco:<br/><br/>Headlines (5 x Stories)',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'false',
                                                                         'icon'         => '',
                                                                         'contenttype'  => 'articles'),

                                'TOP_MARKETS_SYDNEY_EDITORIAL_HEADLINES' => array('friendlyname' => 'Top Markets Sydney:<br/><br/>Headlines (5 x Stories)',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'false',
                                                                         'icon'         => '',
                                                                         'contenttype'  => 'articles'),

                                'HOME_HEADLINES'                => array('friendlyname' => 'Home Page Articles',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'true',
                                                                         'icon'         =>  get_template_directory_uri() . '/img/home_top_markets.jpg',
                                                                         'contenttype'  => 'articles'),

                                'HOME_TOP_MARKETS_HEADLINES'    => array('friendlyname' => 'Top Market Headlines',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'true',
                                                                         'icon'         =>  get_template_directory_uri() . '/img/home_top_markets.jpg',
                                                                         'contenttype'  => 'articles'),

                                'ARTICLE_CTA_TOP_MARKETS'       => array('friendlyname' => 'Unknown',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'true',
                                                                         'icon'         =>  get_template_directory_uri() . '/home_top_markets.jpg',
                                                                         'contenttype'  => 'articles'),


                                'NEWS_MOST_POPULAR'             => array('friendlyname' => 'News Most Popular',
                                                                         'layout'       => 'tbc',
                                                                         'hide'         => 'false',
                                                                         'icon'         =>  get_template_directory_uri() . '/home_top_markets.jpg',
                                                                         'contenttype'  => 'articles'),


                                'NEWS_VIDEOS'                   => array('friendlyname' => 'News Videos',
                                                                        'layout'       => 'tbc',
                                                                        'hide'         => 'false',
                                                                        'icon'         =>  get_template_directory_uri() . '/home_top_markets.jpg',
                                                                        'contenttype'  => 'articles')

                                                                    ) ;

global  $mansion_environments;
        $mansion_environments = array(  'production-2'  => array('name'     => 'Production-2',
                                                                'type'      => 'Servo',
                                                                'id'        => 'production-2',
                                                                'homepage'  => 'http://pub.production-web2.mansion-global.mansion.virginia.onservo.com',
                                                                'default'   => 'false'),
                                        'production'    => array('name'     => 'Production',
                                                                'type'      => 'Servo',
                                                                'id'        => 'production',
                                                                'homepage'  => 'http://pub.production-web.mansion-global.mansion.virginia.onservo.com',
                                                                'default'   => 'true'),
                                        'usertest'      => array('name'     => 'User Test',
                                                                'type'      => 'Servo',
                                                                'id'        => 'usertest',
                                                                'homepage'  => 'http://pub.usertest-web.mansion-global.mansion.virginia.onservo.com',
                                                                'default'   => 'false'),
                                        'salesdemo'     => array('name'     => 'Sales Demo',
                                                                'type'      => 'Servo',
                                                                'id'        => 'salesdemo',
                                                                'homepage'  => 'http://pub.salesdemo-web.mansion-global.mansion.virginia.onservo.com',
                                                                'default'   => 'false'));

?>