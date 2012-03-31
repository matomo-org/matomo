/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function SitesManager ( _timezones, _currencies, _defaultTimezone, _defaultCurrency ) {

	var timezones = _timezones;
	var currencies = _currencies;
	var defaultTimezone = _defaultTimezone;
	var defaultCurrency = _defaultCurrency;
	var siteBeingEdited = false;
	var siteBeingEditedName = '';

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
		ajaxRequest.type = 'POST';
		
		var parameters = {};
	 	var siteName = $(row).find('input#name').val();
	 	var urls =  $(row).find('textarea#urls').val();
		urls = getApiFormatUrls(urls);
		var excludedIps = $(row).find('textarea#excludedIps').val();
		excludedIps = piwikHelper.getApiFormatTextarea(excludedIps);
		var timezone = encodeURIComponent($(row).find('#timezones option:selected').val());
		var currency = encodeURIComponent($(row).find('#currencies option:selected').val());
		var excludedQueryParameters = $(row).find('textarea#excludedQueryParameters').val();
		var ecommerce = encodeURIComponent($(row).find('#ecommerce option:selected').val());
		excludedQueryParameters = piwikHelper.getApiFormatTextarea(excludedQueryParameters);
		
		var request = '';
		request += '&module=API';
		request += '&format=json';
		request += '&method=SitesManager.addSite';
		siteName = encodeURIComponent(siteName);
		request += '&siteName='+siteName;
		request += '&timezone='+timezone;
		request += '&currency='+currency;
		request += '&ecommerce='+ecommerce;
		request += '&excludedIps='+excludedIps;
		request += '&excludedQueryParameters='+excludedQueryParameters;
		$.each(urls, function (key,value){ request+= '&urls[]='+escape(value);} );
	 	request += '&token_auth=' + piwik.token_auth;
	 	
		ajaxRequest.data = request;
	 	
		return ajaxRequest;
	}
	
	function getApiFormatUrls(urls)
	{
		var aUrls = urls.trim().split("\n");
		for(var i=0; i < aUrls.length; i++) {
			aUrls[i] = encodeURIComponent(aUrls[i]);
		}
		return aUrls;
	}
	
	function getUpdateSiteAJAX( row )
	{
		var ajaxRequest = piwikHelper.getStandardAjaxConf();
		ajaxRequest.type = 'POST';
		
		var siteName = $(row).find('input#siteName').val();
		var idSite = $(row).children('#idSite').html();
		var urls = $(row).find('textarea#urls').val();
		urls = getApiFormatUrls(urls);
		var excludedIps = $(row).find('textarea#excludedIps').val();
		excludedIps = piwikHelper.getApiFormatTextarea(excludedIps);
		var excludedQueryParameters = $(row).find('textarea#excludedQueryParameters').val();
		excludedQueryParameters = piwikHelper.getApiFormatTextarea(excludedQueryParameters);
		var timezone = encodeURIComponent($(row).find('#timezones option:selected').val());
		var currency = encodeURIComponent($(row).find('#currencies option:selected').val());
		var ecommerce = encodeURIComponent($(row).find('#ecommerce option:selected').val());
		var request = '';
		request += '&module=API';
		request += '&format=json';
		request += '&method=SitesManager.updateSite';
		siteName = encodeURIComponent(siteName);
		request += '&siteName='+siteName;
		request += '&idSite='+idSite;
		request += '&timezone='+timezone;
		request += '&currency='+currency;
		request += '&ecommerce='+ecommerce;
		request += '&excludedIps='+excludedIps;
		request += '&excludedQueryParameters='+excludedQueryParameters;
		$.each(urls, function (key,value){ if(value.length>1) request+= '&urls[]='+value;} );
	 	request += '&token_auth=' + piwik.token_auth;
	 	
		ajaxRequest.data = request;
		
		return ajaxRequest;
	}
	
	function getGlobalSettingsAJAX()
	{
		var ajaxRequest = piwikHelper.getStandardAjaxConf('ajaxLoadingGlobalSettings', 'ajaxErrorGlobalSettings');
		var timezone = encodeURIComponent($('#defaultTimezone option:selected').val());
		var currency = encodeURIComponent($('#defaultCurrency option:selected').val());
		var excludedIps = $('textarea#globalExcludedIps').val();
		excludedIps = piwikHelper.getApiFormatTextarea(excludedIps);
		var excludedQueryParameters = $('textarea#globalExcludedQueryParameters').val();
		excludedQueryParameters = piwikHelper.getApiFormatTextarea(excludedQueryParameters);
		var request = '';
		request += 'module=SitesManager';
		request += '&action=setGlobalSettings';
		request += '&format=json';
		request += '&timezone='+timezone;
		request += '&currency='+currency;
		request += '&excludedIps='+excludedIps;
		request += '&excludedQueryParameters='+excludedQueryParameters;
	 	request += '&token_auth=' + piwik.token_auth;
		ajaxRequest.data = request;
		return ajaxRequest;
	}

	this.init = function () {
	$('.addRowSite').click( function() {
		piwikHelper.hideAjaxError();
		$('.addRowSite').toggle();
		
		var numberOfRows = $('table#editSites')[0].rows.length;
		var newRowId = 'row' + numberOfRows;
		var submitButtonHtml = '<input type="submit" class="addsite submit" value="' + _pk_translate('General_Save_js') +'" />';
		$(' <tr id="'+newRowId+'">\
				<td>&nbsp;</td>\
				<td><input id="name" value="Name" size="15" /><br/><br/><br/>'+submitButtonHtml+'</td>\
				<td><textarea cols="25" rows="3" id="urls">http://siteUrl.com/\nhttp://siteUrl2.com/</textarea><br />'+aliasUrlsHelp+'</td>\
				<td><textarea cols="20" rows="4" id="excludedIps"></textarea><br />'+excludedIpHelp+'</td>\
				<td><textarea cols="20" rows="4" id="excludedQueryParameters"></textarea><br />'+excludedQueryParametersHelp+'</td>\
				<td>'+getTimezoneSelector(defaultTimezone)+'<br />' + timezoneHelp + '</td>\
				<td>'+getCurrencySelector(defaultCurrency)+'<br />' + currencyHelp + '</td>\
				<td>'+getEcommerceSelector(0) + '<br />' + ecommerceHelp+ '</td>\
				<td>'+submitButtonHtml+'</td>\
	  			<td><span class="cancel link_but">'+sprintf(_pk_translate('General_OrCancel_js'),"","")+'</span></td>\
	 		</tr>')
	  			.appendTo('#editSites')
		;
		
	  	piwikHelper.lazyScrollTo('#'+newRowId);
	  	
		$('.addsite').click( function(){ 
			$.ajax( getAddSiteAJAX($('tr#'+newRowId)) ); 
		});
		
		$('.cancel').click(function() { 
			piwikHelper.hideAjaxError(); 
			$(this).parents('tr').remove();  
			$('.addRowSite').toggle(); 
		});
		return false;
	 } );
	
	// when click on deleteuser, the we ask for confirmation and then delete the user
	$('.deleteSite').click( function() {
			piwikHelper.hideAjaxError();
			var idRow = $(this).attr('id');
			var nameToDelete = $(this).parent().parent().find('input#siteName').val() || $(this).parent().parent().find('td#siteName').html();
			var idsiteToDelete = $(this).parent().parent().find('#idSite').html();
			
			$('#confirm h2').text(sprintf(_pk_translate('SitesManager_DeleteConfirm_js'),'"'+nameToDelete+'" (idSite = '+idsiteToDelete+')'));
			piwikHelper.modalConfirm('#confirm', {yes: function(){
			    $.ajax( getDeleteSiteAJAX( idsiteToDelete ) );
			}});
		}
	);
	
	var alreadyEdited = new Array;
	$('.editSite')
		.click( function() {
			piwikHelper.hideAjaxError();
			var idRow = $(this).attr('id');
			if(alreadyEdited[idRow]==1) return;
			if(siteBeingEdited)
			{
				$('#alert h2').text(sprintf(_pk_translate('SitesManager_OnlyOneSiteAtTime_js'), '"'+$("<div/>").html(siteBeingEditedName).text()+'"'));
				piwikHelper.modalConfirm('#alert', {});
				return;
			}
			siteBeingEdited = true;
			
			alreadyEdited[idRow] = 1;
			$('tr#'+idRow+' .editableSite').each(
				// make the fields editable
				// change the EDIT button to VALID button
				function (i,n) {
					var contentBefore = $(n).html();

					var idName = $(n).attr('id');
					if(idName == 'siteName')
					{
						siteBeingEditedName = contentBefore;
						var contentAfter = '<input id="'+idName+'" value="'+piwikHelper.htmlEntities(contentBefore)+'" size="15" />';
						
						var inputSave = $('<br/><input style="margin-top:50px" type="submit" class="submit" value="'+_pk_translate('General_Save_js')+'" />')
											.click( function(){ submitUpdateSite($(this).parent()); });
						var spanCancel = $('<div><br/>'+sprintf(_pk_translate('General_OrCancel_js'),"","")+'</div>')
											.click( function(){ piwikHelper.refreshAfter(0); } );
						$(n)
							.html(contentAfter)
							.keypress( submitSiteOnEnter )
							.append(inputSave)
							.append(spanCancel);
					}
					else if(idName == 'urls')
					{
						var contentAfter = '<textarea cols="25" rows="3" id="urls">'+contentBefore.replace(/<br *\/? *>/gi,"\n")+'</textarea>';
						contentAfter += '<br />'+aliasUrlsHelp;
						$(n).html(contentAfter);
					}
					else if(idName == 'excludedIps')
					{
						var contentAfter = '<textarea cols="20" rows="4" id="excludedIps">'+contentBefore.replace(/<br *\/? *>/gi,"\n")+'</textarea>';
						contentAfter += '<br />'+excludedIpHelp;
						$(n).html(contentAfter);
					}
					else if(idName == 'excludedQueryParameters')
					{
						var contentAfter = '<textarea cols="20" rows="4" id="excludedQueryParameters">'+contentBefore.replace(/<br *\/? *>/gi,"\n")+'</textarea>';
						contentAfter += '<br />'+excludedQueryParametersHelp;
						$(n).html(contentAfter);
					}
					else if(idName == 'timezone')
					{
						var contentAfter = getTimezoneSelector(contentBefore);
						contentAfter += '<br />' + timezoneHelp;
						$(n).html(contentAfter);
					}
					else if(idName == 'currency')
					{
						var contentAfter = getCurrencySelector(contentBefore);
						contentAfter += '<br />' + currencyHelp;
						$(n).html(contentAfter);
					}
					else if(idName == 'ecommerce')
					{
						ecommerceActive = contentBefore.indexOf("ecommerceActive") > 0 ? 1 : 0;
						contentAfter = getEcommerceSelector(ecommerceActive) + '<br />' + ecommerceHelp; 
						$(n).html(contentAfter);
					}
				}
			);
			$(this)
				.toggle()
				.parent()
				.prepend( $('<input type="submit" class="updateSite submit" value="' + _pk_translate('General_Save_js') + '" />')
							.click( function(){ $.ajax( getUpdateSiteAJAX( $('tr#'+idRow) ) ); } ) 
					);
		});
	
		$('#globalSettingsSubmit').click( function() {
			$.ajax( getGlobalSettingsAJAX() );
		});
	
		$('#defaultTimezone').html( getTimezoneSelector(defaultTimezone));
		$('#defaultCurrency').html( getCurrencySelector(defaultCurrency));
		
		$('td.editableSite').click( function(){ $(this).parent().find('.editSite').click(); } );
	}
	
	function getEcommerceSelector(enabled)
	{
		var html = '<select id="ecommerce">';
		selected = ' selected="selected" ';
		html += '<option ' + (enabled ? '' : selected) + ' value="0">' + ecommerceDisabled + '</option>';
		html += '<option ' + (enabled ? selected : '') + ' value="1">' + ecommerceEnabled + '</option>';
		html += '</select>';
		return html;
	}
	
	function getTimezoneSelector(selectedTimezone)
	{
		var html = '<select id="timezones">';
		for(var continent in timezones) {
			html += '<optgroup label="' + continent + '">';
			for(var timezoneId in timezones[continent]) {
				var selected = '';
				if(timezoneId == selectedTimezone) {
					selected = ' selected="selected" ';
				}
				html += '<option ' + selected + ' value="'+ timezoneId + '">' + timezones[continent][timezoneId] + '</option>';
			}
			html += "</optgroup>\n";
		}
		html += '</select>';
		return html;
	}
	
	
	function getCurrencySelector(selectedCurrency)
	{
		var html = '<select id="currencies">';
		for(var currency in currencies) {
			var selected = '';
			if(currency == selectedCurrency) {
				selected = ' selected="selected" ';
			}
			html += '<option ' + selected + ' value="'+ currency + '">' + currencies[currency] + '</option>';
		}
		html += '</select>';
		return html;
	}
	
	function submitSiteOnEnter(e)
	{
		var key=e.keyCode || e.which;
		if (key==13)
		{
			submitUpdateSite(this);
			$(this).find('.addsite').click();
		}
	}
	function submitUpdateSite(self)
	{
		$(self).parent().find('.updateSite').click();
	}
}
