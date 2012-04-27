<?php
include_once('twitter/versions-proxy.php');
include_once('hacks.php');
include_once('video-functions.php');
include_once('user-functions.php');
include_once('comments.php');
#include_once('pps.php');

# ------------------------------ General ------------------------------ #

#
# PHP 4.2.x Compatibility function
# http://www.php.net/manual/en/function.file-get-contents.php#80707
#
if (!function_exists('file_get_contents')) {
	function file_get_contents($filename, $incpath = false, $resource_context = null) {
		if (false === $fh = fopen($filename, 'rb', $incpath)) {
			trigger_error('file_get_contents() failed to open stream: No such file or directory', E_USER_WARNING);
			return false;
		}
		
		clearstatcache();
		if ($fsize = @filesize($filename)) {
			$data = fread($fh, $fsize);
		} else {
			$data = '';
			while (!feof($fh)) {
				$data .= fread($fh, 8192);
			}
		}
		
		fclose($fh);
		return $data;
	}
}

#
# Sends emails to an array of recipients (taks both string and array values).
#
function _send_mail($mailto, $from, $subject, $mailcontent, $headers = '') {
	if (!$headers) {
		$headers = "From: $from \nReply-To: $from \nReturn-Path: $from \nX-Mailer: PHP\n";
		$headers .= "Content-Transfer-Encoding: 8bit\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\n";
	}
	
	if (!is_array($mailto)) {
		$mailto = array($mailto);
	}
	foreach ($mailto as $m) {
		@mail($m, $subject, $mailcontent, $headers);
	}
}

#
# Adds slashes to GPC (For db queries)
#
function gpc($field) {
	return get_magic_quotes_gpc($field) ? $field : addslashes($field);
}

#
# Strips slashes from GPC (For emails)
#
function reverse_gpc($field) {
	return get_magic_quotes_gpc($field) ? stripslashes($field) : $field;
}

#
# Returns the directory path to the uploads directory
#
function get_upload_dir() {
	$updir = wp_upload_dir();
	return $updir['basedir'];
}

#
# Returns the public URL to the uploads directory
#
function get_upload_url() {
	$updir = wp_upload_dir();
	return $updir['baseurl'];
}

#
# Truncates a strung to a certain amount of words.
#
# >>> shortalize('lorem ipsum dolor sit amet');
# ... lorem ipsum dolor sit amet
# >>> shortalize('lorem ipsum dolor sit amet', 5);
# ... lorem ipsum dolor sit amet
# >>> shortalize('lorem ipsum dolor sit amet', 4);
# ... lorem ipsum dolor sit...
# >>> shortalize('lorem ipsum dolor sit amet', -1);
#
define('STR_WORD_COUNT_FORMAT_ADD_POSITIONS', 2);
function shortalize($input, $words_limit=15, $strip_tags = true, $end = '...') {
	if ($strip_tags) {
		$input = strip_tags($input);
	}
    $words_limit = abs(intval($words_limit));
    if ($words_limit==0) {
        return $input;
    }
    $words = str_word_count($input, STR_WORD_COUNT_FORMAT_ADD_POSITIONS, '0123456789');
    if (count($words)<=$words_limit + 1) {
        return $input;
    }
    $loop_counter = 0;
    foreach ($words as $word_position => $word) {
        $loop_counter++;
        if ($loop_counter==$words_limit + 1) {
            return substr($input, 0, $word_position) . $end;
        }
    }
}

#
# DATED FUNCTION
# 
# Shortcut function for acheiving
# $no_nav_pages = _get_page_by_name('no-nav-pages');
# wp_list_pages('sort_column=menu_order&exclude_tree=' . $no_nav_pages->ID);
# with:
# wp_list_pages('sort_column=menu_order&' . exclude_no_nav());
#
function exclude_no_nav($no_nav_pages_slug='no-nav-pages') {
    $no_nav_page = _get_page_id_by_name($no_nav_pages_slug);
    return "exclude_tree=$no_nav_page";
}

#
# Checks if particular page ID has parent with particular slug
#
$__has_parent_depth = 0;
function has_parent($id, $parent_name) {
    global $__has_parent_depth;
    $__has_parent_depth++;
    if ($__has_parent_depth==100) {
    	exit('too much recursion');
    }
    $post = get_post($id);
    
    if ($post->post_name==$parent_name) {
    	return true;
    }
    if ($post->post_parent==0) {
    	return false;
    }
    $__has_parent_depth--;
    return has_parent($post->post_parent, $parent_name);
}

# ------------------------------ Page / Post ------------------------------ #

# Crawls the pages tree up to top level page ancestor 
# and returns that page as object
function get_page_ancestor($page_id) {
    $page_obj = get_page($page_id);
    while($page_obj->post_parent!=0) {
        $page_obj = get_page($page_obj->post_parent);
    }
    return get_page($page_obj->ID);
}

