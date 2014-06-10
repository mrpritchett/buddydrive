function buddyDriveListGroups( element, user ) {
	var data = {
      action:'buddydrive_getgroups',
      userid:user,
      selectname:'buddydrive-edit[buddygroup]'
    };

    jQuery.post(ajaxurl, data, function(response) {
        jQuery(element).html( '<label for="buddygroup">' + buddydrive_admin.label_group + '</label>' + response);
    });
}

jQuery(document).ready(function($){
	$.cookie( 'buddydrive-admin-oldestpage', 1, {path: '/'} );

	var id_detail_old_html = $('#buddydrive-admin-privacy-detail').html();

	$('#buddydrive-admin-sharing-options').on('change', function(){
		var privacy = $(this).val();

		id_details = '#buddydrive-admin-privacy-detail';
		user = $('#buddydrive-owner-id').val();

		switch(privacy) {
			case 'password':
				if( id_detail_old_html.indexOf('buddydrive-password') != -1 ) 
					$(id_details).html( id_detail_old_html);
				else
					$(id_details).html('<label for="buddydrive-password">' + buddydrive_admin.label_pwd + '</label><input type="text" name="buddydrive-edit[password]" id="buddydrive-password"/>');
				break;
			case 'groups':
				if( id_detail_old_html.indexOf('buddygroup') != -1 ) 
					$(id_details).html( id_detail_old_html);
				else
					buddyDriveListGroups( id_details, user );
				break;
			default:
				$(id_details).html('');
				break;
		}

		return false;
	});

	$('#buddydrive-admin-files').on('click', '.buddydrive-load-more a', function(){
		var currentfolder = false;
		
		if( $('#buddydrive-admin-files').length )
			currentfolder = $('#buddydrive-admin-files').attr('data-folder');
		
		var loadmore_tr = $(this).parent().parent();
		
		$(this).addClass('loading');
		
		if ( null == $.cookie('buddydrive-admin-oldestpage') )
	        $.cookie('buddydrive-admin-oldestpage', 1, {path: '/'} );

	    var oldest_page = ( $.cookie('buddydrive-admin-oldestpage') * 1 ) + 1;
		
		var data = {
	      action:'buddydrive_adminloadmore',
	      page: oldest_page,
		  folder:currentfolder
	    };

	    $.post(ajaxurl, data, function(response) {
	    	console.log( response );
	        $.cookie( 'buddydrive-admin-oldestpage', oldest_page, {path: '/'} );
	        $("#buddydrive-admin-files tbody").append(response);
			loadmore_tr.hide();
	    });
		
		return false;
	});
});

/* jQuery Cookie plugin */
jQuery.cookie=function(name,value,options){if(typeof value!='undefined'){options=options||{};if(value===null){value='';options.expires=-1;}var expires='';if(options.expires&&(typeof options.expires=='number'||options.expires.toUTCString)){var date;if(typeof options.expires=='number'){date=new Date();date.setTime(date.getTime()+(options.expires*24*60*60*1000));}else{date=options.expires;}expires='; expires='+date.toUTCString();}var path=options.path?'; path='+(options.path):'';var domain=options.domain?'; domain='+(options.domain):'';var secure=options.secure?'; secure':'';document.cookie=[name,'=',encodeURIComponent(value),expires,path,domain,secure].join('');}else{var cookieValue=null;if(document.cookie&&document.cookie!=''){var cookies=document.cookie.split(';');for(var i=0;i<cookies.length;i++){var cookie=jQuery.trim(cookies[i]);if(cookie.substring(0,name.length+1)==(name+'=')){cookieValue=decodeURIComponent(cookie.substring(name.length+1));break;}}}return cookieValue;}};
