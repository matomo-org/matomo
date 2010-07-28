{assign var=showSitesSelection value=true}
{assign var=showPeriodSelection value=true}
{include file="CoreHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='Dashboard'}


{literal}
<style>
.widgetize{ 
	width:980px; 
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
	piwik.availableWidgets = {$availableWidgets};
{literal}
$(document).ready( function() {
	var menu = new widgetMenu();
	var widgetized = new widgetize();
	menu.init();
	menu.registerCallbackOnWidgetLoad( widgetized.callbackAddExportButtonsUnderWidget );
	menu.registerCallbackOnMenuHover( widgetized.deleteEmbedElements );
	menu.show();
	var dashboardUrl = document.location.protocol + '//' + document.location.hostname + document.location.pathname + '?module=Widgetize&action=iframe&moduleToWidgetize=Dashboard&actionToWidgetize=index&idSite=1&period=week&date=yesterday';
	$('#exportFullDashboard').html(
		widgetized.getInputFormWithHtml( 'dashboardEmbed', '<iframe src="'+ dashboardUrl +'" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="100%"></iframe>')
	);
	$('#linkDashboardUrl').attr('href',dashboardUrl); 
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
	<p>	<b>&rsaquo; Select a report, and copy paste in your page the embed code below the widget:</b>
	<div id="widgetChooser" style='height:600px'>
		<div class="subMenu" id="sub1"></div>
		<div class="subMenu" id="sub2"></div>
		<div class="subMenu" id="sub3"></div>
		<div class="menuClear"></div>
	</div>
	<div id='iframeDivToExport' style='display:none;'></div>
</div>

{include file="CoreHome/templates/piwik_tag.tpl"}
