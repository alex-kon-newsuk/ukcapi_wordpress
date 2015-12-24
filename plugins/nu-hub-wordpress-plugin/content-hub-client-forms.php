<?php defined('ABSPATH') or die('Plugin file cannot be accessed directly.'); ?>

<style type="text/css">
    .hub_small_font {
        font-size: 11px;
    }

    .hub_table_head {
        font-size: 12px;
        font-weight: bold;
        background-color: #e2e2e2;
    }
</style>

<h1>Content Hub</h1>

<hr>

<div class="wrap">

    <h3>Config</h3>

    <form id="hub_config_form" action="wp-admin/admin-post.php" method="post" style="width: 100%"
          onkeypress="return event.keyCode != 13;">
        <input type="hidden" name="action" value="save_config">

        <table>
            <tr>
                <td>Hub URL</td>
                <td><input size="60" type="text" name="defaultHubUrl" value="<?php echo $hubUrl ?>"></td>
            </tr>
            <tr>
                <td>Callback URL</td>
                <td><input type="text" size="60" name="callbackUrl" value="<?php echo $callbackUrl ?>"></td>
            </tr>
            <tr>
                <td>Signing Key ID</td>
                <td><input size="60" type="password" name="signingKeyId"> (leaving it blank won't change it)</td>
            </tr>
            <tr>
                <td>Signing Key Value</td>
                <td><input size="60" type="password" name="signingKeyValue"> (leaving it blank won't change it)</td>
            </tr>
            <tr>
                <td colspan="2"><input type="checkbox" name="acceptRawDelivery"
                        <?php if ($acceptRawDelivery) echo "checked" ?>>Accept Raw Delivery (check if subscribing to Hub 1.0)
                </td>
            </tr>
            <tr>
                <td colspan="2"><input type="checkbox" name="storeNotifications"
                        <?php if ($storeNotifications) echo "checked" ?>>Store notifications
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type="button"
                            onclick="hubConfigSubmitForm('#hub_config_form', '#config_status_message');">
                        Save
                    </button>
                </td>
            </tr>
        </table>

        <div id="config_status_message"></div>
    </form>

    <?php

    if ($signingKeyPending) {
        ?>
        <div id="hub_warning" style="color: red;">The signing keys are required to use the Hub.</div>
        <?php
    }
    ?>

    <div id="hub_actions" <?php if ($signingKeyPending) echo "style='display: none'" ?> >
        <hr>

        <h3>Publish</h3>

        <form id="hub_publish_form" action="wp-admin/admin-post.php" method="post" style="width: 100%">
            <input type="hidden" name="action" value="hub_publish">

            <table>
                <tr>
                    <td>Topic Name</td>
                    <td><input type="text" size="40" name="topicName"> (e.g. http://domain.co.uk/sports/football)</td>
                </tr>
                <tr>
                    <td>Content</td>
                    <td><textarea name="content" cols="59" rows="10"></textarea></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button type="button"
                                onclick="hubSubmitForm('#hub_publish_form', '#publish_status_message');">
                            Publish to Topic
                        </button>
                    </td>
                </tr>
            </table>
            <div id="publish_status_message"></div>
        </form>

        <hr>

        <h3>Subscribe</h3>

        <form id="hub_subscribe_form" action="wp-admin/admin-post.php" method="post" style="width: 100%"
              onkeypress="return event.keyCode != 13;">
            <input type="hidden" name="action" value="hub_subscribe">

            <table>
                <tr>
                    <td>Topic Name</td>
                    <td><input type="text" size="40" name="topicName"> (e.g. http://domain.co.uk/sports/football)</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button type="button"
                                onclick="hubSubmitForm('#hub_subscribe_form', '#subscribe_status_message');">
                            Subscribe to Topic
                        </button>
                    </td>
                </tr>
            </table>

            <div id="subscribe_status_message"></div>
        </form>

        <hr>

        <h3>Change Delivery Policy</h3>

        <form id="hub_change_delivery_policy_form" action="wp-admin/admin-post.php" method="post" style="width: 100%"
              onkeypress="return event.keyCode != 13;">
            <input type="hidden" name="action" value="hub_change_delivery_policy">

            <table>
                <tr>
                    <td>Topic Name</td>
                    <td><input type="text" size="40" name="topicName"> (e.g. sports/football)</td>
                </tr>
                <tr>
                    <td>Max receive rate (per second)</td>
                    <td><input size="60" type="number" name="maxReceiveRatePerSecond"
                               value="<?php echo $maxReceiveRatePerSecond ?>"></td>
                </tr>
                <tr>
                    <td>Number of retries</td>
                    <td><input size="60" type="number" name="numRetries" value="<?php echo $numRetries ?>"></td>
                </tr>
                <tr>
                    <td>Seconds between each retry</td>
                    <td><input size="60" type="number" name="secondsBetweenRetries"
                               value="<?php echo $secondsBetweenRetries ?>"></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button type="button"
                                onclick="hubSubmitForm('#hub_change_delivery_policy_form', '#change_delivery_policy_status_message');">
                            Change delivery policy
                        </button>
                    </td>
                </tr>
            </table>

            <div id="change_delivery_policy_status_message"></div>
        </form>
    </div>

    <hr>

    <h3>Subscriptions</h3>

    <table width="700">
        <tr class="hub_table_head">
            <td width="75%" style="font-weight: bold">Topic ARN</td>
            <td width="25%" style="font-weight: bold">Creation Date</td>
        </tr>
        <?php
        foreach ($subscriptions as $subscription) {
            ?>
            <tr class="hub_small_font">
                <td><?php echo $subscription->topic_arn ?></td>
                <td><?php echo $subscription->creation_date ?></td>
            </tr>
            <?php
        }
        ?>
    </table>

    <hr>

    <h3>Published Messages Received</h3>

    <table width="100%">
        <tr class="hub_table_head">
            <td style="font-weight: bold">ID</td>
            <td style="font-weight: bold">Topic Arn</td>
            <td style="font-weight: bold">Origin Topic Name</td>
            <td style="font-weight: bold">Content-Type</td>
            <td style="font-weight: bold">Message Date</td>
            <td style="font-weight: bold">Received Date</td>
        </tr>
        <?php
        foreach ($notifications as $notification) {
            ?>
            <tr class="hub_small_font">
                <td><?php echo $notification->id_notification ?></td>
                <td><?php echo $notification->topic_arn ?></td>
                <td><?php echo $notification->origin_topic_name ?></td>
                <td><?php echo $notification->content_type ?></td>
                <td><?php echo $notification->message_timestamp ?></td>
                <td><?php echo $notification->received_date ?></td>
            </tr>
            <?php
        }
        ?>
    </table>
</div>