	<?php while (have_posts()) : the_post(); ?>
		<div class="post loop">
			<?php 
			if(has_post_thumbnail()) {
				the_post_thumbnail('featured-thumb');
			}
			?>
			<h3><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h3>
			<?php the_content(''); ?>
			<div class="cl">&nbsp;</div>
		</div>
	<?php endwhile; ?>

	<?php if (  $wp_query->max_num_pages > 1 ) : ?>
		<div class="navigation">
			<div class="alignleft"><?php previous_posts_link(__('Older Entries &raquo;')); ?></div>
			<div class="alignright"><?php next_posts_link(__('Next Entries &laquo;')); ?></div>
		</div>
	<?php endif; ?>