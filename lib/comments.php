<?php
# 
# Renders a single comments; Called for each comment
#
function theme_render_comment($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
		<div id="comment-<?php comment_ID(); ?>" class="commentinside">
		    <div class="image">
		     <?php echo get_avatar($comment, 48); ?>
		    </div>
		    <div class="info">
		    	<span class="name">
		        <?php comment_author_link() ?>
		        says 
		        <a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
		    		<?php comment_date() ?> <?php comment_time() ?>
		    	</a>
		        </span>
		        <?php comment_text() ?>
		        <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
		    </div>
		
		    <div class="comment-meta">

		    	<?php edit_comment_link(__('(Edit)'),'  ','') ?>
	    	</div>
		</div>
	<?php
}

# 
# Restricts direct access to the comments.php and checks whether the comments are password protected.
# 
function theme_comments_restrict_access() {
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');
	
	if ( post_password_required() ) {
		echo '<p class="nocomments">This post is password protected. Enter the password to view comments.</p>';
		return false;
	}
	return true;
}

# 
# Renders all current comments
# 
function theme_comments_render_list($callback) {
	?>
	<?php if ( have_comments() ) : ?>
		<div class="cl">&nbsp;</div>
		<h3><?php comments_number('No Responses', 'One Response', '% Responses' );?></h3>
		<ol class="all-comments">
			<?php wp_list_comments('callback=' . $callback); ?>
		</ol>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="alignleft"><?php previous_comments_link() ?></div>
				<div class="alignright"><?php next_comments_link() ?></div>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<?php if ( comments_open() ) : ?>
	        <!-- If comments are open, but there are no comments. -->
		 <?php else : // comments are closed ?>
			<p class="nocomments">Comments are closed.</p>
		<?php endif; ?>
	<?php endif; ?>
	<?php
}

#
# Comment form hooks:
#	comment_form_before
#	comment_form_must_log_in_after
#	comment_form_top
#	comment_form_logged_in_after
#	comment_notes_before
#	comment_form_before_fields
#	comment_form_field_{$name} (a filter on each and every field, where {$name} is the key name of the field in the array)
#	comment_form_after_fields
#	comment_form_field_comment (a filter on the “comment_field” default setting, which contains the textarea for the comment)
#	comment_form (action hook after the textarea, for backward compatibility mainly)
#	comment_form_after
# 	comment_form_comments_closed
#
# Comment form arguments:
#	'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
#	'comment_field'        => '<p class="comment-form-comment">...',
#	'must_log_in'          => '<p class="must-log-in">...',
#	'logged_in_as'         => '<p class="logged-in-as">...',
#	'comment_notes_before' => '<p class="comment-notes">...',
#	'comment_notes_after'  => '<dl class="form-allowed-tags">...',
#	'id_form'              => 'commentform',
#	'id_submit'            => 'submit',
#	'title_reply'          => __( 'Leave a Reply' ),
#	'title_reply_to'       => __( 'Leave a Reply to %s' ),
#	'cancel_reply_link'    => __( 'Cancel reply' ),
#	'label_submit'         => __( 'Post Comment' ),
#
# Reference: http://codex.wordpress.org/Function_Reference/comment_form
#

function theme_comments_render_form($arguments) {
	comment_form($arguments);
	return false;

	// OLD (left for reference; should be removed in the next code iteration):
	/*
	<?php if ( comments_open() ) : ?>
		<div id="respond">
			<h3>Leave a Reply</h3>
			
			<div class="cancel-comment-reply">
				<small><?php cancel_comment_reply_link(); ?></small>
			</div>
			
			<?php if ( get_option('comment_registration') && !is_user_logged_in() ) : ?>
				<p>You must be <a href="<?php echo wp_login_url( get_permalink() ); ?>">logged in</a> to post a comment.</p>
			<?php else : ?>
				<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
					<?php if ( is_user_logged_in() ) : ?>
						<p>
							Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. 
							<a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account">Log out &raquo;</a>
						</p>
					<?php else : ?>
						<label for="author">Name <?php if ($req) echo "(required)"; ?></label>
						<input type="text" name="author" id="author" value="<?php echo esc_attr($comment_author); ?>" class="field" tabindex="1" <?php if ($req) echo "aria-required='true'"; ?> />
						<div class="cl">&nbsp;</div>
						
						<label for="email">Mail (will not be published) <?php if ($req) echo "(required)"; ?></label>
						<input type="text" name="email" id="email" value="<?php echo esc_attr($comment_author_email); ?>" class="field" tabindex="2" <?php if ($req) echo "aria-required='true'"; ?> />
						<div class="cl">&nbsp;</div>
						
						<label for="url">Website</label>
						<input type="text" name="url" id="url" value="<?php echo esc_attr($comment_author_url); ?>" class="field" tabindex="3" />
						<div class="cl">&nbsp;</div>
					<?php endif; ?>
					
					<label for="comment">Comment</label>
					<textarea name="comment" id="comment" cols="40" rows="10" tabindex="4" class="field"></textarea>
				
	                <input name="submit" type="submit" id="submit" tabindex="5" value="Submit Comment" />

					<?php comment_id_fields(); ?>
					<?php do_action('comment_form', $post->ID); ?>
				</form>
			<?php endif; // If registration required and not logged in ?>
		</div>
	<?php endif; ?>
	<?php
	*/
}
?>