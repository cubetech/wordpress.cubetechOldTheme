<?php
function attach_main_options_page() {
	$title = "Theme Options";
	add_menu_page(
		$title,
		$title, 
		'edit_themes', 
	    basename(__FILE__),
		create_function('', '')
	);
}
add_action('admin_menu', 'attach_main_options_page');

$inner_options = new OptionsPage(array(
	wp_option::factory('text', 'header_text')->set_default_value('Hello. Welcome to cubetech! <br />Feel free to check out our work or so.'),
	wp_option::factory('text', 'default_header_title')->set_default_value('Default header title'),
	wp_option::factory('text', 'default_header_description')->set_default_value('Default Description'),
	wp_option::factory('text', 'facebook_link')->set_default_value('http://www.facebook.com'),
	wp_option::factory('text', 'twitter_link')->set_default_value('http://www.twitter.com'),
	wp_option::factory('text', 'googleplus_link')->set_default_value('http://www.google.com/+'),
	wp_option::factory('text', 'copyright')->set_default_value('(c) ' . date('Y') . ' Allright reservred'),
    wp_option::factory('header_scripts', 'header_script'),
    wp_option::factory('footer_scripts', 'footer_script'),
));
$inner_options->title = 'General';
$inner_options->file = basename(__FILE__);
$inner_options->parent = "theme-options.php";
$inner_options->attach_to_wp();

?>