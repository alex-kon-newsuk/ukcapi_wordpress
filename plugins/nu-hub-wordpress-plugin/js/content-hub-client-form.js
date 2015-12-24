function hubSubmitForm(formId, divIdToDisplayMessage) {
	var data = jQuery(formId).serialize();
	clearAllStatusMessages();
	jQuery.post(ajaxurl, data,
		function (response) {
			jQuery(divIdToDisplayMessage).html(response);
		})
		.fail(
		function (data) {
			jQuery(divIdToDisplayMessage).html(data.responseText);
		}
	);
}

function hubConfigSubmitForm(formId, divIdToDisplayMessage) {
	var data = jQuery(formId).serialize();
	clearAllStatusMessages();
	jQuery.post(ajaxurl, data,
		function (response) {
			jQuery(divIdToDisplayMessage).html(response);
			showPublishAndSubscribeForms();
		})
		.fail(
		function (data) {
			jQuery("#hub_warning").html(data.responseText);
		}
	);
}

function showPublishAndSubscribeForms() {
	jQuery("#hub_actions").show();
}

function clearAllStatusMessages() {
	jQuery("#subscribe_status_message").html("");
	jQuery("#publish_status_message").html("");
	jQuery("#config_status_message").html("");
	jQuery("#change_delivery_policy_status_message").html("");
	jQuery("#hub_warning").html("");
}
