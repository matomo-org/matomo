{assign var=showSitesSelection value=true}
{assign var=showPeriodSelection value=true}
{include file="CoreAdminHome/templates/header.tpl"}

{loadJavascriptTranslations plugins='Dashboard'}
<script type="text/javascript" src="plugins/Dashboard/templates/widgetMenu.js"></script>

<script type="text/javascript" src="themes/default/common.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.dimensions.js"></script>
<script type="text/javascript" src="libs/jquery/tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="libs/jquery/truncate/jquery.truncate.js"></script>
<script type="text/javascript" src="libs/jquery/jquery.scrollTo.js"></script>
<script type="text/javascript" src="libs/swfobject/swfobject.js"></script>

<script type="text/javascript" src="plugins/CoreHome/templates/datatable.js"></script>
<script type="text/javascript" src="libs/jquery/ui.mouse.js"></script>
<script type="text/javascript" src="libs/jquery/ui.sortable_modif.js"></script>

<link rel="stylesheet" href="plugins/CoreHome/templates/datatable.css">
<link rel="stylesheet" href="plugins/Dashboard/templates/dashboard.css">
<script type="text/javascript" src="plugins/Widgetize/templates/widgetize.js"></script>

{*<script type="text/javascript" src="http://widgets.clearspring.com/launchpad/include.js"></script>
*}
<script src="http://cdn.clearspring.com/launchpad/v2/standalone.js" type="text/javascript"></script>
{literal}
<style>
.menu {
	display: inline;
}
.formEmbedCode{
	font-size: 11px;
	text-decoration: none;
	background-color: #FBFDFF;
	border: 1px solid #ECECEC;
	width:220px;
}

#periodString {
	margin-left:500px;
}

label {
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
	widgetized.callbackHideButtons();
	menu.init();
	menu.registerCallbackOnWidgetLoad( widgetized.callbackAddExportButtonsUnderWidget );
	menu.registerCallbackOnMainMenuHover( widgetized.callbackHideButtons );
	menu.registerCallbackOnSubMenuHover( widgetized.callbackSavePluginName );
	menu.show();
});

{/literal}
</script>

<div style="max-width:980px;">
<p>With Piwik, you can export your Web Analytics reports on your blog, website, or intranet dashboard... in one click. 
If you want your widgets to be viewable by everybody, you first have to set the 'view' permissions to the anonymous user in the <a href='?module=UsersManager'>Users Management section</a>.</p>
<div id="widgetChooser">
	<div class="subMenu" id="sub1">
	</div>
	<div class="subMenu" id="sub2">
	</div>
	<div class="subMenu" id="sub3">
		<div class="widget">
			<div class="widgetDiv previewDiv"></div>
		</div>

		<div id="embedThisWidgetIframe">
			<label for="embedThisWidgetIframeInput">&rsaquo; Embed Iframe</label>
			<span id="embedThisWidgetIframeInput"></span>
		</div>
		
		<div id="embedThisWidgetFlash">
			<label for="embedThisWidgetFlashInput">&rsaquo; Embed Flash</label>
			<span id="embedThisWidgetFlashInput"></span>
		</div>
		
		<div id="embedThisWidgetEverywhere">
			<div id="exportThisWidget">
				<label for="flashEmbed">&rsaquo; Export anywhere!</label>
				<img src='http://cdn.clearspring.com/launchpad/static/cs_button_share1.gif'>
			</div>
			<div id="exportThisWidgettest"></div>
			<div id="exportThisWidgetMenu">
				<span style="display:none"><img src="{$piwikUrl}themes/default/images/loading-blue.gif" /></span>
			</div>
		</div>
	</div>
	
	<div class="menuClear"> </div>
</div>
<div id='iframeDivToExport' style='display:none;'></div>
</div>
