{assign var=showSitesSelection value=true}
{assign var=showPeriodSelection value=true}
{include file="CoreAdminHome/templates/header.tpl"}

{loadJavascriptTranslations plugins='Dashboard'}
<script type="text/javascript" src="plugins/Dashboard/templates/AddWidget.js"></script>

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
}

#periodString {
	margin-left:500px;
}
</style>
{/literal}
<script type="text/javascript">
	var piwik = new Object;
	piwik.availableWidgets = {$availableWidgets};
	piwik.idSite = "{$idSite}";
	piwik.period = "{$period}";
	piwik.currentDateStr = "{$date}";
	
{literal}
$(document).ready( function() {
	var menu = new widgetMenu();
	menu.init( callbackAddExportButtonsUnderWidget );
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
			<div class="widgetDiv previewDiv">Widget here
			</div>
		</div>
		<div id='embedThisWidget'></div>
	</div>
	
	<div class="menuClear"> </div>
</div>

See also <a href='?module=Widgetize&action=testClearspring'>test to <b>embed the widget on netvibes/ igoogle / apple dashboard / etc.</b></a></li>

</div>