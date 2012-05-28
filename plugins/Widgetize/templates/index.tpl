{assign var=showSitesSelection value=true}
{assign var=showPeriodSelection value=true}
{include file="CoreHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='Dashboard'}


{literal}
<style type="text/css">
.widgetize{ 
	width:100%; 
	padding:15px 15px 0 15px; 
	font-size:13px; 
}
.widgetize p{ 
	padding: 0 0 20px 0; 
}
.menu {
	display: inline;
}
.widgetize .formEmbedCode{
	font-size: 11px;
	text-decoration: none;
	background-color: #FBFDFF;
	border: 1px solid #ECECEC;
	width:220px;
}

#periodString {
	margin-left:15px;
}

.widgetize label {
	color:#666666;
	line-height:18px;
	margin-right:5px;
	font-weight:bold;
	padding-bottom:100px;
}

#embedThisWidgetIframe,
#embedThisWidgetFlash,
#embedThisWidgetEverywhere {
	margin-top:5px;
}

.menuSelected{
	font-weight:bold;
}
</style>
{/literal}
<script type="text/javascript">
{literal}
$(document).ready( function() {
	var widgetized = new widgetize();
	var urlPath = document.location.protocol + '//' + document.location.hostname + (document.location.port == '' ? '' : (':' + document.location.port)) + document.location.pathname ;
	var dashboardUrl = urlPath + '?module=Widgetize&action=iframe&moduleToWidgetize=Dashboard&actionToWidgetize=index&idSite='+piwik.idSite+'&period=week&date=yesterday';
	$('#exportFullDashboard').html(
		widgetized.getInputFormWithHtml( 'dashboardEmbed', '<iframe src="'+ dashboardUrl +'" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="100%"></iframe>')
	);
	$('#linkDashboardUrl').attr('href',dashboardUrl);
	
	var allWebsitesDashboardUrl = urlPath + '?module=Widgetize&action=iframe&moduleToWidgetize=MultiSites&actionToWidgetize=standalone&idSite='+piwik.idSite+'&period=week&date=yesterday';
	$('#exportAllWebsitesDashboard').html(
		widgetized.getInputFormWithHtml( 'allWebsitesDashboardEmbed', '<iframe src="'+ allWebsitesDashboardUrl +'" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="100%"></iframe>')
	);
	$('#linkAllWebsitesDashboardUrl').attr('href',allWebsitesDashboardUrl);
    $('#widgetPreview').widgetPreview({
        onPreviewLoaded: widgetized.callbackAddExportButtonsUnderWidget
    });
});

{/literal}
</script>

<div class="top_controls_inner">
    {include file="CoreHome/templates/period_select.tpl"}
</div>

<div class="widgetize">
	<p>With Piwik, you can export your Web Analytics reports on your blog, website, or intranet dashboard... in one click. 
	<p><b>&rsaquo; Widget authentication:</b> If you want your widgets to be viewable by everybody, you first have to set the 'view' permissions 
	to the anonymous user in the <a href='index.php?module=UsersManager' target='_blank'>Users Management section</a>. 
	<br />Alternatively, if you are publishing widgets on a password protected or private page, 
	you don't necessarily have to allow 'anonymous' to view your reports. In this case, you can add the secret token_auth parameter (found in the <a href='{url module=API action=listAllAPI}' target='_blank'>API page</a>) in the widget URL. 
	</p>
	<p><b>&rsaquo; Widgetize the full dashboard:</b> You can also display the full Piwik dashboard in your application or website in an IFRAME (<a href='' target='_blank' id='linkDashboardUrl'>see example</a>). 
    The date parameter can be set to a specific calendar date, "today", or "yesterday".  The period parameter can be set to "day", "week", "month", or "year".
    The language parameter can be set to the language code of a translation, such as language=fr.
	For example, for idSite=1 and date=yesterday, you can write: <span id='exportFullDashboard'></span>
	</p>
	<p><b>&rsaquo; Widgetize the all websites dashboard in an IFRAME</b> (<a href='' target='_blank' id='linkAllWebsitesDashboardUrl'>see example</a>)  <span id='exportAllWebsitesDashboard'></span>
	</p>
	<p>	<b>&rsaquo; Select a report, and copy paste in your page the embed code below the widget:</b>
    
    <div id="widgetPreview"></div>
    
	<div id='iframeDivToExport' style='display:none;'></div>
</div>

{include file="CoreHome/templates/piwik_tag.tpl"}
