{loadJavascriptTranslations plugins='CoreHome Dashboard'}

<script type="text/javascript">
	piwik.dashboardLayout = {$layout};
	{*
	the old dashboard layout style is:
	piwik.dashboardLayout = 'VisitsSummary.getEvolutionGraph~VisitorInterest.getNumberOfVisitsPerVisitDuration~UserSettings.getBrowser~ExampleFeedburner.feedburner|Referers.getKeywords~Referers.getWebsites|Referers.getSearchEngines~VisitTime.getVisitInformationPerServerTime~ExampleRssWidget.rssPiwik|';
	*}
	piwik.availableWidgets = {$availableWidgets};
</script>

{literal}
<script type="text/javascript">
$(document).ready( function() {
        // Standard dashboard
		if($('#periodString').length) 
		{
		$('#addWidget').css({left:$('#periodString')[0].offsetWidth+25});
        }
		// Embed dashboard
		else 
		{
        	$('#addWidget').css({left:7, top:42});
        }
		piwik.dashboardObject = new dashboard();
		var widgetMenuObject = new widgetMenu(piwik.dashboardObject);
		piwik.dashboardObject.init(piwik.dashboardLayout);
		widgetMenuObject.init();
		$('#addWidget .widget_button').click(function(e){widgetMenuObject.toggle(e);});
		//$('#addWidget').mouseout(function(){});
});
</script>
{/literal}
<div id="dashboard">
 
	<div class="dialog" id="confirm">
	        <h2>{'Dashboard_DeleteWidgetConfirm'|translate}</h2>
			<input id="yes" type="button" value="{'General_Yes'|translate}" />
			<input id="no" type="button" value="{'General_No'|translate}" />
	</div> 
	
	<div id="addWidget">
		<div class="widget_button">{'Dashboard_AddWidget'|translate} <img height="16" width="16" class="arr" src="themes/default/images/sortdesc.png"></div>
	
		<div class="menu" id="widgetChooser">	
			<div class="subMenu" id="sub1"></div>
			<div class="subMenu" id="sub2"></div>
			<div class="subMenu" id="sub3"></div>
			<div class="menuClear"> </div>
		</div>	
	</div>
	
	<div class="clear"></div>
	
	<div id="dashboardWidgetsArea">
		<div class="col" id="1"></div>
		<div class="col" id="2"></div>
		<div class="col" id="3"></div>
		<div class="clear"></div>
	</div>
</div>
