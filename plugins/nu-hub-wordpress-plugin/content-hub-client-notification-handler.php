<?php

// This file receives HTTP requests from SNS, that's why it's open to the world, but the requests from SNS
// contain a signature and a certificate that are verified by the notificationHandler->readNotification(...), so only
// genuine messages are processed.

require_once("../../../wp-load.php");
require_once 'vendor/autoload.php';
require_once('content-hub-client-service.php');

use Hub\NotificationHandler;

$hubService = new HubService();

$hubUrl = $hubService->get_property("hub.url");
$signingKeyId = $hubService->get_property("signing.key.id");
$signingKeyValue = $hubService->get_property("signing.key.value");
$acceptRawDelivery = $hubService->get_property("accept.raw.delivery") == "true";

$notificationHandler = new NotificationHandler($signingKeyId, $signingKeyValue, $hubUrl);
$data = $notificationHandler->readNotification($acceptRawDelivery, true);
$messageType = $data["Type"];

if ("Notification" == $messageType) {
    $hubService->save_notification_and_post(
        $data["MessageId"],
        $data["TopicArn"],
        $data["MessageAttributes"]["originTopicName"]["Value"],
        $data["MessageAttributes"]["Content-Type"]["Value"],
        $data["Message"],
        $data["Timestamp"],
        $data["UnsubscribeURL"]
    );
    echo "Notification received";

} else if ("SubscriptionConfirmation" == $messageType) {
    $hubService->save_subscription($data["TopicArn"]);
    echo "message content: " . $data["Message"] . " \n";
    echo "subscription confirmed";


} else if ("UnsubscribeConfirmation" == $messageType) {
    $hubService->remove_subscription($data["TopicArn"]);
    echo "message content: " . $data["Message"] . " \n";
    echo "subscription removed";
}