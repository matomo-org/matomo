function SitesManager ( _timezones, _currencies, _defaultTimezone, _defaultCurrency ) {

	var timezones = _timezones;
	var currencies = _currencies;
	var defaultTimezone = _defaultTimezone;
	var defaultCurrency = _defaultCurrency;

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
	 	var siteName = $(row).find('input#name').val();
	 	var urls =  $(row).find('textarea#urls').val();
		urls = getApiFormatUrls(urls);
		var excludedIps = $(row).find('textarea#excludedIps').val();
		excludedIps = getApiFormatTextarea(excludedIps);
		var timezone = encodeURIComponent($(row).find('#timezones option:selected').val());
		var currency = encodeURIComponent($(row).find('#currencies option:selected').val());
		var excludedQueryParameters = $(row).find('textarea#excludedQueryParameters').val();
		excludedQueryParameters = getApiFormatTextarea(excludedQueryParameters);
		
		var request = '';
		request += '&module=API';
		request += '&format=json';
		request += '&method=SitesManager.addSite';
		siteName = encodeURIComponent(siteName);
		request += '&siteName='+siteName;
		request += '&timezone='+timezone;
		request += '&currency='+currency;
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
	function getApiFormatTextarea(textareaContent)
	{
		return textareaContent.trim().split("\n").join(',');
	}
	
	function getUpdateSiteAJAX( row )
	{
		var ajaxRequest = piwikHelper.getStandardAjaxConf();
		
		var siteName = $(row).find('input#siteName').val();
		var idSite = $(row).children('#idSite').html();
		var urls = $(row).find('textarea#urls').val();
		urls = getApiFormatUrls(urls);
		var excludedIps = $(row).find('textarea#excludedIps').val();
		excludedIps = getApiFormatTextarea(excludedIps);
		var excludedQueryParameters = $(row).find('textarea#excludedQueryParameters').val();
		excludedQueryParameters = getApiFormatTextarea(excludedQueryParameters);
		var timezone = encodeURIComponent($(row).find('#timezones option:selected').val());
		var currency = encodeURIComponent($(row).find('#currencies option:selected').val());
		var request = '';
		request += '&module=API';
		request += '&format=json';
		request += '&method=SitesManager.updateSite';
		siteName = encodeURIComponent(siteName);
		request += '&siteName='+siteName;
		request += '&idSite='+idSite;
		request += '&timezone='+timezone;
		request += '&currency='+currency;
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
		excludedIps = getApiFormatTextarea(excludedIps);
		var excludedQueryParameters = $('textarea#globalExcludedQueryParameters').val();
		excludedQueryParameters = getApiFormatTextarea(excludedQueryParameters);
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
		$(this).toggle();
		
		var numberOfRows = $('table#editSites')[0].rows.length;
		var newRowId = 'row' + numberOfRows;
	
		$(' <tr id="'+newRowId+'">\
				<td>&nbsp;</td>\
				<td><input id="name" value="Name" size="15" /></td>\
				<td><textarea cols="25" rows="3" id="urls">http://siteUrl.com/\nhttp://siteUrl2.com/</textarea><br />'+aliasUrlsHelp+'</td>\
				<td><textarea cols="20" rows="4" id="excludedIps"></textarea><br />'+excludedIpHelp+'</td>\
				<td><textarea cols="20" rows="4" id="excludedQueryParameters"></textarea><br />'+excludedQueryParametersHelp+'</td>\
				<td>'+getTimezoneSelector(defaultTimezone)+'<br />' + timezoneHelp + '</td>\
				<td>'+getCurrencySelector(defaultCurrency)+'<br />' + currencyHelp + '</td>\
				<td><img src="plugins/UsersManager/images/ok.png" class="addsite" href="#" title="' + _pk_translate('SitesManager_Save_js') + '" /></td>\
	  			<td><img src="plugins/UsersManager/images/remove.png" class="cancel" title="' + _pk_translate('SitesManager_Cancel_js') +'" /></td>\
	 		</tr>')
	  			.appendTo('#editSites')
		;
		
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
						var contentAfter = '<input id="'+idName+'" value="'+contentBefore+'" size="15" />';
						$(n)
							.html(contentAfter)
							.keypress( submitSiteOnEnter );
					}
					if(idName == 'urls')
					{
						var contentAfter = '<textarea cols="25" rows="3" id="urls">'+contentBefore.replace(/<br *\/? *>/gi,"\n")+'</textarea>';
						contentAfter += '<br />'+aliasUrlsHelp;
						$(n).html(contentAfter);
					}
					if(idName == 'excludedIps')
					{
						var contentAfter = '<textarea cols="20" rows="4" id="excludedIps">'+contentBefore.replace(/<br *\/? *>/gi,"\n")+'</textarea>';
						contentAfter += '<br />'+excludedIpHelp;
						$(n).html(contentAfter);
					}
					if(idName == 'excludedQueryParameters')
					{
						var contentAfter = '<textarea cols="20" rows="4" id="excludedQueryParameters">'+contentBefore.replace(/<br *\/? *>/gi,"\n")+'</textarea>';
						contentAfter += '<br />'+excludedQueryParametersHelp;
						$(n).html(contentAfter);
					}
					if(idName == 'timezone')
					{
						var contentAfter = getTimezoneSelector(contentBefore);
						contentAfter += '<br />' + timezoneHelp;
						$(n).html(contentAfter);
					}
					if(idName == 'currency')
					{
						var contentAfter = getCurrencySelector(contentBefore);
						contentAfter += '<br />' + currencyHelp;
						$(n).html(contentAfter);
					}
				}
			);
			$(this)
				.toggle()
				.parent()
				.prepend( $('<img src="plugins/UsersManager/images/ok.png" class="updateSite" title="' + _pk_translate('SitesManager_Save_js') + '" />')
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
			$(this).parent().find('.updateSite').click();
			$(this).find('.addsite').click();
		}
	}
}