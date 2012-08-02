<?php get_header(); ?>
	<?php cc_breadcrumbs(); ?>
	<!-- End Breadcrumbs -->
	<!--  Sidebar -->
	<?php get_sidebar(); ?>
	<!-- End Sidebar -->
	<!-- Content -->
	<div id="content">
        <div class="content">
		<?php get_template_part('loop', 'single'); ?>
        </div>
	</div>
	<!-- End Content -->
	<div class="cl">&nbsp;</div>
<?php get_footer(); ?>
