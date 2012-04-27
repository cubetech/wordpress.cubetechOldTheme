function getRandomInt(min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min;
}

(function($) {
	
	function EcfUploadObject () {
		this.swfu = null;
		this.entropy = getRandomInt(10000, 100000).toString()
	}
	EcfUploadObject.prototype.constructor = EcfUploadObject;

	window.EcfUploadObject = EcfUploadObject;

	jQuery(document).ready(function() {
		/* SWF Upload related */

		var swf_loaded = false;

		function fileQueued (file) {
			this.customSettings.ecfUpload.onFileSelect(file);
		}
		function fileQueueError(file, errorCode, message) {
			alert('There was a problem with your upload. Please check your file size and try again.');
		}
		function uploadStart(file) {
			this.customSettings.ecfUpload.onUploadStart();
		}
		function uploadSuccess(file, serverData) {
			this.customSettings.ecfUpload.onUploadSuccess(file, serverData);
		}
		function uploadProgress(file, bytesLoaded, bytesTotal) {
			this.customSettings.ecfUpload.onUploadProgress(file, bytesLoaded, bytesTotal);
		}
		function uploadError(file, errorCode, message) {
			this.setButtonDisabled(false);
			$('#' + this.field_wrap_id).find('.swf-upload-temp-filename').hide();
			$('#' + this.field_wrap_id).find('.swf-upload-progress').addClass('swf-upload-progress-error');
			$('#' + this.field_wrap_id).find('.swf-upload-progress-wrap').stop(true, true).fadeTo(2000, 0);
			alert('There was a problem with your upload. Please check your file size and try again.');
		}
		function fileDialogComplete(numFilesSelected, numFilesQueued) {
			// Update file name field
			// this.customSettings.ecfUpload
			if ( numFilesSelected == 0 && numFilesQueued == 0) {
				this.customSettings.ecfUpload.onFileSelect(null);
			};
		}
		function swfUploaded () {
			swf_loaded = true;
		}
		function uploadComplete(file) {
			// do nothing
		}

		window.swfUploadInit = function (span_id, swf_file_post_name, extensions) {
			var swfu = new SWFUpload({
				file_post_name : swf_file_post_name,
				upload_url : swf_upload_url,
				flash_url : swf_flash_url,
				button_placeholder_id : span_id,
				button_image_url: swf_upload_files_url + "upload.png",
				button_text_style: ".swf-upload-button-text { text-align: center; color: #FFFFFF; font-weight: bold; font-size: 11px; font-family: 'Lucida Grande', Verdana, Arial; }",
				button_width: "98",
				button_height: "23",
				button_text: '<span class="swf-upload-button-text">Upload</span>',
				button_text_top_padding: 2,
				button_cursor : SWFUpload.CURSOR.HAND,
				button_action : SWFUpload.BUTTON_ACTION.SELECT_FILES,
				// file_size_limit : window.file_size_limit,
				file_types : extensions,
				file_types_description : "Allowed Files",
				file_upload_limit : 0,
				file_queue_limit : 0,
				custom_settings : {
					ecfUpload: new EcfUploadObject()
				},
				debug: false,

				file_queued_handler : fileQueued,
				file_queue_error_handler : fileQueueError,
				file_dialog_complete_handler : fileDialogComplete,
				upload_start_handler : uploadStart,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : uploadSuccess,
				upload_complete_handler : uploadComplete,
				swfupload_loaded_handler: swfUploaded
			});

			swfu.customSettings.ecfUpload.onFileSelect = function (file) {
				if (file) {
					if (file.name) {
						swfu.startUpload();
						swfu.setButtonDisabled(true);
						$('#' + swfu.field_wrap_id).find('.swf-upload-progress').stop().removeClass('swf-upload-progress-error').removeClass('swf-upload-progress-success');
						$('#' + swfu.field_wrap_id).find('.swf-upload-progress-wrap').stop(true, true).show().css('opacity', 0).fadeTo(300, 1);
						var short_name = (file.name.length > 20) ? file.name.substr(0, 17) + '...' : file.name;
						$('#' + swfu.field_wrap_id).find('.swf-upload-temp-filename').show().html(short_name);
					}
				}
			};

			swfu.customSettings.ecfUpload.onUploadStart = function () {
				swfu.addPostParam('entropy', swfu.customSettings.ecfUpload.entropy);
			};

			swfu.customSettings.ecfUpload.onUploadProgress = function (file, bytesLoaded, bytesTotal) {
				var fraction = (bytesLoaded / bytesTotal);

				$('#' + swfu.field_wrap_id).find('.swf-upload-progress').stop().css('opacity', 1);
				$('#' + swfu.field_wrap_id).find('.swf-upload-progress').css('width', Math.ceil(fraction * 100).toString() + '%');
			};

			swfu.customSettings.ecfUpload.onUploadSuccess = function (file, serverData) {
				swfu.setButtonDisabled(false);
				if (serverData.toString().toLowerCase().substr(0, 3) != 'ok:') {
					$('#' + swfu.field_wrap_id).find('.swf-upload-progress').addClass('swf-upload-progress-error');
					alert(serverData.toString());
				} else {
					$('#' + swfu.field_wrap_id).find('.swf-upload-progress').addClass('swf-upload-progress-success');
					var filename = serverData.toString().toLowerCase().substr(3);
					$('#' + swfu.field_wrap_id).find('input[type="hidden"]').val(filename);
				}
				$('#' + swfu.field_wrap_id).find('.swf-upload-progress-wrap').stop(true, true).fadeTo(2000, 0);
			};

			return swfu;
		}
	});

})(jQuery)