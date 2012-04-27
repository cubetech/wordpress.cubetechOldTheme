<?php get_header(); ?>
	<?php cc_breadcrumbs(); ?>
	<!-- End Breadcrumbs -->
	<!--  Sidebar -->
	<?php get_sidebar(); ?>
	<!-- End Sidebar -->
	<!-- Content -->
			<div id="content">
				<h2 class="pagetitle"><?php _e('Search Results'); ?></h2>
				<br />
				<?php get_template_part('loop', 'two') ?>
			</div>
			<!-- End Content -->
			<div class="cl">&nbsp;</div>

<?php get_footer(); ?>