<?php
/**
 * @package ATOM to CAPI Mapper
 * @author Paul Cleaves
 * @version 1.0.0
 */
/*
Plugin Name: ATOM to CAPI Mapper
Author:      Paul Cleaves
Description: Permits editing in WordPress of Node based microservice module that transforms NewsUK ATOM to AU CAPI JSON
Version:     1.0.0
*/
defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

if (!class_exists('ATOMtoCAPIPlugin')) {

    class ATOMtoCAPIPlugin
    {
        private $hubService;

        function __construct()
        {
            // $this->hubService = new HubService();

            add_action('admin_menu', array($this, 'mapper_menu'));

            //add_action('wp_head', array($this, 'hub_head'));
            //add_action('admin_head', array($this, 'hub_head'));

            //add_action('wp_ajax_save_config', array($this, 'save_config'));
            //add_action('wp_ajax_hub_subscribe', array($this, 'hub_subscribe'));
            //add_action('wp_ajax_hub_publish', array($this, 'hub_publish'));
            //add_action('wp_ajax_hub_change_delivery_policy', array($this, 'hub_change_delivery_policy'));

            //add_action('transition_post_status', array($this, 'postToHubOnPublish'), 10, 3) ;
            //add_action( 'transition_post_status', 'post_unpublished', 10, 3 );

            register_activation_hook(__FILE__, array($this, 'install'));
            register_deactivation_hook(__FILE__, array($this, 'uninstall'));
        }

        function mapper_menu()
        {
            add_menu_page('Content Mapper', 'Content Mapper', 'administrator', 'mapper', array($this, 'mapper_main'));
            //add_menu_page('AUS CAPI', 'AUS CAPI', 'administrator', 'au-capi', array($this, 'hub_main'));
        }

        function postToHubOnPublish($new_status, $old_status, $post)
        {
            if($new_status != 'publish')
                return;

            $title_long = get_post_meta($post->ID, 'titleLongForm', true);
            $title_short = get_post_meta($post->ID, 'titleShortForm', true);
            $title_tiny = get_post_meta($post->ID, 'titleTinyForm', true);
            $subtitle = get_post_meta($post->ID, 'subtitle', true);
            $byLine = get_post_meta($post->ID, 'byLine', true);

            $uuid = get_post_meta($post->ID, 'hub_uuid', true);
            if(empty($uuid))
            {
                $uuid = sprintf("00000000-0000-0000-0000-%012d", $post->ID);
                add_post_meta($post->ID,'hub_uuid',$uuid, true);
            }

            $xmlATOM = '<?xml version="1.0" encoding="utf-8"?>
                            <entry xmlns="http://www.w3.org/2005/Atom" xmlns:age="http://purl.org/atompub/age/1.0" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:cpi="http://xmlns.new.co.uk/types" xmlns:web="http://thetimes.co.uk">
                              <id>https://s3-eu-west-1.amazonaws.com/thesun-atomdocs-uat/97c64f20-cb67-11e4-a202-50ac5def393a</id>
                              <identifier>' . $uuid . '</identifier>
                              <title>' . $post->post_title . '</title>
                              <cpi:shorttitle>' . $title_short . '</cpi:shorttitle>
                              <cpi:strapline>' . $title_tiny . '</cpi:strapline>
                              <cpi:subtitle>' . $subtitle . '</cpi:subtitle>
                              <updated/>
                              <published>2015-03-23T23:06:34Z</published>
                              <cpi:editiondate>2015-03-23</cpi:editiondate>
                              <cpi:publication>TheSun</cpi:publication>
                              <cpi:times_templateid>default</cpi:times_templateid>
                              <cpi:leadassetid>31bdb7c7-1339-442b-8483-319a70f5ee7d</cpi:leadassetid>
                              <version>1</version>
                              <region/>
                              <category term="article"/>
                              <author>
                                <name>' . $byLine . '</name>
                              </author>
                              <content type="xhtml">
                                <div xmlns="http://www.w3.org/1999/xhtml">
                                <p>' . $post->post_content . '</p>
                                </div>
                              </content>
                              <age:expires/>
                              <cpi:status>WebFlow/Published</cpi:status>
                            </entry>';

            // $this->hubService->hub_publish_silent("http://news.co.uk/thesun_capi", $xmlATOM);
        }

        function install()
        {
            // $this->hubService->install();
        }

        function uninstall()
        {
            // $this->hubService->uninstall();
        }

//        /**
//         * Subscribe to a topic.
//         */
//        function hub_subscribe()
//        {
//            $this->hubService->hub_subscribe($_POST["topicName"]);
//        }
//
//        /**
//         * Publish to a topic.
//         */
//        function hub_publish()
//        {
//            $this->hubService->hub_publish($_POST["topicName"], $_POST["content"]);
//        }
//
//        function hub_head()
//        {
//            $url = plugins_url() . '/' . basename(dirname(__FILE__));
//            print '<script language="javascript" src="' . $url . '/js/content-hub-client-form.js" /></script>';
//        }

        /**
         * Display the plugin's main page.
         */
        function mapper_main()
        {
//            $hubUrl = $this->hubService->get_property('hub.url');
//            $subscriptions = $this->hubService->get_subscriptions();
//            $notifications = $this->hubService->get_notifications();
//            $callbackUrl = $this->hubService->get_callback_url();
//            $signingKeyPending = $this->hubService->is_signing_key_pending();
//            $storeNotifications = $this->hubService->get_property("store.notifications") == "true";
//            $acceptRawDelivery = $this->hubService->get_property("accept.raw.delivery") == "true";

            require("atom-to-capi-mapper-form.php");
        }

        /**
         * Change the plugin's settings.
         */
        function save_config()
        {
//            $hubUrl = $_POST["defaultHubUrl"];
//            $callbackUrl = $_POST["callbackUrl"];
//            $signingKeyId = $_POST["signingKeyId"];
//            $signingKeyValue = $_POST["signingKeyValue"];
//            $storeNotifications = $_POST["storeNotifications"];
//            $acceptRawDelivery = $_POST["acceptRawDelivery"];
//
//            $this->hubService->update_property("hub.url", $hubUrl);
//            $this->hubService->update_property("hub.callback.url", $callbackUrl);
//
//            if (!is_null($storeNotifications) && !empty($storeNotifications)) {
//                $this->hubService->update_property("store.notifications", "true");
//            } else {
//                $this->hubService->update_property("store.notifications", "false");
//            }
//
//            if (!is_null($acceptRawDelivery) && !empty($acceptRawDelivery)) {
//                $this->hubService->update_property("accept.raw.delivery", "true");
//            } else {
//                $this->hubService->update_property("accept.raw.delivery", "false");
//            }
//
//            if (!is_null($signingKeyId) && !empty($signingKeyId) && !is_null($signingKeyValue) && !empty($signingKeyValue)) {
//                $this->hubService->update_property("signing.key.id", $signingKeyId);
//                $this->hubService->update_property("signing.key.value", $signingKeyValue);
//                echo "Saved";
//
//            } else {
//                if ($this->hubService->is_signing_key_pending()) {
//                    header('HTTP/1.1 400 BAD REQUEST');
//                    echo "Signing keys are missing.";
//                    die();
//                }
//                echo "Saved (signing keys were ignored)";
//            }
//
//            die();
        }
    }
}

new ATOMtoCAPIPlugin();



