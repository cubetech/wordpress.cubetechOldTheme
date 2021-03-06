<?php
if (theme_comments_restrict_access()) {
	theme_comments_render_form(array(
		'title_reply'=>__('Hinterlasse eine Nachricht'),
		'fields' =>  array(
			'author' => '<div class="fields"><label class="label-hide" for="author">Search</label><input alt="Author" id="author" title="Name *" name="author" class="field" type="text" value="Name *" size="30"' . $aria_req . ' />',
			'email'  => '<label class="label-hide" for="email">Email</label><input alt="Mailadresse " class="field" title="Mailadresse *" id="email" name="email" type="text" value="Mailadresse *" size="30"' . $aria_req . ' />',
			'url'    => '<label class="label-hide" for="url">Search</label><input alt="Website" class="field" title="Website" id="url" name="url" type="text" value="Website" size="30" /></div>',
		),
		'comment_field' => '<label class="label-hide" for="comment">Search</label><textarea class="field" title="Nachricht *" id="comment" name="comment" cols="45" rows="8" >Nachricht *</textarea>',
		'comment_notes_before' => '',
		'comment_notes_after' => '',
	));
	theme_comments_render_list('theme_render_comment');
}
?>
