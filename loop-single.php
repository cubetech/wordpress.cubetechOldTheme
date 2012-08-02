<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="post">
		<h3><?php the_title(); ?></h3>
		<span class="writer"><?php the_author() ?> am <?php the_time('j.F Y') ?><?php comments_popup_link(__('No Comments'), __('1 Kommentar'), __('% Kommentare')); ?></span>
		<?php 
		if(has_post_thumbnail()) {
			the_post_thumbnail('big-thumb');
		}
		the_content(); ?>
		<div class="cl">&nbsp;</div>
		<p class="tags">
				Dieser Beitrag wurde von <?php the_author() ?> 
				unter <?php the_category(', ') ?> ver&ouml;ffentlicht 
				und mit <?php the_tags(''. ', ') ?> verschlagwortet. 
				Es gibt auch ein 
				<a href="<?php the_permalink(); ?>" title="permalink">permanenter Link</a> 
				zu diesem Beitrag.
				Sie können diesem Beitrag <?php post_comments_feed_link('mit dem RSS Feed'); ?> folgen.

				<?php if ( comments_open() && pings_open() ) { ?>
					Auch <a href="#respond">eine Antwort zu hinterlassen</a> oder <a href="<?php trackback_url(); ?>" rel="trackback">ein Trackback</a> von Ihrer Seite zu erstellen ist m&ouml;glich.
				<?php } elseif ( !comments_open() && pings_open() ) { ?>
					Die Antwortmöglichkeit ist geschlossen aber Sie können <a href="<?php trackback_url(); ?> " rel="trackback">ein Trackback</a> von Ihrer Seite erstellen.
				<?php } elseif ( comments_open() && !pings_open() ) { ?>
					Auch <a href="#respond">eine Antwort zu hinterlassen</a> ist m&oouml;glich.
				<?php } elseif ( !comments_open() && !pings_open() ) { ?>
					Antwortmöglichkeit und Trackbacks sind geschlossen.
				<?php } ?>
		</p><!-- /p.postmetadata -->
		<?php comments_template(); ?>
	</div><!-- /div.entry -->
<?php endwhile; ?>
<?php endif; ?>
