

function getUpdateUserAJAX( row )
{
	var ajaxRequest = getStandardAjaxConf();
	toggleAjaxLoading();
	
	// prepare the API parameters to update the user
	var parameters = new Object;
	parameters.module = 'API';
	parameters.format = 'json';
 	parameters.method =  'UsersManager.updateUser';
 	parameters.userLogin = $(row).children('#userLogin').html();
 	var password =  $(row).find('input[@id=password]').val();
 	if(password != '-') parameters.password = password;
 	parameters.email = $(row).find('input[@id=email]').val();
 	parameters.alias = $(row).find('input[@id=alias]').val();
	
	ajaxRequest.data = parameters;
	
	return ajaxRequest;

}

function getDeleteUserAJAX( login )
{
	var ajaxRequest = getStandardAjaxConf();
	toggleAjaxLoading();
		
	// prepare the API parameters to update the user
	var parameters = new Object;
	parameters.module = 'API';
	parameters.format = 'json';
 	parameters.method =  'UsersManager.deleteUser';
 	parameters.userLogin = login;
	
	ajaxRequest.data = parameters;
	
	return ajaxRequest;
}

function getAddUserAJAX( row )
{
	var ajaxRequest = getStandardAjaxConf();
	toggleAjaxLoading();
	
	// prepare the API parameters to add the user
	var parameters = new Object;
	parameters.module = 'API';
	parameters.format = 'json';
 	parameters.method =  'UsersManager.addUser';
 	parameters.userLogin = $(row).find('input[@id=useradd_login]').val();
 	parameters.password =  $(row).find('input[@id=useradd_password]').val();
 	parameters.email = $(row).find('input[@id=useradd_email]').val();
 	parameters.alias = $(row).find('input[@id=useradd_alias]').val();
 	
	ajaxRequest.data = parameters;
 	
	return ajaxRequest;
}

function getIdSites()
{
	return $('#selectIdsite option:selected').val();
}

function getUpdateUserAccess(login, access, successCallback)
{
	var ajaxRequest = getStandardAjaxConf();
	
	ajaxRequest.success = successCallback;
	ajaxRequest.async = false;
	
	// prepare the API parameters to add the user
	var parameters = new Object;
	parameters.module = 'API';
	parameters.format = 'json';
 	parameters.method =  'UsersManager.setUserAccess';
 	parameters.userLogin = login;
 	parameters.access = access;
 	parameters.idSites = getIdSites();

	ajaxRequest.data = parameters;
 	
	return ajaxRequest;
}

function submitOnEnter(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		$(this).find('.adduser').click();
		$(this).find('.updateuser').click();
	}
}


function launchAjaxRequest(self, successCallback)
{
	//launching AJAX request
	$.ajax( getUpdateUserAccess( 
					$(self).parent().parent().find('#login').html(),//if changed change also the modal
					$(self).parent().attr('id'),
					successCallback
				) 
			);	
}

function bindUpdateAccess()
{
	$('#accessUpdated').hide();
	var self = this;
	
	// callback called when the ajax request Update the user permissions is successful
	function successCallback (response)
	{
		// if the permission couldn't be granted
		if(response.result == "error") 
		{
			ajaxShowError(response.message);
		}
		// if the permission change was successful
		else
		{
			ajaxHideError();
			
			//once successful
			$(self).parent().parent().find('.accessGranted')
				.attr("src","plugins/UsersManager/images/no-access.png" )
				.attr("class","updateAccess" )
				.click(bindUpdateAccess)
				;
			$(self)
				.attr('src',"plugins/UsersManager/images/ok.png" )
				.attr('class',"accessGranted" )
				;
			$('#accessUpdated').show();
			$('#accessUpdated').fadeOut(1500);
		}
	}
	
	var idSite = getIdSites();
	if(idSite == 'all')
	{
		var target = this;       
		
		//ask confirmation
		var userLogin = $(this).parent().parent().find('#login').html();
		$('.dialog#confirm #login').text( userLogin ); // if changed here change also the launchAjaxRequest
		var question = $('.dialog#confirm').clone();
		$('#yes', question).click(function()
		{
			launchAjaxRequest(target, successCallback);	
			$.unblockUI();
		});
		
		$('#no', question).click($.unblockUI);
		$.blockUI(question, { width: '300px' });
	}
	else
	{
		launchAjaxRequest(this, successCallback);
	}
}

$(document).ready( function() {

	var alreadyEdited = new Array;
	// when click on edituser, the cells become editable
	$('.edituser')
		.click( function() {
				ajaxHideError();
				var idRow = $(this).attr('id');
				if(alreadyEdited[idRow]==1) return;
				alreadyEdited[idRow] = 1;
				$('tr#'+idRow+' .editable').each(
							// make the fields editable
							// change the EDIT button to VALID button
							function (i,n) {
								var contentBefore = $(n).html();
								var idName = $(n).attr('id');
								if(idName != 'userLogin')
								{
									var contentAfter = '<input id="'+idName+'" value="'+contentBefore+'" size="10">';
									$(n).html(contentAfter);
								}
							}
						);
						
				$(this)
					.toggle()
					.parent()
					.prepend( $('<img src="plugins/UsersManager/images/ok.png" class="updateuser">')
								.click( function(){ $.ajax( getUpdateUserAJAX( $('tr#'+idRow) ) ); } ) 
						);
				
				
				
			}
	);
	$('.editable').keypress( submitOnEnter );
	
	$('td.editable')
	 	.click( function(){ $(this).parent().find('.edituser').click(); } )
	 ;
	
	// when click on deleteuser, the we ask for confirmation and then delete the user
	$('.deleteuser')
		.click( function() {
			ajaxHideError();
			var idRow = $(this).attr('id');
			var loginToDelete = $(this).parent().parent().find('#userLogin').html();
			if( confirm(sprintf(_pk_translate('UsersManager_DeleteConfirm'),'"'+loginToDelete+'"')) )
			{
				$.ajax( getDeleteUserAJAX( loginToDelete ) );
			}
		}
	);
	
	$('.addrow').click( function() {
		ajaxHideError();
		$(this).toggle();
		
		var numberOfRows = $('table#users')[0].rows.length;
		var newRowId = numberOfRows + 1;
		var newRowId = 'row' + newRowId;
	
		$(' <tr id="'+newRowId+'">\
				<td><input id="useradd_login" value="login?" size=10></td>\
				<td><input id="useradd_password" value="password" size=10></td>\
				<td><input id="useradd_email" value="email@domain.com" size=15></td>\
				<td><input id="useradd_alias" value="alias" size=15></td>\
				<td>-</td>\
				<td><img src="plugins/UsersManager/images/ok.png" class="adduser"></td>\
	  			<td><img src="plugins/UsersManager/images/remove.png" class="cancel"></td>\
	 		</tr>')
	  			.appendTo('#users')
		;
		$('#'+newRowId).keypress( submitOnEnter );
		$('.adduser').click( function(){ $.ajax( getAddUserAJAX($('tr#'+newRowId)) ); } );
		$('.cancel').click(function() { ajaxHideError(); $(this).parents('tr').remove();  $('.addrow').toggle(); });
	
	 } );
	$('.updateAccess')
		.click( bindUpdateAccess )
		;
});	

