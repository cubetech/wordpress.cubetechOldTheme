<?php get_header(); ?>
	<?php cc_breadcrumbs(); ?>
	<!-- End Breadcrumbs -->
	<!--  Sidebar -->
	<?php get_sidebar(); ?>
	<!-- End Sidebar -->
	<!-- Content -->
	<div id="content">
		<!-- Post -->
		<?php get_template_part('loop'); ?>
		<div class="cl">&nbsp;</div>
	</div>
	<!-- End Content -->
	<div class="cl">&nbsp;</div>
<?php get_footer(); ?>