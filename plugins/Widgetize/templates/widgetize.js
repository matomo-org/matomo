/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function widgetize()
{
	var self = this;
	
	this.getInputFormWithHtml = function(inputId, htmlEmbed)
	{
		return '<input class="formEmbedCode" id="'+inputId+'" value="'+ htmlEmbed.replace(/"/g, '&quot;') +'" onclick="javascript:document.getElementById(\''+inputId+'\').focus();document.getElementById(\''+inputId+'\').select();" readonly="true" type="text" />';
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
		sourceUrl = document.location.protocol + '//' + document.location.hostname + (document.location.port == '' ? '' : (':' + document.location.port)) + document.location.pathname + '?';
		sourceUrl += 	"module=Widgetize" +
						"&action="+exportFormat+
						"&"+piwikHelper.getQueryStringFromParameters(copyParameters)+
						"&idSite="+piwik.idSite+
						"&period="+piwik.period+
						"&date="+broadcast.getValueFromUrl('date')+
						"&disableLink=1&widget=1";
		return sourceUrl;
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
	
		var exportButtonsElement = $('<span id="exportButtons">');

		var urlIframe = self.getEmbedUrl(widgetParameters, "iframe");
		// We first build the HTML code that will load the widget in an IFRAME
		var widgetIframeHtml = '<div id="widgetIframe">'+
								'<iframe width="100%" height="350" src="'+
									urlIframe + 
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
			'</div>' +
			'<div> <label for="embedThisWidgetDirectLink">&rsaquo; Direct Link</label>'+
					'<span id="embedThisWidgetDirectLink"> '+self.getInputFormWithHtml('directLinkEmbed', urlIframe)+' - <a href="'+urlIframe+'" target="_blank">'+_pk_translate('General_OpenInNewWindow_js')+'</a></span>'
					+'</div>'
		);
		
		// We then replace the div iframeDivToExport with the actual Iframe html
		$('#iframeDivToExport')
			.html(widgetIframeHtml);

		// Finally we append the content to the parent widget DIV 
		$(loadedWidgetElement)
			.parent()
			.append(exportButtonsElement);
		
		// JS is buggy at least on IE
		//var widgetJS = '<script type="text/javascript" src="'+ getEmbedUrl(pluginId, actionId, "js") +'"></scr'+'ipt>';
		//divEmbedThisWidget.append('<br />Embed JS: '+ getInputFormWithHtml('javascriptEmbed', widgetJS));
	}
}