#
# Returns the ID of the page with the provided path
#
function get_page_id_by_path($page_path) {
    $p = get_page_by_path($page_path);
    if (empty($p)) {
    	return null;
    }
    return $p->ID;
}

#
# Returns the permalink of the page with the provided path
#
function get_page_permalink_by_path($page_path) {
    $p = get_page_by_path($page_path);
    if (empty($p)) {
    	return '';
    }
    return get_permalink($p->ID);
}

#
# Shortcut function for outputting page permalinks. Example: permapath('about/our-company');
#
function permapath($path, $echo = true) {
	$permalink = get_permalink(get_page_id_by_path($path));
	if ($echo) {
		echo $permalink;
	}
	return $permalink;
}

#
# Returns an array containing all images (obejcts) attached to a post.
# thumb_url = image thumbnail url
# full_url = full sized image url
#
function get_post_images($post_id=null) {
	global $post;
	$post_id = empty($post_id) ? $post->ID : $post_id;
    $images = get_children('post_type=attachment&post_mime_type=image&post_parent=' . $post_id . '&orderby=menu_order&order=ASC');
    foreach ($images as $index => $i) {
	    $images[$index]->thumb_url = wp_get_attachment_thumb_url($i->ID);
	    $images[$index]->full_url = wp_get_attachment_url($i->ID);
	}
    return $images;
}

#
# Returns posts page as object (setuped from Settings > Reading > Posts Page).
#
# If the page for posts is not chosen null is returned
#
function get_posts_page() {
    $posts_page_id = get_option('page_for_posts');
    if ($posts_page_id) {
    	return get_page($posts_page_id);
    }
    return null;
}

# ------------------------------ Taxonomy ------------------------------ #

# Crawls the taxonomy tree up to top level taxonomy ancestor 
# and returns that taxonomy as object
# $taxonomy - the slug of the taxonomy
function get_taxonomy_ancestor($term_id, $taxonomy) {
    $term_obj = get_term_by('id', $term_id, $taxonomy);
    while($term_obj->parent!=0) {
        $term_obj = get_term_by('id', $term_obj->parent, $taxonomy);
    }
    return get_term_by('id', $term_obj->term_id, $taxonomy);
}

# ------------------------------ Meta ------------------------------ #

# Shortcut for get_post_meta. Returns the string value 
# of the custom field if it exist. 
# second arg is required if you're not in the loop
function get_meta($key, $id=null) {
	if (!isset($id)) {
	    global $post;
	    if (empty($post->ID)) {
	    	return null;
	    }
	    $id = $post->ID;
    }
    return get_post_meta($id, $key, true);
}


#
# Parses custom field values to hash array. Expected 
# custom field value format:
# {{{
# title: my cool title
# image: http://example.com/images/1.jpg
# caption: my cool image
# }}}
# Returned array looks like:
# array(
#     'title'=>'my cool title',
#     'image'=>'http://example.com/images/1.jpg',
#     'caption'=>'my cool image',
# )
#
function parse_custom_field($details) {
    $lines = array_filter(preg_split('~\r|\n~', $details));
    $res = array();
    foreach ($lines as $line) {
        if(!preg_match('~(.*?):(.*)~', $line, $pieces)) {
            continue;
        }
        $label = trim($pieces[1]);
        $val = trim($pieces[2]);
        $res[$label] = $val;
    }
    return $res;
}

# ------------------------------ Templates ------------------------------ #

#
# Prints the IE 6 stylesheet replacing the image paths for the Microsoft AlphaLoader to work
#
function _print_ie6_styles() {
    $ie_css_file = dirname(dirname(__FILE__)) . '/ie6.css';
    
	if (!file_exists($ie_css_file)) {
    	return;
    }
    $ie6_hacks = file_get_contents($ie_css_file);
    if (empty($ie6_hacks)) {
    	return;
    }
    
    echo '
<!--[if IE 6]>
<style type="text/css" media="screen">';
    echo "\n\n" . str_replace(
    	'css/images/', 
    	get_bloginfo('stylesheet_directory') . '/images/', 
    	$ie6_hacks
    );
    echo '

</style>
<![endif]-->';
}

# Example function for filtering page template
function filter_template_name() {
    global $post;
    
	$page_tpl = get_post_meta($post->ID, '_wp_page_template', 1);
	
	if ($page_tpl!="default") {
		return TEMPLATEPATH . '/' . $page_tpl;
	}
    /*
	# example logic here ... 
    $page_ancestor = get_page_ancestor($post->ID);
    
    if ($page_ancestor->post_name!='pages-branch-name') {
    	return TEMPLATEPATH . "/my-branch-template.php";
    }
    
    return TEMPLATEPATH . "/page.php";
    */
}
# add_filter('page_template', 'filter_template_name');
?>