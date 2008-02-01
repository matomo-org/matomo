
function getDeleteSiteAJAX( idSite )
{
	var ajaxRequest = getStandardAjaxConf();
	toggleAjaxLoading();
		
	// prepare the API parameters to update the user
	var parameters = new Object;
	parameters.module = 'API';
	parameters.format = 'json';
 	parameters.method =  'SitesManager.deleteSite';
 	parameters.idSite = idSite;
	
	ajaxRequest.data = parameters;
	
	return ajaxRequest;
}

function getAddSiteAJAX( row )
{
	var ajaxRequest = getStandardAjaxConf();
	toggleAjaxLoading();
	
	// prepare the API parameters to add the user
	var parameters = new Object;
	
 	var siteName = $(row).find('input[@id=siteadd_name]').val();
 	var urls =  $(row).find('textarea[@id=siteadd_urls]').val();
	var urls = urls.trim().split("\n");
 	
	var request = '';
	request += '&module=API';
	request += '&format=json';
	request += '&method=SitesManager.addSite';
	request += '&siteName='+escape(siteName);
	
	$.each(urls, function (key,value){ request+= '&urls[]='+escape(value);} );

	ajaxRequest.data = request;
 	
	return ajaxRequest;
}

function getUpdateSiteAJAX( row )
{
	var ajaxRequest = getStandardAjaxConf();
	toggleAjaxLoading();
	
	var siteName = $(row).find('input[@id=siteName]').val();
	var idSite = $(row).children('#idSite').html();
	var urls = $(row).find('textarea[@id=urls]').val().trim().split("\n");
	
	var request = '';
	request += '&module=API';
	request += '&format=json';
	request += '&method=SitesManager.updateSite';
	request += '&siteName='+escape(siteName);
	request += '&idSite='+idSite;
	$.each(urls, function (key,value){ if(value.length>1) request+= '&urls[]='+value;} );

	ajaxRequest.data = request;
	
	return ajaxRequest;

}


	$(document).ready( function() {
	$('#addRowSite').click( function() {
		ajaxHideError();
		$(this).toggle();
		
		var numberOfRows = $('table#editSites')[0].rows.length;
		var newRowIdNumeric = numberOfRows ;
		var newRowId = 'row' + newRowIdNumeric;
	
		$(' <tr id="'+newRowId+'">\
				<td>'+newRowIdNumeric+'</td>\
				<td><input id="siteadd_name" value="Name" size=10></td>\
				<td><textarea cols=30 rows=3 id="siteadd_urls">http://siteUrl.com/\nhttp://siteUrl2.com/</textarea></td>\
				<td><img src="plugins/UsersManager/images/ok.png" id="addsite" href="#"></td>\
	  			<td><img src="plugins/UsersManager/images/remove.png" id="cancel"></td>\
	 		</tr>')
	  			.appendTo('#editSites')
		;
		$('#'+newRowId).keypress( submitSiteOnEnter );
		$('#addsite').click( function(){ $.ajax( getAddSiteAJAX($('tr#'+newRowId)) ); } );
		$('#cancel').click(function() { ajaxHideError(); $(this).parents('tr').remove();  $('#addRowSite').toggle(); });
	
	 } );
	
	// when click on deleteuser, the we ask for confirmation and then delete the user
	$('.deleteSite').click( function() {
			ajaxHideError();
			var idRow = $(this).attr('id');
			var nameToDelete = $(this).parent().parent().find('#siteName').html();
			var idsiteToDelete = $(this).parent().parent().find('#idSite').html();
			if(confirm('Are you sure you want to delete the website "'+nameToDelete+'" (idSite = '+idsiteToDelete+')?'))
			{
				$.ajax( getDeleteSiteAJAX( idsiteToDelete ) );
			}
		}
	);
	
	var alreadyEdited = new Array;
	// when click on edituser, the cells become editable
	$('.editSite')
		.click( function() {
				ajaxHideError();
				var idRow = $(this).attr('id');
				if(alreadyEdited[idRow]==1) return;
				alreadyEdited[idRow] = 1;
				$('tr#'+idRow+' .editableSite').each(
							// make the fields editable
							// change the EDIT button to VALID button
							function (i,n) {
								var contentBefore = $(n).html();
								var idName = $(n).attr('id');
								if(idName == 'siteName')
								{
									var contentAfter = '<input id="'+idName+'" value="'+contentBefore+'" size="10">';
									$(n)
										.html(contentAfter)
										.keypress( submitSiteOnEnter );
								}
								if(idName == 'urls')
								{
									var contentAfter = '<textarea cols=30 rows=3 id="urls">'+contentBefore.replace(/<br>/gi,"\n")+'</textarea>';
									$(n).html(contentAfter);
								}
							}
						);
						
				$(this)
					.toggle()
					.parent()
					.prepend( $('<img src="plugins/UsersManager/images/ok.png" id="updateSite">')
								.click( function(){ $.ajax( getUpdateSiteAJAX( $('tr#'+idRow) ) ); } ) 
						);
				
				
				
			}
	);
	
	
	$('td.editableSite')
		.hover( function() {  
		 	 $(this).css({ cursor: "pointer"}); 
		  	},
		  	function() {  
		 	 $(this).css({ cursor: "auto"}); 
		  	}
	 	)
	 	.click( function(){ $(this).parent().find('.editSite').click(); } )
	 ;
});
 
function submitSiteOnEnter(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		$(this).parent().find('#updateSite').click();
		$(this).find('#addsite').click();
	}
}
