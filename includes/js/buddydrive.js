
function fileDialogStart() {
	jQuery("#media-upload-error").empty();
}

// progress and success handlers for media multi uploads
function fileQueued(fileObj) {
	// Get rid of unused form
	jQuery('.media-blank').remove();
	
	if ( jQuery('#no-buddyitems').length )
		jQuery('#no-buddyitems').remove();

	var items = jQuery('#media-items').children(), postid = 0;

	// Create a progress bar containing the filename
	jQuery('#buddydrive-dir tbody').prepend('<tr><td colspan="5"><div id="media-item-' + fileObj.id + '" class="media-item child-of-' + postid + '"><div class="progress"><div class="percent">0%</div><div class="bar"></div></div><div class="filename original"> ' + fileObj.name + '</div></div></td></tr>');

}

function uploadProgress(up, file) {
	var item = jQuery('#media-item-' + file.id);

	jQuery('.bar', item).width( (200 * file.loaded) / file.size );
	jQuery('.percent', item).html( file.percent + '%' );
}

// check to see if a large file failed to upload
function fileUploading(up, file) {
	var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);

	if ( max > hundredmb && file.size > hundredmb ) {
		setTimeout(function(){
			var done;

			if ( file.status < 3 && file.loaded == 0 ) { // not uploading
				wpFileError(file, pluploadL10n.big_upload_failed.replace('%1$s', '<a class="uploader-html" href="#">').replace('%2$s', '</a>'));
				up.stop(); // stops the whole queue
				up.removeFile(file);
				up.start(); // restart the queue
			}
		}, 10000); // wait for 10 sec. for the file to start uploading
	}
}

function updateMediaForm() {
	
	jQuery('#buddydrive-sharing-details').html('');
	jQuery('#buddyfile-desc').val('');
	jQuery('#buddydrive-sharing-options').val('private');
	jQuery('#buddydrive-sharing-settings').val('private');
	
	jQuery('.next-step').each( function(){
		jQuery(this).show();
	});

	jQuery( '.buddydrive-step' ).addClass( 'hide' );
	jQuery( '#buddydrive-file-uploader' ).addClass( 'hide' );
	
	
	jQuery('#buddydrive-edit-item').html('');
	jQuery('#buddydrive-edit-item').addClass('hide');
	
}

function uploadSuccess(fileObj, serverData) {
	var item = jQuery('#media-item-' + fileObj.id);

	// on success serverData should be numeric, fix bug in html4 runtime returning the serverData wrapped in a <pre> tag
	serverData = serverData.replace(/^<pre>(\d+)<\/pre>$/, '$1');

	// if async-upload returned an error message, place it in the media item div and return
	if ( serverData.match(/media-upload-error|error-div/) ) {
		item.html(serverData);
		return;
	} else {
		jQuery('.percent', item).html( pluploadL10n.crunching );
	}

	prepareMediaItem( fileObj, serverData );
	updateMediaForm();
}


function prepareMediaItem(fileObj, serverData ) {
	
	parenttr = jQuery('#media-item-' + fileObj.id).parent().parent();

	var data = {
      action:'buddydrive_fetchfile',
      createdid:serverData
    };

	jQuery.post(ajaxurl, data, function(response) {
        parenttr.html( response );
    });

	parenttr.attr({
	  'id': 'item-' + serverData,
	  'class': 'latest'
	});
	
	if( jQuery('#no-buddyitems').length )
		jQuery('#no-buddyitems').remove();

	updateBuddyQuota();
}

function updateBuddyQuota() {
	var data = {
      action:'buddydrive_updatequota'
    };

	jQuery.post(ajaxurl, data, function(response) {
        jQuery('#buddy-quota').html(response);
    });

	return;
}


// generic error message
function wpQueueError(message) {
	jQuery('#media-upload-error').show().html( '<div class="error"><p>' + message + '</p></div>' );
}

// file-specific error messages
function wpFileError(fileObj, message) {
	itemAjaxError(fileObj.id, message);
}

