<script type="text/javascript">
	{* define some global constants for the following javascript includes *}
	var piwik = new Object;
	
	{if !empty($layout) }
		piwik.dashboardLayout = '{$layout}';
	{else}
		//Load default layout...
		piwik.dashboardLayout = 'VisitsSummary.getLastVisitsGraph~VisitorInterest.getNumberOfVisitsPerVisitDuration~UserSettings.getBrowser|Referers.getKeywords|Referers.getSearchEngines~VisitTime.getVisitInformationPerServerTime~ExamplePlugin.feedburner|';
	{/if}
	
	piwik.availableWidgets = {$availableWidgets};
	piwik.idSite = {$idSite};
	piwik.period = "{$period}";
	piwik.currentDateStr = "{$date}";
</script>

<script type="text/javascript" src="plugins/Dashboard/templates/Dashboard.js"></script>


<div id="dashboard">
 
	<div class="dialog" id="confirm"> 
	        <img src="themes/default/images/delete.png" style="padding: 10px; position: relative; margin-top: 10%; float: left;"/>
	        <p>Are you sure you want to delete this widget from the dashboard ?</p>
			<input id="yes" type="button" value="Yes"/>
			<input id="no" type="button" value="No"/>
	</div> 

	<div class="button" id="addWidget">
		Add a widget...
	</div>
	
	<div class="menu" id="widgetChooser">
		<div id="closeMenuIcon"><img src="themes/default/images/close_medium.png" title="Close this menu"/></div>
		<div id="menuTitleBar">Select the widget to add in the dashboard</div>
		<div class="subMenu" id="sub1">
		</div>
		
		<div class="subMenu" id="sub2">
		</div>
		
		<div class="subMenu" id="sub3">
			<div class="widget">
				<div class="handle" title="Add previewed widget to the dashboard">
					<div class="button" id="close">
						<img src="themes/default/images/close.png" />
					</div>
					<div class="widgetTitle">Widget preview</div>
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
