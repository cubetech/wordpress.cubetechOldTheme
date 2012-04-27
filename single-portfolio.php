<?php get_header();
	the_post(); ?>
	<?php cc_breadcrumbs(); ?>
	<!-- End Breadcrumbs -->
	<!--  Sidebar -->
	<?php get_sidebar(); ?>
	<!-- End Sidebar -->
	<!-- Content -->
	<div id="content">
		<!-- Project -->
		<div class="project">
			<div class="slider">
				<ul class="slides">
					<?php
					$desc = get_meta('_project_description');
					$args = array(
						'post_type' => 'attachment',
						'numberposts' => -1,
						'post_status' => null,
						'post_parent' => get_the_ID(),
					);
					//var_dump($attachments);
					$attachments = get_posts($args);
					foreach ($attachments as $attachment) {
						echo '<li>' . wp_get_attachment_image($attachment->ID, $size='slide', $icon = false) . '</li>';
						?>
							
						<?php
					}
					wp_reset_query();
					?>
				</ul>
				<?php echo '<p class="titleportfolio">' .  get_the_title() . '<span>' . $desc . '</span></p>'; ?>
			</div>
		</div>
		<!-- End Project -->
		<!-- Three Cols -->
			<?php the_content(); ?>
		<!-- End Three Cols -->
	</div>
	<!-- End Content -->
	<div class="cl">&nbsp;</div>
<?php get_footer(); ?>