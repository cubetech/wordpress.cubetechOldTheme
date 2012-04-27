<?php  

register_post_type('portfolio', array(
	'labels' => array(
		'name'	 => 'Portfolio',
		'singular_name' => 'Project',
		'add_new' => __( 'Add New' ),
		'add_new_item' => __( 'Add new Project' ),
		'view_item' => 'View Project',
		'edit_item' => 'Edit Project',
	    'new_item' => __('New Project'),
	    'view_item' => __('View Project'),
	    'search_items' => __('Search Projects'),
	    'not_found' =>  __('No  Projects found'),
	    'not_found_in_trash' => __('No projects found in Trash'),
	),
	'public' => true,
	'exclude_from_search' => false,
	'show_ui' => true,
	'capability_type' => 'post',
	'hierarchical' => true,
	'_edit_link' =>  'post.php?post=%d',
	'rewrite' => array(
		"slug" => "project",
		"with_front" => false,
	),
	'query_var' => true,
	'supports' => array('title', 'editor', 'page-attributes'),
));





?>