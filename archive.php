<?php get_header(); ?>
	<?php cc_breadcrumbs(); ?>
	<!-- End Breadcrumbs -->
	<!--  Sidebar -->
	<?php get_sidebar(); ?>
	<!-- End Sidebar -->
	<!-- Content -->
			<div id="content">
				<h2 class="pagetitle">
					<?php if (is_category()) { ?>
						Archiv f&uuml;r die Kategorie &#8216;<?php single_cat_title(); ?>&#8217;
					<?php } elseif( is_tag() ) { ?>
						Beitr&auml;ge, die mit &#8216;<?php single_tag_title(); ?>&#8217 getaggt sind;
					<?php } elseif (is_day()) { ?>
						Archive for <?php the_time('F jS, Y'); ?>
					<?php } elseif (is_month()) { ?>
						Archive for <?php the_time('F, Y'); ?>
					<?php } elseif (is_year()) { ?>
						Archive for <?php the_time('Y'); ?>
					<?php } elseif (is_author()) { ?>
						Author Archive
					<?php } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
						Blogarchive
					<?php } ?>
				</h2>
				<br />
				<?php get_template_part('loop'); ?>	
			</div>
			<!-- End Content -->
			<div class="cl">&nbsp;</div>

<?php get_footer(); ?>
