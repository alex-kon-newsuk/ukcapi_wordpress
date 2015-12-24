<?php
defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

require_once 'vendor/autoload.php';
require_once('content-hub-client-dao.php');

use Hub\Publisher;
use Hub\Subscriber;

if (!class_exists('HubService')) {

    class HubService
    {
        private $hubDao;

        function __construct()
        {
            $this->hubDao = new HubDao();
        }

        function install()
        {
            $this->hubDao->create_tables();
            $this->hubDao->create_mandatory_properties();
        }

        function uninstall()
        {
            $this->hubDao->drop_tables();
        }

        function hub_subscribe($topicName)
        {
            try {
                $subscriber = new Subscriber(
                    $this->get_property("signing.key.id"),
                    $this->get_property("signing.key.value"),
                    $this->get_property("hub.url")
                );

                $resultMessage = $subscriber->subscribe($topicName, $this->get_callback_url());

                echo $resultMessage;

            } catch (Exception $e) {
                echo "Problem subscribing to the Hub." . "<br>" .
                    " Hub URL: " . $this->get_property("hub.url") . "<br>" .
                    " Topic Name: " . $topicName . "<br>" .
                    " Callback URL: " . $this->get_callback_url() . "<br>" .
                    " Error: " . $e->getMessage();
            }
            die();
        }

        function hub_publish_silent($topicName, $content)
        {
            try
            {
                $publisher = new Publisher(
                    $this->get_property("signing.key.id"),
                    $this->get_property("signing.key.value"),
                    $this->get_property("hub.url")
                );
                $resultMessage = $publisher->publish($topicName, 'application/atom+xml', stripslashes($content));
                return (is_null($resultMessage) && empty($resultMessage));
            }
            catch (Exception $e)
            {

            }
            return false;
        }

        function hub_publish($topicName, $content)
        {
            try {
                $publisher = new Publisher(
                    $this->get_property("signing.key.id"),
                    $this->get_property("signing.key.value"),
                    $this->get_property("hub.url")
                );

                $resultMessage = $publisher->publish($topicName, 'application/atom+xml', stripslashes($content));

                if (!is_null($resultMessage) && !empty($resultMessage)) {
                    echo $resultMessage;
                } else {
                    echo "Message published successfully";
                }

            } catch (Exception $e) {
                echo "Problem publishing to the Hub." . "<br>" .
                    " Hub URL: " . $this->get_property("hub.url") . "<br>" .
                    " Topic Name: " . $topicName . "<br>" .
                    " Content: " . $content . "<br>" .
                    " Error: " . $e->getMessage();
            }
            die();
        }

        function hub_change_delivery_policy($topicName, $maxReceiveRatePerSecond, $numRetries, $secondsBetweenRetries)
        {
            try {
                $subscriber = new Subscriber(
                    $this->get_property("signing.key.id"),
                    $this->get_property("signing.key.value"),
                    $this->get_property("hub.url")
                );

                $resultMessage = $subscriber->changeDeliveryPolicy(
                    $topicName,
                    $this->get_callback_url(),
                    $maxReceiveRatePerSecond,
                    $numRetries,
                    $secondsBetweenRetries
                );

                echo $resultMessage;

            } catch (Exception $e) {
                echo "Problem changing delivery policy." . "<br>" .
                    " Hub URL: " . $this->get_property("hub.url") . "<br>" .
                    " Topic Name: " . $topicName . "<br>" .
                    " maxReceiveRatePerSecond: " . $maxReceiveRatePerSecond . "<br>" .
                    " numRetries: " . $numRetries . "<br>" .
                    " secondsBetweenRetries: " . $secondsBetweenRetries . "<br>" .
                    " Error: " . $e->getMessage();
            }
            die();
        }

        function update_property($propertyName, $propertyValue)
        {
            $this->hubDao->update_property($propertyName, $propertyValue);
        }

        function get_property($propertyName)
        {
            return $this->hubDao->get_property($propertyName);
        }

        function save_subscription($topicArn)
        {
            $this->hubDao->save_subscription($topicArn);
        }

        function remove_subscription($topicArn)
        {
            $this->hubDao->remove_subscription($topicArn);
        }

        function get_subscriptions()
        {
            return $this->hubDao->get_subscriptions();
        }

        function save_notification_and_post($snsMessageId, $topicArn, $originTopicName,
                                            $contentType, $content, $messageTimestamp, $unsubscribeUrl)
        {
            $notificationId = null;
            if ($this->get_property("store.notifications") == "true") {
                $notificationId = $this->hubDao->save_notification($snsMessageId, $topicArn, $originTopicName,
                    $contentType, $content, $messageTimestamp, $unsubscribeUrl);
            }

            do_action('hub_notification_arrived', $notificationId, $content, $contentType, $originTopicName,
                $messageTimestamp, $snsMessageId);
        }

        function get_notifications()
        {
            return $this->hubDao->get_notifications(20);
        }

        public function is_signing_key_pending()
        {
            $signingKeyId = $this->hubDao->get_property("signing.key.id");
            $signingKeyValue = $this->hubDao->get_property("signing.key.value");
            return is_null($signingKeyId) || is_null($signingKeyValue);
        }

        function get_callback_url()
        {
            return $this->hubDao->get_property("hub.callback.url");
        }
    }

}