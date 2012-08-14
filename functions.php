<?php

function string_limit_words($string, $word_limit)
{
  $words = explode(' ', $string, ($word_limit + 1));
  if(count($words) > $word_limit)
  array_pop($words);
  return implode(' ', $words);
}

function theme_init_theme() {
	# Enqueue jQuery
	wp_enqueue_script('jquery');
	if(!is_admin()) {
        # Enqueue Custom Scripts
        # @wp_enqueue_script attributes -- id, location, dependancies, version
    	wp_enqueue_script('jquery-flexslider', get_bloginfo('stylesheet_directory') . '/js/jquery.flexslider.js', array('jquery'), '1.8');
    	wp_enqueue_script('jquery-cufon', get_bloginfo('stylesheet_directory') . '/js/cufon-yui.js', array('jquery'), '1.0');
        wp_enqueue_script('jquery-Ubuntu', get_bloginfo('stylesheet_directory') . '/js/Ubuntu_bold_700.font.js', array('jquery'), '1.0');
        wp_enqueue_script('jquery-functions', get_bloginfo('stylesheet_directory') . '/js/functions.js', array('jquery', 'jquery-flexslider', 'jquery-Ubuntu', 'jquery-cufon'), '1.0');
	wp_enqueue_style('forms', get_bloginfo('stylesheet_directory') . '/forms.css');
	wp_enqueue_style('opensans', 'http://fonts.googleapis.com/css?family=Open+Sans:300,400,600');
	wp_enqueue_style('ubuntu', 'http://fonts.googleapis.com/css?family=Ubuntu:700');
    }
}
add_action('init', 'theme_init_theme');


add_action('after_setup_theme', 'theme_setup_theme');

# To override theme setup process in a child theme, add your own theme_setup_theme() to your child theme's
# functions.php file.
if ( ! function_exists( 'theme_setup_theme' ) ):
function theme_setup_theme() {
	include_once('lib/common.php');

	# Theme supports
	add_theme_support('automatic-feed-links');
	
	# Manually select Post Formats to be supported - http://codex.wordpress.org/Post_Formats
	// add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ) );

	# Register Theme Menu Locations
	
	add_theme_support('post-thumbnails');
	add_image_size('featured-thumb', 145, 93, 1);
	add_image_size('small-thumb', 98, 63, 1);
	add_image_size('big-thumb', 329, 210, 1);
    add_image_size('portfolio', 208, 152, 1);
    add_image_size('slide', 687, 264, 1);

	add_theme_support('menus');
	register_nav_menus(array(
		'main-menu'=>__('Main Menu'),
		'footer-menu'=>__('Footer Menu'),

	));

	# Register CPTs
	include_once('options/theme-post-types.php');
	
	# Attach custom widgets
	include_once('lib/custom-widgets/widgets.php');
	include_once('options/theme-widgets.php');
	
	# Add Actions
	add_action('widgets_init', 'theme_widgets_init');
	add_action('wp_loaded', 'attach_theme_options');
	add_action('wp_head', '_print_ie6_styles');

	# Add Filters
	
}
endif;

# Register Sidebars
# Note: In a child theme with custom theme_setup_theme() this function is not hooked to widgets_init
function theme_widgets_init() {
	register_sidebar(array(
		'name' => 'Default Sidebar',
		'id' => 'default-sidebar',
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>',
	));
	register_sidebar(array(
		'name' => 'Footer Sidebar',
		'id' => 'footer-sidebar',
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>',
	));
}

function attach_theme_options() {
	# Attach theme options
	include_once('lib/theme-options/theme-options.php');
	
	include_once('options/theme-options.php');
	// include_once('options/other-options.php');
	
	# Theme Help needs to be after options/theme-options.php
	include_once('lib/theme-options/theme-readme.php');
	
	# Attach ECFs
	include_once('lib/enhanced-custom-fields/enhanced-custom-fields.php');
	include_once('options/theme-custom-fields.php');
}

/* Custom code goes below this line. */

