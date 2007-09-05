

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

function getUpdateUserAccess(login, access)
{
	var ajaxRequest = getStandardAjaxConf();
	
	ajaxRequest.success = function (response){
							if(response.result == "error") 
							{
								ajaxShowError(response.message);
							}
						}
	ajaxRequest.async = false;
	
	// prepare the API parameters to add the user
	var parameters = new Object;
	parameters.module = 'API';
	parameters.format = 'json';
 	parameters.method =  'UsersManager.setUserAccess';
 	parameters.userLogin = login;
 	parameters.access = access;
 	
 	var idSites = $('#selectIdsite option:selected').val();
 	if(idSites != -1)
 	{
	 	parameters.idSites = idSites;
 	}
 	
	ajaxRequest.data = parameters;
 	
	return ajaxRequest;
}

function submitOnEnter(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		$(this).find('#adduser').click();
		$(this).find('#updateuser').click();
	}
}

function bindUpdateAccess()
{
	$('#accessUpdated').hide();
	
	//launching AJAX request
	$.ajax( getUpdateUserAccess( 
					$(this).parent().parent().find('#login').html(),
					$(this).parent().attr('id')
				) 
			);
	
	//once successful
	$(this).parent().parent().find('.accessGranted')
		.attr("src","plugins/UsersManager/images/no-access.png" )
		.attr("class","updateAccess" )
		.click(bindUpdateAccess)
		;
	$(this)
		.attr('src',"plugins/UsersManager/images/ok.png" )
		.attr('class',"accessGranted" )
		;
	$('#accessUpdated').show();
	$('#accessUpdated').fadeOut(1500);
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
					.prepend( $('<img src="plugins/UsersManager/images/ok.png" id="updateuser">')
								.click( function(){ $.ajax( getUpdateUserAJAX( $('tr#'+idRow) ) ); } ) 
						);
				
				
				
			}
	);
	$('.editable').keypress( submitOnEnter );
	
	$('td.editable')
		.hover( function() {  
		 	 $(this).css({ cursor: "pointer"}); 
		  	},
		  	function() {  
		 	 $(this).css({ cursor: "auto"}); 
		  	}
	 	)
	 	.click( function(){ $(this).parent().find('.edituser').click(); } )
	 ;
	
	// when click on deleteuser, the we ask for confirmation and then delete the user
	$('.deleteuser').click( function() {
			ajaxHideError();
			var idRow = $(this).attr('id');
			var loginToDelete = $(this).parent().parent().find('#userLogin').html();
			if(confirm('Are you sure you want to delete the user "'+loginToDelete+'"?'))
			{
				$.ajax( getDeleteUserAJAX( loginToDelete ) );
			}
		}
	);
	
	$('#addrow').click( function() {
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
				<td><img src="plugins/UsersManager/images/ok.png" id="adduser" href="#"></td>\
	  			<td><img src="plugins/UsersManager/images/remove.png" id="cancel"></td>\
	 		</tr>')
	  			.appendTo('#users')
		;
		$('#'+newRowId).keypress( submitOnEnter );
		$('#adduser').click( function(){ $.ajax( getAddUserAJAX($('tr#'+newRowId)) ); } );
		$('#cancel').click(function() { ajaxHideError(); $(this).parents('tr').remove();  $('#addrow').toggle(); });
	
	 } );
	$('.updateAccess').click( bindUpdateAccess );
});	

