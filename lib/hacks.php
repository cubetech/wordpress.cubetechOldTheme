<?php
# This file contains functions that are using direct SQL queries instead of 
# WP API functions.

#
# Gets all pages/posts which have the specified custom field. Does not check wheather it has any value - just if it has the custom field
# Return empty array if no pages/posts have been found
#
function _get_content_by_meta_key($meta_key) {
	global $wpdb;
	$result = $wpdb->get_col('
		SELECT DISTINCT(post_id)
		FROM ' . $wpdb->postmeta . '
		WHERE meta_key = "' . $meta_key . '"
	');
	if(empty($result)) {
	    return array();
	}
	return $result;
}

# deprecated - use get_adjacent_post instead()
function _get_previous_post($current_post_id, $exclude_categories_string) {
	return 'This function is deprecated. Use get_adjacent_post() instead.';
}

# deprecated - use get_adjacent_post instead()
function _get_next_post($current_post_id, $exclude_categories_string) {
	return 'This function is deprecated. Use get_adjacent_post() instead.';
}
?>