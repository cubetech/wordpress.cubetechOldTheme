<?php
add_action('admin_menu', 'add_theme_readme');

function add_theme_readme() {
	wp_register_style('theme-help-style', get_bloginfo('stylesheet_directory') . '/lib/theme-options/theme-help.css', array(), '0.1', 'screen');
	wp_enqueue_style('theme-help-style');
	add_submenu_page('theme-options.php', get_bloginfo('name') . ' Theme Help', 'Theme Help', 'administrator', 'theme-readme', 'theme_include_help');
}

function theme_include_help() {
	?>
	<div id="theme-help">
		<div id="summary-holder">
			<h3>Summary</h3>
		</div>
		<div class="help-content">
		</div>
	</div>
	<script type="text/javascript" language="javascript">
	jQuery(function($) {
		$('#theme-help .help-content').load('<?php bloginfo('url'); ?>/wp-content/readme.html #inline-readme', function() {
			$('#installation').parent().remove();
			
			var topDepth = 3; // the depth of the outmost
			var summary = '<ol>';
			var headings = $('h2, h3, h4, h5, h6').filter('[id]');
			
			function depth(item) {
				if (typeof(item) == 'undefined') return 0;
				switch (item.tagName) {
					case 'H1':
					case 'H2':
					case 'H3':
					case 'H4':
					case 'H5':
					case 'H6':
						return item.tagName.substr(1, 1);
						break;
					default:
						return 0;
				}
			}
			
			for (var i=0; i<headings.length; ++i) {
				var item = headings[i];
				if (typeof(item) != 'object') continue;
				var previous = headings[i-1];
				var next = headings[i+1];
				
				if (typeof(previous) != 'undefined') {
					if (depth(previous) > depth(item)) {
						for (var j=0; j < depth(previous) - depth(item); ++j) {
							summary += '</li></ol></li>';
						}
					} else if (depth(previous) == depth(item)) {
						summary += '</li>';
					}
				}
				
				summary += '<li><a href="#' + $(item).attr('id') + '">' + $(item).text() + '</a>';
				
				if (typeof(next) != 'undefined') {
					if (depth(next) - depth(item) == 1) {
						summary += '<ol>';
					}
				} else {
					summary += '</li>';
				}
			}
			
			for (var i=0; i<depth(headings[headings.length-1]) - topDepth; ++i) {
				summary += '</ol></li>';
			}
			
			summary += '</ol>';
			$('#summary-holder').append(summary);		
		});
		
	});
	</script>
	<!--[if lt IE 8]>
	<style type="text/css" media="screen">
	#theme-help { padding-left: 10px; }
	#theme-help #inline-readme { list-style-type: decimal; }
	</style>
	<![endif]-->
	<?php
}
?>