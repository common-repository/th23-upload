jQuery(document).ready(function($){

	// handle changes of screen options
	$('#th23-upload-screen-options input').change(function() {
		var data = {
			action: 'th23_upload_screen_options',
			nonce: $('#th23-upload-screen-options-nonce').val(),
		};
		// add screen option fields to data dynamically
		$('#th23-upload-screen-options input').each(function() {
			if($(this).attr('type') == 'checkbox') {
				var value = $(this).is(':checked');
			}
			else {
				var value = $(this).val();
			}
			if(typeof $(this).attr('name') != 'undefined') {
				data[$(this).attr('name')] = value;
			}
		});
		// saving user preference
		$.post(ajaxurl, data, function() {});
		// change live classes
		var classBase = $(this).attr('data-class');
		var classAdd = '';
		if($(this).attr('type') == 'checkbox') {
			if($(this).is(':checked')) {
				classAdd = classBase;
			}
		}
		else {
			classAdd = classBase + '-' + $(this).val().split(' ').join('_');
		}
		$("#th23-upload-options").removeClass(function(index, className) {
			var regex = new RegExp('(^|\\s)' + classBase + '.*?(\\s|$)', 'g');
			return (className.match(regex) || []).join(' ');
		}).addClass(classAdd);
	});

	// handle show/hide of children options (up to 2 child levels deep)
	$('input[data-childs]').change(function() {
		if($(this).attr('checked') == 'checked') {
			// loop through childs as selectors, for all that contain inputs with data-childs attribute, show this childs, if parent input is checked - and finally show ourselves as well
			$($(this).attr('data-childs')).each(function() {
				if($('input[data-childs]', this).attr('checked')) {
					$($('input[data-childs]', this).attr('data-childs')).show();
				}
			}).show();
		}
		else {
			// loop through childs as selectors, for all that contain inputs with data-childs attribute, hide this childs - and finally ourselves as well
			$($(this).attr('data-childs')).each(function() {
				$($('input[data-childs]', this).attr('data-childs')).hide();
			}).hide();
		}
	});

	// remove any "disabled" attributes from settings before submitting - to fetch/ perserve values
	$('.th23-upload-options-submit').click(function() {
		$('input[name="th23-upload-options-do"]').val('submit');
		$('#th23-upload-options :input').removeProp('disabled');
		$('#th23-upload-options').submit();
	});

	// toggle show / hide eg for placeholder details in description
	$('.toggle-switch').click(function(e) {
		$(this).blur().next('.toggle-show-hide').toggle();
		e.preventDefault();
	});

	// handle professional extension upload
	$('#th23-upload-pro-file').on('change', function(e) {
		$('#th23-upload-options-submit').click();
	});

	// == customization: from here on plugin specific ==

	// Media Library: Add / remove watermark for an attachment
	var locked = [];
	$('a.th23-upload-admin-watermark').click(function(e) {

		e.preventDefault();
		e.stopPropagation();
		$(this).blur();

		var attachment_id = $(this).attr('data-attachment');

		// avoid multiple clicks before one execution is finished
		if(locked.indexOf(attachment_id) !== -1) {
			return;
		}
		locked.push(attachment_id);

		// keep attachment actions visible during execution
		var row_actions = $(this).closest('.row-actions');
		row_actions.css({ 'position': 'initial' });

		// show waiting text
		$(this).html('<span class="blinking">' + $(this).attr('data-wait') + '</span>');

		var data = {
			action: 'th23_upload_watermark',
			nonce: $(this).attr('data-nonce'),
			id: attachment_id,
			do: $(this).attr('data-do'),
		};
		// make $(this) accessible upon response
		var item = $(this);
		$.post(ajaxurl, data, function(r) {
			if(r.result == 'success') {
				// show success message
				item.html('<span class="success">' + r.msg + '</span>');
				// 3 seconds after success, reset attachment actions visibility, change action link, unlock to be used again
				setTimeout(function(){
					row_actions.css({ 'position': '' });
					item.attr('data-do', r.do);
					item.attr('data-wait', r.wait);
					item.html(r.html);
					var pos = locked.indexOf(attachment_id);
					if(pos !== -1) locked.splice(pos, 1);
				}, 3000);
			}
			else {
				// show error
				item.html('<span class="error"><span class="blinking">' + r.msg + '</span></span>');
			}
		});

	});

	// Plugin Settings: Handle watermark image selection
	// click on watermark image label in options page - open image selection, not highlight (hidden) input field
	$('label[for="input_watermarks_image"]').click(function(e) {
		e.preventDefault();
		$('#th23-upload-watermark-image').click();
	});
	// toggle show / hide watermark image selection
	$('#th23-upload-watermark-image').click(function() {
		$('.th23-upload-watermark-selection').toggle();
		if($('.th23-upload-watermark-selection').is(':visible')) {
			$('html, body').animate({ scrollTop: ($('#th23-upload-watermark-image').offset().top - 50) }, 300);
		}
	});
	// select watermark image from list - note: on-click ensures dynamically generated elements (uploaded watermarks) get the event
	$('.th23-upload-watermark-selection').on('click', '.th23-upload-watermark-select', function(e){
		$('.th23-upload-watermark-placeholder').hide();
		$('#th23-upload-watermark-image img').remove();
		$('#th23-upload-watermark-image').append('<img src="' + $('#th23-upload-watermark-baseurl').val() + $(this).attr('data-file') + '" />');
		$('#input_watermarks_image').val($(this).attr('data-file'));
		$('.th23-upload-watermark-unavailable').remove();
		$('.th23-upload-watermark-selection').toggle();
	});
	// add watermark image to list and select uploaded image
	$('#th23-upload-watermark-file').on('change', function(e) {
		var form_data = new FormData();
		form_data.append('action', 'th23_upload_watermark_settings');
		form_data.append('nonce', $('#th23-upload-watermark-nonce').val());
		form_data.append('file', $(this).prop('files')[0]);
		form_data.append('do', 'upload');
		$.ajax({
			url: ajaxurl,
			type: 'post',
			contentType: false,
			processData: false,
			data: form_data,
			success: function(r) {
				if(r.result == 'success') {
					// make newly uploaded the selected watermark
					$('.th23-upload-watermark-placeholder').hide();
					$('#th23-upload-watermark-image img').remove();
					$('#th23-upload-watermark-image').append('<img src="' + $('#th23-upload-watermark-baseurl').val() + r.item + '" />');
					$('#input_watermarks_image').val(r.item);
					$('.th23-upload-watermark-unavailable').remove();
					$('.th23-upload-watermark-selection').toggle();
					// remove previous watermark with same filename from selection list
					if(r.replace) {
						$('.th23-upload-watermark-select[data-file="' + r.item + '"]').remove();
					}
					// add newly uploaded watermark to the beginning of the selection list / after upload entry
					$('.th23-upload-watermark-selection span').first().after(r.html);
				}
				else {
					var parent = $('.th23-upload-watermark-selection span').first();
					$('.message', parent).remove();
					parent.append('<span class="message error">' + r.msg + '</span>');
				}
			},
		});
		// clear file upload input field
		$("#th23-upload-watermark-file").val('');
	});
	// delete watermark image from list, remove deleted if selected - note: on-click ensures dynamically generated elements (uploaded watermarks) get the event
	$('.th23-upload-watermark-selection').on('click', '.th23-upload-watermark-delete', function(e){
		e.stopPropagation();
		// make file accessible upon response
		var file = $(this).attr('data-file');
		var data = {
			action: 'th23_upload_watermark_settings',
			nonce: $('#th23-upload-watermark-nonce').val(),
			file: file,
			do: 'delete',
		};
		// make $(this) accessible upon response
		var item = $(this);
		$.post(ajaxurl, data, function(r) {
			var parent = item.closest('.th23-upload-watermark-select');
			if(r.result == 'success') {
				parent.remove();
				if($('#input_watermarks_image').val() == file) {
					$('#th23-upload-watermark-image img').remove();
					$('.th23-upload-watermark-placeholder').show();
					$('#input_watermarks_image').val('');
				}
			}
			else {
				$('.message', parent).remove();
				parent.append('<span class="message error">' + r.msg + '</span>');
			}
		});
	});

	// Plugin Settings: Mass actions for add/remove watermark to all attachments
	var attachments, len, action, nonce, i, stop;
	// trigger ajax call
	function trigger_ajax() {
		if(i < len && stop != 1) {
			// call ajax
			var data = {
				action: 'th23_upload_watermark',
				nonce: nonce,
				id: attachments[i],
				do: action,
			};
			$.post(ajaxurl, data, function(r) {
				// update progress bar
				var prog = (i + 1) / len * 100;
				$('#th23-upload-mass-bar > div').css({ "width": prog + "%" });
				// add attachment to done list
				$('#th23-upload-mass-last').prepend('<div>' + r.item + '<div><span class="' + r.result + '">' + r.msg + '</span></div></div>');
				i++;
				trigger_ajax();
			});
		}
		else {
			// hide "stop" / unhide "close"
			$('#th23-upload-mass-stop').css({ 'display': 'none' });
			$('#th23-upload-mass-close').css({ 'display': 'inline' });
		}
	}
	// start mass action
	$('.th23-upload-mass-action').click(function() {

		$(this).blur();

		// check for confirmation
		$('.th23-upload-mass-confirm').removeClass('blinking temp');
		if(!$('#th23-upload-mass-confirm').is(":checked")) {
			// note: assess .width() to trigger animation restart properly upon subsequent clicks
			$('.th23-upload-mass-confirm').width();
			$('.th23-upload-mass-confirm').addClass('blinking temp');
			return;
		}
		$('#th23-upload-mass-confirm').prop('checked', false);

		// get attachment ids
		attachments = $('#th23-upload-attachments').val().split(",");
		len = attachments.length;

		// start mass action
		$('#th23-upload-mass-trigger').css({ 'display': 'none' });
		$('#th23-upload-mass-progress').css({ 'display': 'block' });
		$('#th23-upload-mass-stop').css({ 'display': 'inline' });
		action = $(this).attr('data-action');
		nonce = $(this).attr('data-nonce');
		i = 0;
		stop = 0;
		trigger_ajax();

	});
	// stop button
	$('#th23-upload-mass-stop').click(function() {
		stop = 1;
	});
	// close button
	$('#th23-upload-mass-close').click(function() {
		$('#th23-upload-mass-trigger').css({ 'display': 'block' });
		$('#th23-upload-mass-progress').css({ 'display': 'none' });
		$('#th23-upload-mass-bar > div').css({ "width": "1%" });
		$('#th23-upload-mass-last').html('');
		$('#th23-upload-mass-close').css({ 'display': 'none' });
	});

});
