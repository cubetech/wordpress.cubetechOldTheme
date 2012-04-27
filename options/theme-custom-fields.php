<?php

$page_settings_panel =& new ECF_Panel('page-settings', 'Page Settings', 'page', 'normal', 'high');
$page_settings_panel->add_fields(array(
	ECF_Field::factory('text', 'header_title'),
	ECF_Field::factory('text', 'page_description'),
	ECF_Field::factory('choose_sidebar', 'custom_sidebar', 'Sidebar'),
));

$contact_settings_panel =& new ECF_Panel('contact-settings', 'Contact Settings', 'page', 'normal', 'high');
$contact_settings_panel->show_on_template('page-contact.php');
$contact_settings_panel->add_fields(array(
	ECF_Field::factory('image', 'googlemap_icon', 'Google Map Icon'),
	ECF_Field::factory('map', 'googlemap', 'Google Map'),
	ECF_Field::factory('address', 'googleaddress', 'Google Map Adress'),
));

$pageportfolio_settings_panel =& new ECF_Panel('portfolio-page-settings', 'Portfolio page Settings', 'page', 'normal', 'high');
$pageportfolio_settings_panel->show_on_template('page-portfolio.php');
$pageportfolio_settings_panel->add_fields(array(
	ECF_Field::factory('text', 'projects_per_page'),
));


$portfolio_settings_panel =& new ECF_Panel('portfolio-settings', 'Project Settings', 'portfolio', 'normal', 'high');
$portfolio_settings_panel->add_fields(array(
	ECF_Field::factory('text', 'project_description'),
	ECF_Field::factory('image', 'project_featurer_image')->set_size(208, 152)->help_text('Image size is 208px x 152px'),
));

$page_settings_panel =& new ECF_Panel('page-default-settings', 'Page Default Template Settings', 'page', 'normal', 'high');
$page_settings_panel->show_on_template('default');
$page_settings_panel->add_fields(array(
	ECF_Field::factory('rich_text', 'left_bototm_section'),
	ECF_Field::factory('rich_text', 'right_bototm_section'),
));
?>