<?php
function ecf_conf_error($message) {
    exit("<strong>Enhanced Custom Fields configuration error: </strong>$message");
}
include_once(dirname(__FILE__) . '/panel.php');
include_once(dirname(__FILE__) . '/fields.php');

/*
Updates an image url (as supplied by efc_fieldimage) to a full image url.
Sidenote: Why arent values stored as full urls and stored in default wp uploads-style categorization?
*/
function ecf_get_image_url($url) {
	$upload_url_path = get_option('upload_url_path');
	if (!$upload_url_path) {
		$upload_url_path = get_option('home') . '/wp-content/uploads/';
	}
	return $upload_url_path . $url;
}
?>