function itemAjaxError(id, message) {
	var item = jQuery('#media-item-' + id), filename = item.find('.filename').text(), last_err = item.data('last-err');

	if ( last_err == id ) // prevent firing an error for the same file twice
		return;

	item.html('<div class="error-div">'
				+ '<a class="dismiss" href="#">' + pluploadL10n.dismiss + '</a>'
				+ '<strong>' + pluploadL10n.error_uploading.replace('%s', jQuery.trim(filename)) + '</strong> '
				+ message
				+ '</div>').data('last-err', id);
}


function dndHelper(s) {
	var d = document.getElementById('dnd-helper');

	if ( s ) {
		d.style.display = 'block';
	} else {
		d.style.display = 'none';
	}
}

function uploadError(fileObj, errorCode, message, uploader) {
	var hundredmb = 100 * 1024 * 1024, max;

	switch (errorCode) {
		case plupload.FAILED:
			wpFileError(fileObj, pluploadL10n.upload_failed);
			break;
		case plupload.FILE_EXTENSION_ERROR:
			wpFileError(fileObj, pluploadL10n.invalid_filetype);
			break;
		case plupload.FILE_SIZE_ERROR:
			uploadSizeError(uploader, fileObj);
			break;
		case plupload.IMAGE_FORMAT_ERROR:
			wpFileError(fileObj, pluploadL10n.not_an_image);
			break;
		case plupload.IMAGE_MEMORY_ERROR:
			wpFileError(fileObj, pluploadL10n.image_memory_exceeded);
			break;
		case plupload.IMAGE_DIMENSIONS_ERROR:
			wpFileError(fileObj, pluploadL10n.image_dimensions_exceeded);
			break;
		case plupload.GENERIC_ERROR:
			wpQueueError(pluploadL10n.upload_failed);
			break;
		case plupload.IO_ERROR:
			max = parseInt(uploader.settings.max_file_size, 10);

			if ( max > hundredmb && fileObj.size > hundredmb )
				wpFileError(fileObj, pluploadL10n.big_upload_failed.replace('%1$s', '<a class="uploader-html" href="#">').replace('%2$s', '</a>'));
			else
				wpQueueError(pluploadL10n.io_error);
			break;
		case plupload.HTTP_ERROR:
			wpQueueError(pluploadL10n.http_error);
			break;
		case plupload.INIT_ERROR:
			jQuery('.media-upload-form').addClass('html-uploader');
			break;
		case plupload.SECURITY_ERROR:
			wpQueueError(pluploadL10n.security_error);
			break;
		default:
			wpFileError(fileObj, pluploadL10n.default_error);
	}
}

function uploadSizeError( up, file, over100mb ) {
	var message;

	if ( over100mb )
		message = pluploadL10n.big_upload_queued.replace('%s', file.name) + ' ' + pluploadL10n.big_upload_failed.replace('%1$s', '<a class="uploader-html" href="#">').replace('%2$s', '</a>');
	else
		message = pluploadL10n.file_exceeds_size_limit.replace('%s', file.name);

	jQuery('#buddydrive-dir tbody').prepend('<tr><td colspan="5"><div id="media-item-' + file.id + '" class="media-item error"><div class="error-div"><a class="dismiss" href="#">' + pluploadL10n.dismiss + '</a> <strong>' + message + '</strong></div></div></td></tr>');
	up.removeFile(file);
}

function buddyDriveListGroups( element ) {
	var data = {
      action:'buddydrive_getgroups'
    };

    jQuery.post(ajaxurl, data, function(response) {
        jQuery(element).html( '<label for="buddygroup">' + pluploadL10n.label_group + '</label>' + response);
    });
}

function buddydriveStripLast() {
	if ( jQuery('#buddydrive-dir tbody').find('.buddydrive-load-more').length )
		jQuery('#buddydrive-dir tbody').find('.buddydrive-load-more').parent().prev().remove();
}

