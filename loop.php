<?php if (have_posts()) : $i = 0; ?>
	<?php while (have_posts()) : the_post(); ?>
		<?php $i++; if($i == 1) : ?>
		<div class="post loop">
			<?php 
			if(has_post_thumbnail()) {
				the_post_thumbnail('featured-thumb');
			}
			?>
		<?php else : ?>
			<div class="post loop">
			<?php 
			if(has_post_thumbnail()) {
				the_post_thumbnail('small-thumb');
			}
			endif;
			?>
			<h3><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h3>
			<span class="writer"><?php the_author() ?> am <?php the_time('j.F Y') ?> <?php comments_popup_link(__('No Comments'), __('1 Comment'), __('% Comments')); ?></span>
			<?php if($i==1): the_content(); else: $excerpt = get_the_content(); echo string_limit_words($excerpt,40); endif; ?>
			<br /><a href="<?php echo get_permalink(); ?>">Mehr lesen...</a>
			<div class="cl">&nbsp;</div>
		</div>
	<?php endwhile; ?>

	<?php if (  $wp_query->max_num_pages > 1 ) : ?>
		<div class="navigation">
			<div class="alignleft"><?php previous_posts_link(__('Older Entries &raquo;')); ?></div>
			<div class="alignright"><?php next_posts_link(__('Next Entries &laquo;')); ?></div>
		</div>
	<?php endif; ?>
	
<?php else : ?>
	<div id="post-0" class="post error404 not-found">
		<h2>Not Found</h2>
		
		<div class="entry">
			<?php  
				if ( is_category() ) { // If this is a category archive
					printf("<p>Sorry, but there aren't any posts in the %s category yet.</p>", single_cat_title('',false));
				} else if ( is_date() ) { // If this is a date archive
					echo("<p>Sorry, but there aren't any posts with this date.</p>");
				} else if ( is_author() ) { // If this is a category archive
					$userdata = get_userdatabylogin(get_query_var('author_name'));
					printf("<p>Sorry, but there aren't any posts by %s yet.</p>", $userdata->display_name);
				} else if ( is_search() ) {
					echo("<p>No posts found. Try a different search?</p>");
				} else {
					echo("<p>No posts found.</p>");
				}
			?>
			<?php get_search_form(); ?>
		</div>
	</div>
<?php endif; ?>
