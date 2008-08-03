function callbackAddExportButtonsUnderWidget(widget, pluginId, actionId)
{
	var divEmbedThisWidget = $(document).find('#embedThisWidget');
	divEmbedThisWidget.empty();
		
	var html = widget.html();
	widget.find('embed').each(function() {
		var htmlEmbed = $(this).parent().html();
		var htmlEmbed = 'Embed Flash: ' + getInputFormWithHtml('flashEmbed', htmlEmbed);
		divEmbedThisWidget.append(htmlEmbed);	
	});
	var clearspringHtml = '<script type="text/javascript">\
			$Launchpad.ShowButton({targetElement: "clearspringButton", userId: "4797da88692e4fe9", servicesInclude: ["google", "facebook", "live", "spaces", "netvibes", "email", "yahoowidgets", "dashboard", "vista", "jscode", "objectcode"], customCSS: "http://cdn.clearspring.com/launchpad/skins/white.css", widgetName: "Piwik example", source: "widgetIframe"});\
			</script>';

	var widgetIframe = '<div id="widgetIframe"><iframe width="500" height="350" src="'+ getEmbedUrl(pluginId, actionId, "iframe") +'" scrolling="no" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>';
	divEmbedThisWidget.append('<br/>Embed Iframe: '+ getInputFormWithHtml('iframeEmbed', widgetIframe));

	var widgetJS = '<script type="text/javascript" src="'+ getEmbedUrl(pluginId, actionId, "js") +'"></scr'+'ipt>';
	divEmbedThisWidget.append('<br/>Embed JS: '+ getInputFormWithHtml('javascriptEmbed', widgetJS));
}
function getEmbedUrl( pluginId, actionId, exportFormat )
{
	var sourceUrl;
	sourceUrl = document.location.protocol + '//' + document.location.hostname + document.location.pathname + '?';
	sourceUrl += "module=Widgetize&action="+exportFormat+"&moduleToWidgetize="+pluginId+"&actionToWidgetize="+actionId+"&idSite="+piwik.idSite+"&period="+piwik.period+"&date="+piwik.currentDateStr;
	return sourceUrl;
}
function getInputFormWithHtml(idInput, htmlEmbed)
{
	return '<input size=20 class="formEmbedCode" id="'+idInput+'" value=\''+ htmlEmbed +'\' onclick="javascript:document.getElementById(\''+idInput+'\').focus();document.getElementById(\''+idInput+'\').select();" readonly="true" type="text">';
}

function htmlentities(s)
{
	return s.replace( /[<>&]/g, function(m) { return "&" + m.charCodeAt(0) + ";"; });
}