function openFolder( srcstring ) {
	var folder_id = srcstring.replace('?folder-', '');
	var buddyscope = false;

	if ( jQuery('#subnav.item-list-tabs li.current a').length )
		buddyscope = jQuery('#subnav.item-list-tabs li.current a').prop('id');

	folder_id = Number(folder_id) + 0;

	if ( ! isNaN( folder_id ) ) {
		var data = {
      		action:'buddydrive_openfolder',
	  		folder: folder_id,
			foldername:1,
			scope:buddyscope
    	};

    	jQuery('#buddydrive-dir tbody').html('<tr><td colspan="5"><p class="buddydrive-opening-dir"><a class="loading">'+pluploadL10n.loading+'</a></p></td></tr>');

		jQuery.post(ajaxurl, data, function(response) {
			
			jQuery('#buddy-new-folder').hide();

			jQuery('.buddytree').each(function(){
				jQuery(this).removeClass('current');
			});
			
			if ( response.length > 1)
				jQuery('.buddydrive-crumbs').append( ' / <span id="folder-'+folder_id+'" class="buddytree current"><input type="hidden" id="buddydrive-open-folder" value="'+folder_id+'">'+response[1]+'</span>' );
			
			jQuery('#buddydrive-dir tbody').html('');
	        jQuery("#buddydrive-dir tbody").prepend(response[0]);
			
	    }, 'json' );
	}
}


