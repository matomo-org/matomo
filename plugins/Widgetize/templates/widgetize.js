function widgetize()
{
	var self = this;
	
	this.getInputFormWithHtml = function(inputId, htmlEmbed)
	{
		return '<input class="formEmbedCode" id="'+inputId+'" value="'+ htmlEmbed.replace(/"/g, '&quot;') +'" onclick="javascript:document.getElementById(\''+inputId+'\').focus();document.getElementById(\''+inputId+'\').select();" readonly="true" type="text">';
	}
	
	this.getEmbedUrl = function( parameters, exportFormat )
	{
		copyParameters = {};
		for(var variableName in parameters) {
			copyParameters[variableName] = parameters[variableName];
		}
		copyParameters['moduleToWidgetize'] = parameters['module'];
		copyParameters['actionToWidgetize'] = parameters['action'];
		delete copyParameters['action'];
		delete copyParameters['module'];
		var sourceUrl;
		sourceUrl = document.location.protocol + '//' + document.location.hostname + document.location.pathname + '?';
		sourceUrl += 	"module=Widgetize" +
						"&action="+exportFormat+
						"&"+piwikHelper.getQueryStringFromParameters(copyParameters)+
						"&idSite="+piwik.idSite+
						"&period="+piwik.period+
						"&date="+piwik.currentDateString+
						"&disableLink=1";
		return sourceUrl;
	}
	
	this.deleteEmbedElements = function()
	{
		$('#exportButtons').remove();
	}
	
	this.htmlentities = function(s)
	{
		return s.replace( /[<>&]/g, function(m) { return "&" + m.charCodeAt(0) + ";"; });
	}
	
	this.callbackAddExportButtonsUnderWidget = function (	widgetUniqueId, 
															loadedWidgetElement)
	{
		widget = widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId);
		widgetName = widget["name"];
		widgetParameters = widget['parameters'];
	
		self.deleteEmbedElements();
		var exportButtonsElement = $('<span id="exportButtons">');

		// We first build the HTML code that will load the widget in an IFRAME
		var widgetIframeHtml = '<div id="widgetIframe">'+
								'<iframe width="100%" height="350" src="'+
									self.getEmbedUrl(widgetParameters, "iframe")+ 
									'" scrolling="no" frameborder="0" marginheight="0" marginwidth="0">'+
								'</iframe>'+
							'</div>';

		// Add the input field containing the widget in an Iframe 
		$(exportButtonsElement).append(
			'<div id="embedThisWidgetIframe">'+
				'<label for="embedThisWidgetIframeInput">&rsaquo; Embed Iframe</label>'+
				'<span id="embedThisWidgetIframeInput">'+
					self.getInputFormWithHtml('iframeEmbed', widgetIframeHtml)+
				'</span>'+
			'</div>'
		);
		
		if(false) {
			// Add the Flash Export if a flash <embed> is found in the widget 
			$(loadedWidgetElement)
				.find('embed,object')
				.each(function() {
					var htmlEmbed = $(this).parent().html();
	
					htmlEmbed = htmlEmbed.replace(/ (data=")/, ' $1' + unescape(piwik.piwik_url));
					htmlEmbed = htmlEmbed.replace(/ (value=")x-(data-file=)/, ' $1$2' + piwik.piwik_url + 'index.php');
	
					$(exportButtonsElement).append(
						'<div id="embedThisWidgetFlash">'+
							'<label for="embedThisWidgetFlashInput">&rsaquo; Embed Flash</label>'+
							'<span id="embedThisWidgetFlashInput">'+
								self.getInputFormWithHtml('flashEmbed', htmlEmbed) +
							'</span>'+
						'</div>'
					);
				});
		}
		// 0.5: Removing launchpad feature as it doesn't seem to work well despite us contacting Clearspring
		if(false) {
			$(exportButtonsElement).append(
				'<div id="embedThisWidgetEverywhere">'+
					'<div id="exportThisWidget">'+
						'<label for="flashEmbed">&rsaquo; Export anywhere!</label>'+
						'<img src="http://cdn.clearspring.com/launchpad/static/cs_button_share1.gif">'+
					'</div>'+
					'<div id="exportThisWidgetMenu"></div>'+
				'</div>'
			);
			// Call clearspring
			$Launchpad.ShowButton({
									actionElement : "exportThisWidget",
									targetElement : "exportThisWidgetMenu",
									userId : "4797da88692e4fe9",
									widgetName : widgetName + " - Piwik",
									source : "iframeDivToExport"
			});
		}

		// We then replace the div iframeDivToExport with the actual Iframe html
		// Clearspring will then build a widget that has the same html as this div
		$('#iframeDivToExport')
			.html(widgetIframeHtml);

		// Finally we append the content to the parent widget DIV 
		$(loadedWidgetElement)
			.parent()
			.append(exportButtonsElement);
		
		// JS is buggy at least on IE
		//var widgetJS = '<script type="text/javascript" src="'+ getEmbedUrl(pluginId, actionId, "js") +'"></scr'+'ipt>';
		//divEmbedThisWidget.append('<br/>Embed JS: '+ getInputFormWithHtml('javascriptEmbed', widgetJS));
	}
}