function social_links() {
    ?>
    <div class="social"><a target="_blank" href="<?php echo get_option('googleplus_link'); ?>" class="p-icon">Plus</a><a target="_blank" href="<?php echo get_option('facebook_link'); ?>" class="f-icon">Facebook</a><a target="_blank" href="<?php echo get_option('twitter_link'); ?>" class="t-icon">Twitter</a><div class="cl">&nbsp;</div></div>
    <?php
}

function clean_shortcode_content( $content ) {
    /* Parse nested shortcodes and add formatting. */
    $content = trim( wpautop( do_shortcode( $content ) ) );
    /* Remove '</p>' from the start of the string. */
    if ( substr( $content, 0, 4 ) == '</p>' )
    $content = substr( $content, 4 );

    /* Remove '<p>' from the end of the string. */
    if ( substr( $content, -3, 3 ) == '<p>' )
    $content = substr( $content, 0, -3 );

    /* Remove any instances of '<p></p>'. */
    $content = str_replace( array( '<p></p>' ), '', $content );

    return $content;
}

function show_intro() {
    if(is_home() || is_single() || is_archive() || is_404() || is_single() || is_search()) {
        $idblog = get_option('page_for_posts');
        if($idblog) {
            $post_desc = get_meta('_page_description', $idblog);
            $header_title = get_meta('_header_title', $idblog);
        }
    } else {
        $post_desc = get_meta('_page_description');
        $header_title = get_meta('_header_title');
    }
    if(get_post_type() == 'portfolio' ) {
        $page = get_page_by_path('portfolio');
        if($page) {
            $post_desc = get_meta('_page_description', $page->ID);
            $header_title = get_meta('_header_title', $page->ID);
        }
    }
    ?>
    <h2><?php if($header_title) echo $header_title;  else echo get_option('default_header_title'); ?><span><?php if($post_desc) echo $post_desc; else echo get_option('default_header_title'); ?></span></h2>
    <?php
}

function section_shortcode($atts, $content) {
    return clean_shortcode_content('<div class="case">' . $content . '</div>');  
}

function three_columns_shortcode($atts, $content) {
    return clean_shortcode_content('<div class="three-cols">' . $content . '</div>');  
}

function two_columns_shortcode($atts, $content) {
    return clean_shortcode_content('<div class="small-two-cols">' . $content . '</div>');  
}

add_shortcode('three_columns', 'three_columns_shortcode');
add_shortcode('two_columns', 'two_columns_shortcode');

add_shortcode('section', 'section_shortcode');

function web_pagination() {
    ?>
    <div class="left">
        <span> 
        <?php 
            echo previous_posts_link('&laquo; Older Entries');
        ?>
        </span>
    </div>
    <div class="right">
        <span>
        <?php
            echo next_posts_link('Next Entries &raquo;')
        ?>
        </span>
    </div>
    <?php
}


