{loadJavascriptTranslations modules='Home Dashboard'}

<script type="text/javascript">
	{* define some global constants for the following javascript includes *}
	var piwik = new Object;
	
	{if !empty($layout) }
		piwik.dashboardLayout = '{$layout}';
	{else}
		//Load default layout...
		piwik.dashboardLayout = 'VisitsSummary.getLastVisitsGraph~VisitorInterest.getNumberOfVisitsPerVisitDuration~UserSettings.getBrowser|Referers.getKeywords~Referers.getWebsites|Referers.getSearchEngines~VisitTime.getVisitInformationPerServerTime~ExamplePlugin.feedburner|';
	{/if}
	
	piwik.availableWidgets = {$availableWidgets};
	piwik.idSite = "{$idSite}";
	piwik.period = "{$period}";
	piwik.currentDateStr = "{$date}";
</script>

<script type="text/javascript" src="plugins/Dashboard/templates/Dashboard.js"></script>


<div id="dashboard">
 
	<div class="dialog" id="confirm"> 
	        <img src="themes/default/images/delete.png" style="padding: 10px; position: relative; margin-top: 10%; float: left;"/>
	        <p>{'Dashboard_DeleteWidgetConfirm'|translate}</p>
			<input id="yes" type="button" value="{'General_Yes'|translate}"/>
			<input id="no" type="button" value="{'General_No'|translate}"/>
	</div> 

	<div class="button" id="addWidget">
		{'Dashboard_AddWidget'|translate}
	</div>
	
	<div class="menu" id="widgetChooser">
		<div id="closeMenuIcon"><img src="themes/default/images/close_medium.png" title="{'General_Close'|translate}"/></div>
		<div id="menuTitleBar">{'Dashboard_SelectWidget'|translate}</div>
		<div class="subMenu" id="sub1">
		</div>
		
		<div class="subMenu" id="sub2">
		</div>
		
		<div class="subMenu" id="sub3">
			<div class="widget">
				<div class="handle" title="{'Dashboard_AddPreviewedWidget'|translate}">
					<div class="button" id="close">
						<img src="themes/default/images/close.png" title="{'General_Close'|translate}"/>
					</div>
					<div class="widgetTitle">{'Dashboard_WidgetPreview'|translate}</div>
				</div>
				<div class="widgetDiv previewDiv"></div>
			</div>
		</div>
		
		<div class="menuClear"> </div>
	</div>	

	<div id="dashboardWidgetsArea">
		<div class="col" id="1">
		</div>
	  
		<div class="col" id="2">
		</div>
		
		<div class="col" id="3">
		</div>
	</div>
</div>
