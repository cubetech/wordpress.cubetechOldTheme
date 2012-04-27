<?php
/*

Page per Sidebar - allows different configuration of widgets for each particular page.

It creates a sidebar, called Pages Widgets.
All widgets that will be used on pages should be inserted there.
After that, users can choose and order their desired widgets for each particular page while editing the page.

To call this sidebar, simply use:

<?php dynamic_sidebar(PAGE_WIDGETS_SIDEBAR); ?>

in your sidebar file (for example, sidebar.php)

*/

define('PAGE_WIDGETS_SIDEBAR', 'sidebar-left');
define('PAGE_WIDGETS_SIDEBAR_CREATED', false);

if (!PAGE_WIDGETS_SIDEBAR_CREATED) {
	register_sidebar(array(
	    'id'=>PAGE_WIDGETS_SIDEBAR,
	    'name'=>'Pages Widgets',
	    'description'=>'Drag all widgets that you want to be visible on your pages. Once the widget is here you\'ll be able to choose it on individual page.',
	    'before_widget' => '<li id="%1$s" class="widget %2$s">',
	    'after_widget' => '</li>',
	    'before_title' => '<h2 class="widgettitle">',
	    'after_title' => '</h2>',
	));
}

class pps_widgets_sorter {
    function pps_widgets_sorter($used_widgets) {
        $this->used_widgets = (array)$used_widgets;
        $this->used_widgets_positions = array_flip(array_keys($this->used_widgets));
    }
    function sort_callback($widget_1, $widget_2) {
        if (!isset($this->used_widgets[$widget_1])) {
            return 1;
        }
        if (!isset($this->used_widgets[$widget_2])) {
            return -1;
        }
        $widget_1_position = $this->used_widgets_positions[$widget_1];
        $widget_2_position = $this->used_widgets_positions[$widget_2];
        if ($widget_1_position > $widget_2_position) {
            return 1;
        } else {
            return -1;
        }
    }
}
function pps_get_page_widgets($page_id) {
    return get_post_meta($page_id, '_page_widgets', 1);
}
function pps_print_the_box() {
    global $wp_registered_widgets;
    
    $sidebars = wp_get_sidebars_widgets();
    
    if (!isset($sidebars[PAGE_WIDGETS_SIDEBAR])) {
        echo "Register Sidebar with id <code>" . PAGE_WIDGETS_SIDEBAR . "</code> in order to use this option";
        return;
    }
    if (empty($sidebars[PAGE_WIDGETS_SIDEBAR])) {
        echo "Add some Widgets to Pages Widgets sidebar in Appearance > Widgets in order to use them here";
        return;
    }
    $all_widgets = $sidebars[PAGE_WIDGETS_SIDEBAR];
    
    if (isset($_GET['post'])) { // editing post
        $used_widgets = pps_get_page_widgets(intval($_GET['post']));
        $sorter = new pps_widgets_sorter($used_widgets);
        usort($all_widgets, array($sorter, 'sort_callback'));
    }
    ?>
    <style type="text/css" media="screen">
    	#page-widgets-list input,
    	#hide-all-widgets {
    		margin-right: 6px !important;
    		margin-top: 0px !important;
    	}
    </style>
    <?php
    echo '<p>Check which modules you want to appear, drag and drop to reorder</p>';
    echo "<ul id='page-widgets-list'>";
    foreach ($all_widgets as $widget) {
        $widget_obj = $wp_registered_widgets[$widget];
        
        if (isset($widget_obj['callback'][0]) && is_a($widget_obj['callback'][0], 'WP_Widget_Text')) {
            // Text widgets should be threaded specially since their title should be visible
            $widget_fields = $widget_obj['callback'][0]->get_settings();
            $title = 'Text Widget: ';
            if (!empty($widget_fields[$widget_obj['params'][0]['number']])) { 
			    $title .= $widget_fields[$widget_obj['params'][0]['number']]['title']; 
			} 
        } else {
            $title = $widget_obj['name'];
        }
        $checked = '';
        if (isset($used_widgets[$widget])) {
            $checked = 'checked="checked" ';
        }
        echo '<li style="cursor: move"><input type="checkbox" name="widgets[' . $widget_obj['id'] . ']" value="1" ' . $checked . ' /><span>' . $title . '</span></li>';
    }
    echo "</ul>";
    // With this we now that we're not in quick edit mode
    $checked = get_post_meta(isset($_GET['post']) && $_GET['post'] , '_hide_all_widgets', true);
    echo '<br /><input type="checkbox" name="hide_all_widgets" id="hide-all-widgets" ' . (($checked) ? 'checked="checked"' : '') . '/><span>Hide All Widgets</span>';
    echo '<input type="hidden" name="edit_page_widgets" value="1" />';
    ?>
    <script type="text/javascript" charset="utf-8">
        jQuery(function () {
            jQuery('#page-widgets-list').sortable({
                axis: 'y',
                cursor: 'move'
            });
        });
        
    </script>
    <?php
}
function pps_attach_the_box() {
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-sortable');    
    
    add_meta_box('perpagesidebar', "Page Widgets", 'pps_print_the_box', 'page', 'side', 'low');
}
add_action('admin_menu', 'pps_attach_the_box');

function pps_save_widgets($page_id) {
    if ( $the_post = wp_is_post_revision($page_id) )
        $page_id = $the_post;
    
    if ( isset($_POST['edit_page_widgets']) && !empty($_POST['edit_page_widgets']) && !empty($page_id)) {
        update_post_meta($page_id, '_page_widgets', $_POST['widgets']);
        if (isset($_POST['hide_all_widgets'])) {
        	update_post_meta($page_id, '_hide_all_widgets', 'hide');
        } else {
        	update_post_meta($page_id, '_hide_all_widgets', '');
        }
    }
}
add_action('save_post', 'pps_save_widgets');

function pps_filter_frontend_widgets($widgets) {
    global $wp_query;
    if (!is_page()) {
        return $widgets;
    }
    global $post;
    $page = $post;
    
    if (is_admin()) {
        return $widgets;
    }
    // if (!is_singular() || empty($widgets[PAGE_WIDGETS_SIDEBAR]) || is_single()) {
    //     return $widgets;
    // }
    if (is_home() && !is_front_page()) {
    	$page = get_post(geT_option('page_for_posts'));
    }
    
    if ($page->post_type != 'page' || empty($widgets[PAGE_WIDGETS_SIDEBAR])) {
    	return $widgets;
    }
    
    $hide_all_widgets = get_post_meta($page->ID, '_hide_all_widgets', true);
    if ($hide_all_widgets) {
        $widgets[PAGE_WIDGETS_SIDEBAR] = array();
        return $widgets;
    }
    
    $id = $page->ID;
    $used_widgets = pps_get_page_widgets($id);
    if (empty($used_widgets) || !is_array($used_widgets)) {
        return $widgets;
    }
    
    $widgets[PAGE_WIDGETS_SIDEBAR] = array_keys($used_widgets);
    
    return $widgets;
}
add_filter('sidebars_widgets', 'pps_filter_frontend_widgets');
?>