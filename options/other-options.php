<?php

$opt = new OptionsPage(array(
    wp_option::factory('text', 'twitter_username'),
));
$opt->title = 'Other options';

$opt->file = basename(__FILE__);
$opt->parent = "theme-options.php";
$opt->attach_to_wp();
?>
