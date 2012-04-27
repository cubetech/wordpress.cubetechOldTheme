<?php
$upload_dir = wp_upload_dir();
$upload_url = $upload_dir['baseurl'] . '/';
$button_id = 'swf-upload-button-' . $this->name;
$field_name = $this->name . '_swf_upload_value';
$extensions = '';
if ($this->allowed_extensions) {
	$extensions = '*' . implode(';*', $this->allowed_extensions);
} else {
	$extensions = '*.*';
}
?>
<script type="text/javascript" charset="utf-8">
    var swf_upload_url = "<?php echo add_query_arg('ecf-upload-service', ''); ?>";
    var swf_upload_files_url = "<?php bloginfo('template_directory'); ?>/lib/enhanced-custom-fields/tpls/swfupload/";
    var swf_flash_url = "<?php bloginfo('template_directory'); ?>/lib/enhanced-custom-fields/tpls/swfupload/Flash/swfupload.swf";
    var swf_assets_url = "<?php bloginfo('template_directory'); ?>/lib/enhanced-custom-fields/tpls/swfupload";
    var uploads_url = "<?php echo $upload_url; ?>";
</script>
<script type="text/javascript" charset="utf-8">
(function($){
	$(document).ready(function(){
		if (swfobject.getFlashPlayerVersion().major == 0) {
			if (typeof window.ecf_swf_upload_script_loaded == 'undefined') {
				$('.ecf-upload-field-flash').each(function() {
					var parent = $(this).closest('.ecf-upload-field-wrap');
					$(parent).find('.ecf-upload-field-flash').hide();
					$(parent).find('.ecf-upload-field-browser').show();
				});
			}
		} else {
			swfu = swfUploadInit("<?php echo $button_id; ?>", "<?php echo $this->name; ?>", "<?php echo $extensions ?>");
			swfu['field_wrap_id'] = "<?php echo $this->name; ?>-ecf-wrap";
			if (typeof window.ecf_swf_upload_script_loaded == 'undefined') {
				$('.ecf-upload-field-flash').each(function() {
					var parent = $(this).closest('.ecf-upload-field-wrap');
					$(parent).find('.ecf-upload-field-flash').show();
					$(parent).find('.ecf-upload-field-browser').hide();
				});
			};
		}
		window.ecf_swf_upload_script_loaded = true;
    });
})(jQuery)
</script>
<style type="text/css">
	
</style>

<div class="ecf-upload-field-flash">
	<div class="swf-upload-button-wrap">
		<span id="<?php echo $button_id; ?>"></span>
	</div>
	<div class="swf-upload-temp-filename"></div>
	<div class="swf-upload-progress-wrap"><span class="swf-upload-progress"></span></div>
	<div class="cl">&nbsp;</div>
	<input type="hidden" name="<?php echo $field_name ?>" value="" />
</div>