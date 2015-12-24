<?php
defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

if (!class_exists('HubDao')) {

    class HubDao
    {
        function create_tables()
        {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $tableHubConfig = $wpdb->prefix . "content_hub_config";
            $tableHubSubscription = $wpdb->prefix . "content_hub_subscription";
            $tableHubNotification = $wpdb->prefix . "content_hub_notification";

            $charsetCollate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE IF NOT EXISTS $tableHubConfig (
                      property VARCHAR(60) NOT NULL,
                      value VARCHAR(200) NOT NULL,
                      PRIMARY KEY (property)
                    ) $charsetCollate";
            dbDelta($sql);

            $sql = "CREATE TABLE IF NOT EXISTS $tableHubSubscription (
                      topic_arn VARCHAR(100) NOT NULL,
                      creation_date DATETIME NOT NULL,
                      PRIMARY KEY (topic_arn)
                    ) $charsetCollate";
            dbDelta($sql);

            $sql = "CREATE TABLE IF NOT EXISTS $tableHubNotification (
                      id_notification INT NOT NULL AUTO_INCREMENT,
                      sns_message_id VARCHAR(60) NOT NULL,
                      topic_arn VARCHAR(100) NOT NULL,
                      origin_topic_name VARCHAR(300),
                      content_type VARCHAR(30),
                      content text NOT NULL,
                      message_timestamp DATETIME,
                      received_date DATETIME NOT NULL,
                      unsubscribe_url VARCHAR(300),
                      PRIMARY KEY (id_notification)
                    ) $charsetCollate";
            dbDelta($sql);
        }

        function drop_tables()
        {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $tableHubConfig = $wpdb->prefix . "content_hub_config";
            $tableHubSubscription = $wpdb->prefix . "content_hub_subscription";
            $tableHubNotification = $wpdb->prefix . "content_hub_notification";

            $wpdb->query("DROP TABLE $tableHubConfig");
            $wpdb->query("DROP TABLE $tableHubSubscription");
            $wpdb->query("DROP TABLE $tableHubNotification");
        }

        function create_mandatory_properties()
        {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $tableHubConfig = $wpdb->prefix . "content_hub_config";

            if (is_null($this->get_property('hub.url'))) {
                $wpdb->insert($tableHubConfig, array(
                    "property" => "hub.url",
                    "value" => "http://enter-hub-url"
                ));
            }

            if (is_null($this->get_property('signing.key.id'))) {
                $wpdb->insert($tableHubConfig, array(
                    "property" => "signing.key.id",
                    "value" => ""
                ));
            }

            if (is_null($this->get_property('signing.key.value'))) {
                $wpdb->insert($tableHubConfig, array(
                    "property" => "signing.key.value",
                    "value" => ""
                ));
            }

            if (is_null($this->get_property('store.notifications'))) {
                $wpdb->insert($tableHubConfig, array(
                    "property" => "store.notifications",
                    "value" => "true"
                ));
            }

            if (is_null($this->get_property('accept.raw.delivery'))) {
                $wpdb->insert($tableHubConfig, array(
                    "property" => "accept.raw.delivery",
                    "value" => "false"
                ));
            }

            if (is_null($this->get_property('hub.callback.url'))) {
                $wpdb->insert($tableHubConfig, array(
                    "property" => "hub.callback.url",
                    "value" => "http://<wordpress-url>/wp-content/plugins/content-hub-client/content-hub-client-notification-handler.php"
                ));
            }
        }

        function update_property($propertyName, $propertyValue)
        {
            global $wpdb;
            $tableName = $wpdb->prefix . "content_hub_config";

            $dataUpdate = array('value' => $propertyValue);
            $dataWhere = array('property' => $propertyName);

            $wpdb->update($tableName, $dataUpdate, $dataWhere);
        }

        function get_property($propertyName)
        {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $tableName = $wpdb->prefix . "content_hub_config";

            return $wpdb->get_var($wpdb->prepare("SELECT value FROM $tableName where property = %s", $propertyName));
        }

        function save_subscription($topicArn)
        {
            global $wpdb;
            $tableName = $wpdb->prefix . "content_hub_subscription";

            $exitingTopicArn = $wpdb->get_var($wpdb->prepare("SELECT topic_arn FROM $tableName where topic_arn = %s", $topicArn));

            if (is_null($exitingTopicArn)) {
                $wpdb->insert($tableName, array(
                    "topic_arn" => $topicArn,
                    "creation_date" => current_time('mysql', 1)
                ));
            }
        }

        function remove_subscription($topicArn)
        {
            global $wpdb;
            $tableName = $wpdb->prefix . "content_hub_subscription";

            $wpdb->delete($tableName, array(
                "topic_arn" => $topicArn
            ));
        }

        function get_subscriptions()
        {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $tableName = $wpdb->prefix . "content_hub_subscription";

            return $wpdb->get_results("
                SELECT *
                FROM  $tableName
                ORDER BY creation_date DESC
            ");
        }

        function save_notification($snsMessageId, $topicArn, $originTopicName,
                                   $contentType, $content, $messageTimestamp, $unsubscribeUrl)
        {
            global $wpdb;
            $tableName = $wpdb->prefix . "content_hub_notification";

            $wpdb->insert($tableName, array(
                "sns_message_id" => $snsMessageId,
                "topic_arn" => $topicArn,
                "origin_topic_name" => $originTopicName,
                "content_type" => $contentType,
                "content" => $content,
                "message_timestamp" => $messageTimestamp,
                "received_date" => current_time('mysql', 1),
                "unsubscribe_url" => $unsubscribeUrl
            ));

            $notificationId = $wpdb->insert_id;

            return $notificationId;
        }

        function get_notifications($limit)
        {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $tableName = $wpdb->prefix . "content_hub_notification";

            return $wpdb->get_results("
                SELECT *
                FROM  $tableName
                ORDER BY id_notification DESC
                LIMIT $limit
            ");
        }
    }

}