<?php get_header(); ?>
	<?php cc_breadcrumbs(); ?>
	<!-- End Breadcrumbs -->
	<!--  Sidebar -->
	<?php get_sidebar(); ?>
	<!-- End Sidebar -->
	<!-- Content -->
			<div id="content">
				<h2 class="pagetitle"><?php _e('Error 404 - Not Found'); ?></h2>
				<p><?php printf(__('Please check the URL for proper spelling and capitalization. If you\'re having trouble locating a destination, try visiting the <a href="%1$s">home page</a>'), get_option('home')); ?></p>
			</div>
			<!-- End Content -->
			<div class="cl">&nbsp;</div>

<?php get_footer(); ?>