function cc_breadcrumbs($before='<div class="breadcrumbs">', $glue=' / ', $after='</div>') {
    global $post;
    
    $stack = array();

    $page_for_posts = get_option('page_for_posts', 0);
    array_push($stack, array(
            'title'=>'cubetech',
            'link'=>get_option('home'),
        )
    );
   	
    if (is_page()) {
        $page_id = $post->ID;
        $page_obj = get_page($page_id);
       
        $tmp = array();
       
        do {
            $tmp[] = array(
                'title'=>apply_filters('the_title', $page_obj->post_title),
                'link'=>get_permalink($page_obj->ID)
            );
        } while ($page_obj->post_parent!=0 && ($page_obj = get_page($page_obj->post_parent)));
       
        $tmp = array_reverse($tmp);
       
        foreach ($tmp as $breadcrumb_elem) {
            array_push($stack, $breadcrumb_elem);
        }
    } else {
        if ($page_for_posts) {
            $blog_page = get_page($page_for_posts);
            if(get_post_type() == 'post') {
                array_push($stack, array(
                    'title'=>apply_filters('the_title', $blog_page->post_title),
                    'link'=>get_permalink($blog_page->ID),
                ));
            }
        }
        
        if ( is_archive() ) {
            array_push($stack, array(
                'title'=>'Archive',
                'link'=>get_permalink(get_page_id_by_path('archive'))
            ));
        }
        if(get_post_type() == 'portfolio') {
            $page = get_page_by_path('portfolio');
            array_push($stack, array(
                'title'=>get_the_title($page->ID),
                'link'=>get_permalink($page->ID),
            ));
            array_push($stack, array(
                'title'=>get_the_title(),
                'link'=>get_permalink(),
            ));
        }
        else if (is_single()) {
        	if ($post->post_type == 'post') {
	           
	            array_push($stack, array(
	                'title'=>get_the_title(),
	                'link'=>get_permalink(),
	            ));
        	}

        } else if (is_category()) {
            $category = get_query_var('cat');
            $ancestors = array_reverse(cc_category_parents($category));
           
            foreach ($ancestors as $breadcrumb_elem) {
                array_push($stack, $breadcrumb_elem);
            }
        } else if (is_tag()) {
            array_push($stack, array(
                'title'=>single_tag_title('', false),
                'link'=>get_tag_link(get_query_var('tag'))
            ));
        } else if (is_day() ) {
            array_push($stack, array(
                'title' => get_the_time('F j, Y'),
                'link' => get_day_link(get_the_time('j'), get_the_time('F'),  get_the_time('Y'))
            ));
        } else if (is_month()) {
            array_push($stack, array(
                'title' => get_the_time('F Y'),
                'link' => get_month_link(get_the_time('F'),  get_the_time('Y'))
            ));
        } else if (is_year()) {
            array_push($stack, array(
                'title' => get_the_time('Y'),
                'link' => get_year_link(get_the_time('Y'))
            ));
        } else if (is_search()) {
            array_push($stack, array(
                'title' => 'Suchresultate',
                'link' => '#'
            ));
        }
    }
   
    if (($page_for_posts && count($stack)<2) || count($stack)<1) {
        return;
    }
   
    $elems = array();
    $i = 0;
    foreach ($stack as $elem) {
        if ($i==count($stack)-1) {
            //$html = '<a href="' . $elem['link'] . '" class="active">' . $elem['title'] . '</a>';
            $html = '<span>' . $elem['title'] . '</span>';
        } else {
            $html = '<a href="' . $elem['link'] . '">' . $elem['title'] . '</a>';
        }        
        $elems[] = $html;
        $i++;
    }
    
    echo $before . implode($glue, $elems) . $after;
}

function cc_category_parents($cat_id) {
    $return = array();
    $cat = get_category($cat_id);
    do {
        $return[] = array(
            'title'=>$cat->name,
            'link'=>get_category_link($cat->term_id)
        );
    } while ($cat->parent!=0 && ($cat = get_category($cat->parent)));	
    return $return;
}
/* Custom code goes above this line. */

/*********************************
Description:  Zeigt bestimmte Bildformate immer in der Lightbox an,
wenn das Plugin "jQuery Lightbox For Native Galleries" installiert ist.
Author:       Artur Weigandt
Author URI:   http://www.wlabs.de
*********************************/
add_action('wp_footer', 'wl_all_images_in_lightbox');
function wl_all_images_in_lightbox()
{
    //Ist das jQueryLightboxForNativeGalleries-Plugin installiert?
    if(!class_exists('jQueryLightboxForNativeGalleries'))
        return;
 
    //Bild-Typen angeben, die in der Lightbox angezeigt werden sollen
    $image_types = array('jpg', 'jpeg', 'gif', 'png');
 
    //Abmasse der Lightbox
    $maxWidth = "95%";
    $maxHeight = "95%";
 
    ?>
<script type="text/javascript">
    jQuery(document).ready(function($){
<?php for($i = 0; $i < count($image_types); $i++) { ?>
        $('a[href$=".<?php echo $image_types[$i]; ?>"]').colorbox({maxWidth:"<?php echo $maxWidth; ?>", maxHeight:"<?php echo $maxHeight; ?>"});
<?php } ?>
    });
</script><?php
}

?>