jQuery(document).ready(function($){
	$.cookie( 'buddydrive-oldestpage', 1, {path: '/'} );

	if ( '-1' != window.location.search.indexOf('folder-') )
		openFolder( window.location.search );

	if ( null != $.cookie('buddydrive_filter') && $( '#buddydrive-filter' ).length )
		$('#buddydrive-filter option[value="' + $.cookie('buddydrive_filter') + '"]').prop( 'selected', true );
	
	$('.next-step').each( function(){
		$(this).show();
	});
	
	$('#buddy-new-file').on('click', function( event ){
		event.preventDefault();

		if ( ! $('#buddydrive-folder-editor').hasClass('hide') )
			$('#buddydrive-folder-editor').addClass('hide');
		
		if ( ! $('#buddydrive-edit-item').hasClass('hide') ){
			$('#buddydrive-edit-item').html('');
			$('#buddydrive-edit-item').addClass('hide');
		}
			
		$('#buddydrive-file-uploader').removeClass('hide');
		$('#buddydrive-first-step').removeClass('hide');
		
		return;
	});
	
	$('#buddy-new-folder').on('click', function( event ){
		event.preventDefault();

		if ( ! $('#buddydrive-file-uploader').hasClass('hide') )
			$('#buddydrive-file-uploader').addClass('hide');
			
		if ( ! $('#buddydrive-edit-item').hasClass('hide') ){
			$('#buddydrive-edit-item').html('');
			$('#buddydrive-edit-item').addClass('hide');
		}
			
		updateMediaForm();
			
		$('#buddydrive-folder-editor').removeClass('hide');
		
		return;
	});
	
	$('#buddydrive-sel-all').on('change', function(){
		var status = $(this).prop('checked');
		
		if ( ! status )
			status = false;
		
		$('.buddydrive-item-cb').each( function() {
			$(this).prop('checked', status );
		});
		
		return;
	})

	$('#buddy-delete-item').on('click', function( event ){
		event.preventDefault();

		var itemlist="";
		var count = 0;
		
		if ( !$('#buddydrive-file-uploader').hasClass('hide') )
			$('#buddydrive-file-uploader').addClass('hide');
			
		if ( !$('#buddydrive-folder-editor').hasClass('hide') )
			$('#buddydrive-folder-editor').addClass('hide');
			
		if ( !$('#buddydrive-edit-item').hasClass('hide') ){
			$('#buddydrive-edit-item').html('');
			$('#buddydrive-edit-item').addClass('hide');
		}
		
		$('.buddydrive-item-cb').each(function(){
			if( $(this).prop('checked') ) {
				itemlist += $(this).val()+',';
				count += 1;
			}
				
		});
		
		if ( count == 0 ) {
			alert( pluploadL10n.cbs_message );
			return;
		}
		
		var confirm_message = pluploadL10n.confirm_delete.replace( '%d', count );
		keepon = confirm( confirm_message );
		
		if ( keepon ) {
			var data = {
		      action:'buddydrive_deleteitems',
		      items: itemlist,
		      '_wpnonce_buddydrive_actions': $("input#_wpnonce_buddydrive_actions").val()
		    };

		    $.post(ajaxurl, data, function(response) {
			
				if ( response['result'] == 0 ){
					alert( pluploadL10n.delete_error_message );
					return;
				} else {
					
					for ( i in response['items'] ){
						
						$("#buddydrive-dir tbody #item-"+response['items'][i]).fadeOut(200, function(){
							$(this).remove();
						});
					}
					
				}
			
		        
		    }, 'json' );
		
			updateBuddyQuota();
		
		}

		return;
	});
	
	$('#buddy-edit-item').on('click', function( event ){
		event.preventDefault();

		var count = 0;
		var item;
		
		if ( !$('#buddydrive-file-uploader').hasClass('hide') )
			$('#buddydrive-file-uploader').addClass('hide');
			
		if ( !$('#buddydrive-folder-editor').hasClass('hide') )
			$('#buddydrive-folder-editor').addClass('hide');
		
		$('.buddydrive-item-cb').each(function(){
			if( $(this).prop('checked') ) {
				item = $(this).val();
				count += 1;
			}
				
		});
		
		if ( count != 1 ) {
			alert( pluploadL10n.cb_message );
		} else {
			var data = {
		      action:'buddydrive_editform',
		      buddydrive_item: item
		    };

		    $.post(ajaxurl, data, function(response) {
			
				$( '#buddydrive-edit-item' ).html( response );
				$( '#buddydrive-edit-item, #buddydrive-edit-item div' ).removeClass( 'hide' );
			
		    } );
		}
		
		return;
	});
	
	$('.cancel-step').on('click', function( event ){
		event.preventDefault();

		updateMediaForm();
		return;
	});
	
	$('#buddydrive-forms').on('click', '.cancel-item', function( event ){
		event.preventDefault();

		$('#buddydrive-edit-item').html('');
		$('#buddydrive-edit-item').addClass('hide');
		return;
	});

	get_custom_field = function( type ) {
		var customs = [],
			cvalue = [];

		$('#buddydrive-custom-step-'+ type +' .buddydrive-customs').each( function() {

			if( typeof cvalue[ $(this).prop( 'name' ) ] == 'undefined' )
				cvalue[ $(this).prop( 'name' ) ] = [];

			switch ( $(this).prop( 'type' ) ) {
				case 'checkbox':
				case 'radio' :
					if ( $(this).prop( 'checked' ) && typeof $(this).val() != 'undefined'  )
						cvalue[ $(this).prop( 'name' ) ].push( $(this).val() );
					break;

				default:
					if( typeof $(this).val() != 'undefined' )
						cvalue[ $(this).prop( 'name' ) ].push( $(this).val() );
					break;
					
			}
		
		});

		for ( i in cvalue ) {
			customs.push( {
				'cname': i,
				'cvalue': cvalue[i].length == 1 ? cvalue[i][0] : cvalue[i]
			} );
		}

		return customs;
	}

	$('#buddydrive-forms').on('submit', '#buddydrive-item-edit-form', function( event ){
		event.preventDefault();

		var item_id = $('#buddydrive-item-id').val();
		var item_title = $('#buddydrive-item-title').val();
		var item_content = $('#buddydrive-item-content').val();
		var item_sharing = $('#buddyitem-sharing-options').val();
		var item_folder = $('#folders').val();
		var item_password = false;
		var item_group = false;
		var errors = Array();
		var customs = Array();
		
		switch ( item_sharing ) {
			case 'password' :
				item_password = $('#buddypass').val();
				break;

			case 'groups':
				item_group = $('#buddygroup').val();
				break;
		}

		if ( item_title.length < 1 ){
			errors.push( pluploadL10n.title_needed );
		}

		if ( item_sharing == 'groups' && !item_group ){
			errors.push( pluploadL10n.group_needed );
		}

		if ( item_sharing == 'password' && !item_password ){
			errors.push( pluploadL10n.pwd_needed );
		}

		if ( errors.length >= 1 ) {
			var message = '';
			for( i in errors ) {
				message += errors[i] +"\n";
			}
			alert( message );
			return;
		}

		if ( $( '.buddydrive-customs' ).length ) 
			customs = get_custom_field( 'edit' );

		var data = {
		      action:'buddydrive_updateitem',
		      id: item_id,
		      title: item_title,
		      content: item_content,
		      sharing: item_sharing,
		      folder: item_folder,
		      password: item_password,
		      group: item_group,
		      '_wpnonce_buddydrive_actions': $("input#_wpnonce_buddydrive_actions").val()
		    };

		    if ( customs.length > 0 ) {
		    	data.customs = JSON.stringify( customs );
		    }

		    $.post(ajaxurl, data, function(response) {
				$('#buddydrive-edit-item').html('');
				$('#buddydrive-edit-item').addClass('hide');
				
				if ( response[0] != 0 ){
					currentfolder = 0;
					output = response[0].replace(/<tr[^>]*>/, '');
					output = output.replace(/<\/tr>/, '');

					$('.buddytree').each(function(){
						if( $(this).hasClass('current') )
							currentfolder = $(this).prop('id').replace('folder-', '');
					});

					if ( response[1] === parseInt(currentfolder) ) {
						$('tr#item-'+item_id).html(output);
						$('tr#item-'+item_id).addClass('latest');
					} else {
						$('tr#item-'+item_id).remove();
					}
					
				} else {
					alert('oops');
				}
		    }, 'json' );

		return;
	});
	
	$('.next-step').on('click', function( event ){
		event.preventDefault();

		var parent = $(this).parent().parent().parent();
		
		var nextstep = parent.find('.hide').first();
		
		$(this).hide();
		
		if ( $('#buddydrive-open-folder').length )
			$('#buddydrive-third-step').removeClass('hide');
		else
			nextstep.removeClass('hide');
			
		return;
	});
	
	$('#buddydrive-forms').on('change', 'select', function(){
		
		var id_details, id_settings;
		
		if ( $(this).prop('id') == 'buddygroup' || $(this).prop('id') == 'folders' ){
			return;
		} else if ( $(this).prop('id') == 'buddydrive-sharing-options' ) {
			id_details = '#buddydrive-sharing-details';
			id_settings = '#buddydrive-sharing-settings';
		} else if ( $(this).prop('id') == 'buddyitem-sharing-options' ){
			id_details = '#buddydrive-admin-privacy-detail';
		}else {
			id_details = '#buddyfolder-sharing-details';
			id_settings = '#buddyfolder-sharing-settings';
		}
		
		var sharing_option = $(this).val();
		
		switch ( sharing_option ) {
			case 'password':
				$(id_details).html('<label for="buddypass">'+pluploadL10n.define_pwd+'</label><input type="text" id="buddypass">');
				if ( id_settings )
					$(id_settings).val( sharing_option );
				break;
			case 'groups':
				buddyDriveListGroups( id_details );
				if ( id_settings )
					$(id_settings).val( sharing_option );
				break;
			default:
				$(id_details).html('');
				if ( id_settings )
					$(id_settings).val( sharing_option );
				break;
		}
		
		return;
	});
	
	$('#buddydrive-dir').on('click', '.buddydrive-load-more a', function( event ){
		event.preventDefault();

		var currentfolder = buddyscope = 0;
		var itemlist = '';
		
		$('.buddytree').each(function(){
			if( $(this).hasClass('current') )
				currentfolder = $(this).prop('id').replace('folder-', '');
		});

		if ( $('#subnav.item-list-tabs li.current a').length )
			buddyscope = $('#subnav.item-list-tabs li.current a').prop('id');

		$('tr.latest .buddydrive-item-cb').each( function(){
				itemlist += $(this).val() +',';
		});

		if( itemlist.length >= 2 )
			itemlist = itemlist.substring( 0, itemlist.length - 1 );
		
		var loadmore_tr = $(this).parent().parent();
		
		$(this).addClass('loading');
		
		if ( null == $.cookie('buddydrive-oldestpage') )
	        $.cookie('buddydrive-oldestpage', 1, {path: '/'} );

	    var oldest_page = ( $.cookie('buddydrive-oldestpage') * 1 ) + 1;
		
		var data = {
	      action:'buddydrive_loadmore',
	      page: oldest_page,
		  folder:currentfolder,
		  scope:buddyscope,
		  exclude:itemlist
	    };

	    $.post(ajaxurl, data, function(response) {
	        $.cookie( 'buddydrive-oldestpage', oldest_page, {path: '/'} );
	        $("#buddydrive-dir tbody").append(response);
			loadmore_tr.hide();
	    });
		
		return;
	});
	
	$('#buddydrive-folder-editor-form').on( 'submit', function( event ){
		event.preventDefault();

		var buddygroup, buddyshared, buddypass;
		
		if ( $('#buddydrive-folder-title').val().length < 1 ) {
			alert( pluploadL10n.title_needed );
			return;
		} 
			
		if ( $('#buddyfolder-sharing-settings').val().length > 1 ) {
			buddyshared = $('#buddyfolder-sharing-settings').val();
			
			switch ( buddyshared ) {
				case 'password':
					if( $('#buddypass').val().length < 1 ){
						alert( pluploadL10n.pwd_needed );
						return;
					} else {
						buddypass = $('#buddypass').val();
					}
					break;
				case 'groups':
					buddygroup = $('#buddygroup').val();
					break;
			}
			
			var data = {
		      action:'buddydrive_createfolder',
			  title: $('#buddydrive-folder-title').val(),
		      sharing_option: buddyshared,
			  sharing_pass: buddypass,
			  sharing_group: buddygroup,
			  '_wpnonce_buddydrive_actions': $("input#_wpnonce_buddydrive_actions").val()
		    };
		
			$.post(ajaxurl, data, function(response) {
		        $("#buddydrive-dir tbody").prepend(response);
		        $("#buddydrive-dir tbody tr").first().addClass('latest');
		        if( $('#no-buddyitems').length )
		        	$('#no-buddyitems').remove();
				
				$('.cancel-folder').trigger('click');
		    });
			
			return;
		}
		
		return;
	});
	
	$('.cancel-folder').on('click', function( event ){
		event.preventDefault();

		$('.next-step').each( function(){
			$(this).show();
		});
		
		$('#buddyfolder-second-step').addClass('hide');
		$('#buddydrive-folder-editor').addClass('hide');
		
		jQuery('#buddyfolder-sharing-details').html('');
		jQuery('#buddydrive-folder-title').val('');
		jQuery('#buddyfolder-sharing-options').val('private');
		jQuery('#buddyfolder-sharing-settings').val('private');
		
		return;
	});
	
	$('#buddydrive-dir').on('click', '.buddyfolder', function( event ){
		event.preventDefault();

		var buddyscope;
		
		$.cookie( 'buddydrive-oldestpage', 1, {path: '/'} );
		
		updateMediaForm();

		if ( $('#subnav.item-list-tabs li.current a').length )
			buddyscope = $('#subnav.item-list-tabs li.current a').prop('id');
		
		parent_id = $(this).data( 'folder' );
		$('#buddy-new-folder').hide();

		$('.buddytree').each(function(){
			$(this).removeClass('current');
		});
		
		$('.buddydrive-crumbs').append( ' / <span id="folder-'+parent_id+'" class="buddytree current"><input type="hidden" id="buddydrive-open-folder" value="'+parent_id+'">'+$(this).html()+'</span>' );
		
		var data = {
	      action:'buddydrive_openfolder',
		  folder: parent_id,
		  scope:buddyscope
	    };

	    $('#buddydrive-dir tbody').html('<tr><td colspan="5"><p class="buddydrive-opening-dir"><a class="loading">'+pluploadL10n.loading+'</a></p></td></tr>');
	
		$.post(ajaxurl, data, function(response) {
			$('#buddydrive-dir tbody').html('');
	        $("#buddydrive-dir tbody").prepend(response[0]);
	    }, 'json' );
		
		return;
		
	});

	$('#buddydrive-dir').on('click', '.dismiss', function( event ){
		event.preventDefault();

		$(this).parent().parent().parent().parent().fadeOut(200, function(){
				$(this).remove();
			});
		return;
	});

	$('#buddydrive-dir').on('click', '.buddydrive-row-actions a', function( event ){

		if ( $(this).hasClass('buddydrive-private-message') )
			return true;

		event.preventDefault();

		if ( $(this).hasClass('buddydrive-group-activity') || $(this).hasClass('buddydrive-profile-activity') ) {

			if ( $(this).hasClass('loading') || $(this).hasClass('shared') )
				return;

			target = $(this).parent().parent().parent().parent().find('.buddydrive-item-cb').val();
			link = $(this).parent().parent().parent().find('a').first().prop('href');
			buddytype = $(this).parent().parent().parent().find('a').first().prop('class');
			
			if ( buddytype.indexOf( 'buddyfile' ) != -1 )
				buddytype = 'file';
				
			if ( buddytype.indexOf( 'buddyfolder' ) != -1 )
				buddytype = 'folder';
			
			var shared = $(this);
			$(this).addClass('loading');
			
			var activity_type = 'buddydrive_groupupdate';
			
			if ( $(this).hasClass('buddydrive-profile-activity') )
				activity_type = 'buddydrive_profileupdate';
			
			var data = {
		      action: activity_type,
			  itemid: target,
			  url:link,
			  itemtype: buddytype,
			  '_wpnonce_buddydrive_actions': $("input#_wpnonce_buddydrive_actions").val()
		    };
		
			$.post(ajaxurl, data, function(response) {

				if ( response == 1 ) {
					shared.html( pluploadL10n.shared );
					shared.css('color', 'green');
					shared.addClass('shared');
				} else {
					alert( response );
				}
				shared.removeClass('loading');
		    });
		}

		if ( $(this).hasClass('buddydrive-remove-group') )
			return;
		
		var show = $(this).prop('class').replace('buddydrive-show-', ''); 
		var desc = $(this).parent().parent().parent().find('.buddydrive-ra-'+show);

		$(this).parent().parent().parent().parent().parent().find('.ba').each(function(){
			if ( $(this).get(0) != desc.get(0) )
				$(this).addClass('hide');
		});

		if ( desc.hasClass('hide') )
			desc.removeClass('hide');
		else
			desc.addClass('hide');

		if ( show == 'link' )
			desc.find('input').focus();

		return;
	});

	$.fn.selectRange = function(start, end) {
	    return this.each(function() {
	        if( this.setSelectionRange ) {
	            this.focus();
	            this.setSelectionRange(start, end);
	        } else if ( this.createTextRange ) {
	            var range = this.createTextRange();
	            range.collapse(true);
	            range.moveEnd('character', end);
	            range.moveStart('character', start);
	            range.select();
	        }
	 });
	};

	$('#buddydrive-dir').on('focus', '.buddydrive-file-input', function() {
		$(this).selectRange( 0, $(this).val().length );
		return;
	});

	$( '#buddydrive-filter').on('change', function( event ) {
		event.preventDefault();

		var buddyscope;
		
		$.cookie( 'buddydrive-oldestpage', 1, {path: '/'} );
		$.cookie( 'buddydrive_filter', $( this ).val(), {path: '/'} );
		
		updateMediaForm();

		if ( $('#subnav.item-list-tabs li.current a').length )
			buddyscope = $('#subnav.item-list-tabs li.current a').prop('id');

		parent_id = $('.buddytree.current').prop('id').replace( 'folder-', '' );
		
		var data = {
	      action:'buddydrive_filterby',
		  folder: parent_id,
		  page:1,
		  scope:buddyscope,
		  buddydrive_filter:$( this ).val(),
	    };

	    $('#buddydrive-dir tbody').html('<tr><td colspan="5"><p class="buddydrive-opening-dir"><a class="loading">'+pluploadL10n.loading+'</a></p></td></tr>');
	
		$.post(ajaxurl, data, function(response) {
			$('#buddydrive-dir tbody').html('');
	        $("#buddydrive-dir tbody").prepend(response);
	    } );
		
		return;
	});
	

	// init and set the uploader
	uploader_init = function() {
		uploader = new plupload.Uploader(wpUploaderInit);
		multipart_origin = uploader.settings.multipart_params;

		uploader.bind('Init', function(up) {
			var uploaddiv = $('#plupload-upload-ui');

			if ( up.features.dragdrop && ! $(document.body).hasClass('mobile') ) {
				uploaddiv.addClass('drag-drop');
				$('#drag-drop-area').bind('dragover.wp-uploader', function(){ // dragenter doesn't fire right :(
					uploaddiv.addClass('drag-over');
				}).bind('dragleave.wp-uploader, drop.wp-uploader', function(){
					uploaddiv.removeClass('drag-over');
				});
			} else {
				uploaddiv.removeClass('drag-drop');
				$('#drag-drop-area').unbind('.wp-uploader');
			}

			if ( up.runtime == 'html4' )
				$('.upload-flash-bypass').hide();

		});

		uploader.init();

		uploader.bind( 'FilesAdded', function(up, files) {
			// one file at a time !
			if ( files.length > 1 ) {
				for ( i in files )
					up.removeFile( files[i] );

				alert( pluploadL10n.one_at_a_time );

				return;
			}

			// Reset multipart params if needed
			if ( up.settings.multipart_params.customs )
				delete up.settings.multipart_params.customs;

			if ( up.settings.multipart_params.buddydesc )
				delete up.settings.multipart_params.buddydesc;

			if ( up.settings.multipart_params.buddyshared )
				delete up.settings.multipart_params.buddyshared;

			if ( up.settings.multipart_params.buddypass )
				delete up.settings.multipart_params.buddypass;

			if ( up.settings.multipart_params.buddygroup )
				delete up.settings.multipart_params.buddygroup;

			if ( up.settings.multipart_params.buddyfolder )
				delete up.settings.multipart_params.buddyfolder;
			
			if( $('#buddyfile-desc').val().length > 1 )
				up.settings.multipart_params.buddydesc = $('#buddyfile-desc').val();
				
			if ( $('#buddydrive-sharing-settings').val().length > 1 ) {
				up.settings.multipart_params.buddyshared = $('#buddydrive-sharing-settings').val();
				
				switch(up.settings.multipart_params.buddyshared) {
					case 'password':
						if( $('#buddypass').val().length < 1 ){
							alert( pluploadL10n.pwd_needed );
							return;
						} else {
							up.settings.multipart_params.buddypass = $('#buddypass').val();
						}
						break;
					case 'groups':
						up.settings.multipart_params.buddygroup = $('#buddygroup').val();
						break;
				}
			}
			
			if ( $('#buddydrive-open-folder').length )
				up.settings.multipart_params.buddyfolder = $('#buddydrive-open-folder').val();

			if ( $( '.buddydrive-customs' ).length ) {
				customs = get_custom_field( 'new' );

				up.settings.multipart_params.customs = JSON.stringify( customs );
			}
				
			
			var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);

			$('#media-upload-error').html('');

			plupload.each(files, function(file){
				if ( max > hundredmb && file.size > hundredmb && up.runtime != 'html5' )
					uploadSizeError( up, file, true );
				else
					fileQueued(file);
			});

			up.refresh();
			up.start();
		});

		uploader.bind('BeforeUpload', function(up, file) {
			// nothing to do
		});

		uploader.bind('UploadFile', function(up, file) {
			fileUploading(up, file);
		});

		uploader.bind('UploadProgress', function(up, file) {
			uploadProgress(up, file);
		});

		uploader.bind('Error', function(up, err) {
			uploadError(err.file, err.code, err.message, up);
			up.refresh();
		});

		uploader.bind('FileUploaded', function(up, file, response) {
			uploadSuccess(file, response.response);
		});

		uploader.bind('UploadComplete', function(up, files) {
			// nothing to do
		});
	}

	if ( typeof(wpUploaderInit) == 'object' )
		uploader_init();

});