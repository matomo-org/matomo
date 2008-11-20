function widgetize()
{
	var self = this;
	
	this.getInputFormWithHtml = function(inputId, htmlEmbed)
	{
		return '<input class="formEmbedCode" id="'+inputId+'" value=\''+ htmlEmbed +'\' onclick="javascript:document.getElementById(\''+inputId+'\').focus();document.getElementById(\''+inputId+'\').select();" readonly="true" type="text">';
	}
	
	this.getEmbedUrl = function( pluginId, actionId, exportFormat )
	{
		var sourceUrl;
		sourceUrl = document.location.protocol + '//' + document.location.hostname + document.location.pathname + '?';
		sourceUrl += "module=Widgetize&action="+exportFormat+"&moduleToWidgetize="+pluginId+"&actionToWidgetize="+actionId+"&idSite="+piwik.idSite+"&period="+piwik.period+"&date="+piwik.currentDateString;
		sourceUrl += "&disableLink=1";
		return sourceUrl;
	}
	
	this.callbackSavePluginName = function(pluginName, actionName, widgetName)
	{
		self.currentWidgetName = widgetName;
		self.callbackHideButtons();
	}
	
	this.callbackHideButtons = function()
	{
		$('#embedThisWidgetIframe, #embedThisWidgetFlash, #embedThisWidgetEverywhere').hide();
	}
	
	this.htmlentities = function(s)
	{
		return s.replace( /[<>&]/g, function(m) { return "&" + m.charCodeAt(0) + ";"; });
	}
	
	this.callbackAddExportButtonsUnderWidget = function (widget, pluginId, actionId)
	{
		var html = widget.html();
		
		// Div containing IFRAME code to load the widget
		var widgetIframe = '<div id="widgetIframe"><iframe width="100%" height="350" src="'
							+ self.getEmbedUrl(pluginId, actionId, "iframe") 
							+ '" scrolling="no" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>';
	
		// Iframe Export 
		$('#embedThisWidgetIframe')
			.show()
			.find('#embedThisWidgetIframeInput')
			.empty()
			.append(self.getInputFormWithHtml('iframeEmbed', widgetIframe));
	
	
		// Flash Export 
		widget.find('embed').each(function() {
			var htmlEmbed = $(this).parent().html();
			$('#embedThisWidgetFlash')
				.show()
				.find('#embedThisWidgetFlashInput')
				.empty()
				.append(self.getInputFormWithHtml('flashEmbed', htmlEmbed));
		});
		
		
		// Clearspring Export 
		$('#iframeDivToExport')
			.html(widgetIframe);

		$('#exportThisWidgetMenu').empty();
		$('#embedThisWidgetEverywhere').show();
		$Launchpad.ShowButton({
								actionElement : "exportThisWidget",
								targetElement : "exportThisWidgetMenu",
								userId : "4797da88692e4fe9",
								widgetName : self.currentWidgetName + " - Piwik",
								source : "iframeDivToExport"
							});

		// JS is buggy at least on IE
		//var widgetJS = '<script type="text/javascript" src="'+ getEmbedUrl(pluginId, actionId, "js") +'"></scr'+'ipt>';
		//divEmbedThisWidget.append('<br/>Embed JS: '+ getInputFormWithHtml('javascriptEmbed', widgetJS));
	}
}
