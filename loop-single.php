<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="post">
		<h3><?php the_title(); ?></h3>
		<span class="writer"><a href="#"><?php the_author() ?></a> in <?php the_time('j.F Y') ?><?php comments_popup_link(__('No Comments'), __('1 Comment'), __('% Comments')); ?></span>
		<?php 
		if(has_post_thumbnail()) {
			the_post_thumbnail('big-thumb');
		}
		the_content(); ?>
		<div class="cl">&nbsp;</div>
		<p class="tags">
				This entry was posted
				on <?php the_time('l, F jS, Y') ?> at <?php the_time() ?>
				and is filed under <?php the_category(', ') ?>.
				You can follow any responses to this entry through the <?php post_comments_feed_link('RSS 2.0'); ?> feed.

				<?php if ( comments_open() && pings_open() ) { ?>
					You can <a href="#respond">leave a response</a>, or <a href="<?php trackback_url(); ?>" rel="trackback">trackback</a> from your own site.
				<?php } elseif ( !comments_open() && pings_open() ) { ?>
					Responses are currently closed, but you can <a href="<?php trackback_url(); ?> " rel="trackback">trackback</a> from your own site.
				<?php } elseif ( comments_open() && !pings_open() ) { ?>
					You can skip to the end and leave a response. Pinging is currently not allowed.
				<?php } elseif ( !comments_open() && !pings_open() ) { ?>
					Both comments and pings are currently closed.
				<?php } ?>
		</p><!-- /p.postmetadata -->
		<?php comments_template(); ?>
	</div><!-- /div.entry -->
<?php endwhile; ?>
<?php endif; ?>