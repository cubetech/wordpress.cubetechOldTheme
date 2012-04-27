<?php 
get_header();
	the_post(); ?>
	<?php cc_breadcrumbs(); ?>
	<!-- End Breadcrumbs -->
	<!--  Sidebar -->
	<?php get_sidebar(); ?>
	<!-- End Sidebar -->
	<!-- Content -->
			<div id="content">
				<?php the_content(); ?>
			</div>
			<!-- End Content -->
			<div class="cl">&nbsp;</div>
			<!-- Two Cols -->
			<?php 
			$left_text = get_meta('_left_bototm_section');
			$right_text = get_meta('_right_bototm_section');  
			if($right_text || $left_text) :
				?>
				<div class="two-cols">
					<ul>
						<?php if($left_text) : ?>
						    <li class="left">
						    	<?php echo $left_text; ?>	
						    </li>
						    <?php endif; ?>
						<?php if($right_text) : ?>
							<li class="right">
								<?php echo $right_text; ?>
							</li>
					   	<?php endif; ?>
					</ul>
					<div class="cl">&nbsp;</div>
				</div>
				<?php 
			endif;
			?>
			<!-- End Two Cols -->
<?php get_footer(); ?>