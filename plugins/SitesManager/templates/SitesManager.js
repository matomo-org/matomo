function getDeleteSiteAJAX( idSite )
{
	var ajaxRequest = piwikHelper.getStandardAjaxConf();

	var parameters = {};
	parameters.module = 'API';
	parameters.format = 'json';
 	parameters.method =  'SitesManager.deleteSite';
 	parameters.idSite = idSite;
 	parameters.token_auth = piwik.token_auth;
	
	ajaxRequest.data = parameters;
	
	return ajaxRequest;
}

function getAddSiteAJAX( row )
{
	var ajaxRequest = piwikHelper.getStandardAjaxConf();
	
	var parameters = {};
 	var siteName = $(row).find('input#siteadd_name').val();
 	var urls =  $(row).find('textarea#siteadd_urls').val();
	var urls = getApiFormatUrls(urls);
	var excludedIps = $(row).find('textarea#siteadd_excludedIps').val();
	excludedIps = getApiFormatExcludedIps(excludedIps);
 	
	var request = '';
	request += '&module=API';
	request += '&format=json';
	request += '&method=SitesManager.addSite';
	siteName = encodeURIComponent(siteName);
	request += '&siteName='+siteName;
	request += '&excludedIps='+excludedIps;
	$.each(urls, function (key,value){ request+= '&urls[]='+escape(value);} );
 	request += '&token_auth=' + piwik.token_auth;
 	
	ajaxRequest.data = request;
 	
	return ajaxRequest;
}

function getApiFormatUrls(urls)
{
	return urls.trim().split("\n");
}
function getApiFormatExcludedIps(excludedIps)
{
	return excludedIps.trim().split("\n").join(',');
}

function getUpdateSiteAJAX( row )
{
	var ajaxRequest = piwikHelper.getStandardAjaxConf();
	
	var siteName = $(row).find('input#siteName').val();
	var idSite = $(row).children('#idSite').html();
	var urls = $(row).find('textarea#urls').val();
	urls = getApiFormatUrls(urls);
	var excludedIps = $(row).find('textarea#excludedIps').val();
	excludedIps = getApiFormatExcludedIps(excludedIps);
	
	var request = '';
	request += '&module=API';
	request += '&format=json';
	request += '&method=SitesManager.updateSite';
	siteName = encodeURIComponent(siteName);
	request += '&siteName='+siteName;
	request += '&idSite='+idSite;
	request += '&excludedIps='+excludedIps;
	$.each(urls, function (key,value){ if(value.length>1) request+= '&urls[]='+value;} );
 	request += '&token_auth=' + piwik.token_auth;
 	
	ajaxRequest.data = request;
	
	return ajaxRequest;
}

function getSetGlobalExcludedIpsAJAX()
{
	var ajaxRequest = piwikHelper.getStandardAjaxConf('ajaxLoadingExcludedIps', 'ajaxErrorExcludedIps');
	var excludedIps = $('textarea#globalExcludedIps').val();
	excludedIps = getApiFormatExcludedIps(excludedIps);
	var request = '';
	request += '&module=API';
	request += '&format=json';
	request += '&method=SitesManager.setGlobalExcludedIps';
	request += '&excludedIps='+excludedIps;
 	request += '&token_auth=' + piwik.token_auth;
	ajaxRequest.data = request;
	return ajaxRequest;
}

$(document).ready( function() {
	$('.addRowSite').click( function() {
		piwikHelper.hideAjaxError();
		$(this).toggle();
		
		var numberOfRows = $('table#editSites')[0].rows.length;
		var newRowId = 'row' + numberOfRows;
	
		$(' <tr id="'+newRowId+'">\
				<td>&nbsp;</td>\
				<td><input id="siteadd_name" value="Name" size="25" /></td>\
				<td><textarea cols="30" rows="3" id="siteadd_urls">http://siteUrl.com/\nhttp://siteUrl2.com/</textarea><br />'+aliasUrlsHelp+'</td>\
				<td><textarea cols="30" rows="3" id="siteadd_excludedIps"></textarea><br />'+excludedIpHelp+'</td>\
				<td><img src="plugins/UsersManager/images/ok.png" class="addsite" href="#" /></td>\
	  			<td><img src="plugins/UsersManager/images/remove.png" class="cancel" /></td>\
	 		</tr>')
	  			.appendTo('#editSites')
		;
		$('#'+newRowId).keypress( submitSiteOnEnter );
		$('.addsite').click( function(){ $.ajax( getAddSiteAJAX($('tr#'+newRowId)) ); } );
		$('.cancel').click(function() { piwikHelper.hideAjaxError(); $(this).parents('tr').remove();  $('.addRowSite').toggle(); });
		return false;
	 } );
	
	// when click on deleteuser, the we ask for confirmation and then delete the user
	$('.deleteSite').click( function() {
			piwikHelper.hideAjaxError();
			var idRow = $(this).attr('id');
			var nameToDelete = $(this).parent().parent().find('input#siteName').val() || $(this).parent().parent().find('td#siteName').html();
			var idsiteToDelete = $(this).parent().parent().find('#idSite').html();
			if(confirm(sprintf(_pk_translate('SitesManager_DeleteConfirm_js'),'"'+nameToDelete+'" (idSite = '+idsiteToDelete+')')) ) {
				$.ajax( getDeleteSiteAJAX( idsiteToDelete ) );
			}
		}
	);
	
	var alreadyEdited = new Array;
	$('.editSite')
		.click( function() {
			piwikHelper.hideAjaxError();
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
						var contentAfter = '<input id="'+idName+'" value="'+contentBefore+'" size="25" />';
						$(n)
							.html(contentAfter)
							.keypress( submitSiteOnEnter );
					}
					if(idName == 'urls')
					{
						var contentAfter = '<textarea cols="30" rows="3" id="urls">'+contentBefore.replace(/<br *\/? *>/gi,"\n")+'</textarea>';
						contentAfter += '<br />'+aliasUrlsHelp;
						$(n).html(contentAfter);
					}
					if(idName == 'excludedIps')
					{
						var contentAfter = '<textarea cols="30" rows="3" id="excludedIps">'+contentBefore.replace(/<br *\/? *>/gi,"\n")+'</textarea>';
						contentAfter += '<br />'+excludedIpHelp;
						$(n).html(contentAfter);
					}
				}
			);
			$(this)
				.toggle()
				.parent()
				.prepend( $('<img src="plugins/UsersManager/images/ok.png" class="updateSite" />')
							.click( function(){ $.ajax( getUpdateSiteAJAX( $('tr#'+idRow) ) ); } ) 
					);
		}
	);
	
	$('#globalExcludedIpsSubmit').click( function() {
		$.ajax( getSetGlobalExcludedIpsAJAX() );
	});
	
	$('td.editableSite').click( function(){ $(this).parent().find('.editSite').click(); } );
});
 
function submitSiteOnEnter(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		$(this).parent().find('.updateSite').click();
		$(this).find('.addsite').click();
	}
}
