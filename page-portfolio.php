<?php 
/*
Template Name: Portfolio
*/
get_header();
	the_post();
	?>
	<?php cc_breadcrumbs(); ?>
	<!-- End Breadcrumbs -->
	<!--  Sidebar -->
	<?php get_sidebar(); ?>
	<!-- End Sidebar -->
	<!-- Content -->
	<div id="content">
		<!-- Projects -->
		<div class="projects">
			<ul>
				<?php 
				$project_per_page = get_meta('_projects_per_page');
				$args = array(
					'post_type' => 'portfolio',
					'posts_per_page' => $project_per_page,
					'orderby' => 'menu_order',
					'order' => 'ASC',
				);
				query_posts($args);
				while(have_posts()) :
					the_post();
					$image = get_meta('_project_featurer_image');
					$desc = get_meta('_project_description');
					if($image) :
						?>
						<li>
							<div class="item">
								<a href="<?php the_permalink(); ?>"><img src="<?php echo ecf_get_image_url($image); ?>" alt="Image" /></a>
								<p><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a><span><?php echo $desc; ?></span></p>
							</div>
						</li>
						<?php
					endif;
				endwhile;
				wp_reset_query();
				?>
			</ul>
			<div class="cl">&nbsp;</div>
		</div>
		<!-- End Projects -->
	</div>
	<!-- End Content -->
	<div class="cl">&nbsp;</div>
<?php get_footer(); ?>