<?php
#
# Returns currently logged in user's ID or NULL if the user is not logged in
#
function _is_logged_in() {
	get_currentuserinfo();
	global $user_ID;
	return $user_ID;
}

#
# Returns the currently logged in user's object
#
function _get_current_user() {
	global $userdata;
	get_currentuserinfo();
	return $userdata;
}

#
# Redirects if the current user is not logged in.
# Note: Careful with the $redirect - may cause infinite redirection loop if the redirect requires login as well
#
function _require_login($redirect = '') {
	if (!_is_logged_in()) {
		$redirect = ($redirect) ? $redirect : get_option('home');
		header('Location: ' . $redirect);
		exit;
	}
}

#
# Redirects if the current user is not of the specified level.
# Note: Admins are always alowed.
# Note: Careful with the $redirect - may cause infinite redirection loop if the redirect requires login as well
#
function _require_user_level($level, $redirect = '') {
	$u = _get_current_user();
	if (!_user_is($u->ID, 'administrator') && !_user_is($u->ID, $level)) {
		$redirect = ($redirect) ? $redirect : get_option('home');
		header('Location: ' . $redirect);
	}
}

#
# Returns True or False depending on whether the specified user has the specified role
#
function _user_is($user_id, $capability) {
	global $wpdb;
	$all_capabilities = get_user_meta($user_id, $wpdb->prefix . 'capabilities', true);
	return isset($all_capabilities[$capability]);
}
?>