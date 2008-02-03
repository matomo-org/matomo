<script type="text/javascript">
	{* define some global constants for the following javascript includes *}
	var piwik = new Object;
	
	{if isset($layout) }
		piwik.dashboardLayout = '{$layout}';
	{else}
		//Load default layout...
		piwik.dashboardLayout = 'Actions.getActions~Actions.getDownloads|UserCountry.getCountry~UserSettings.getPlugin|Referers.getSearchEngines~Referers.getKeywords';
	{/if}
	
	piwik.availableWidgets = {$availableWidgets};
	piwik.idSite = {$idSite};
	piwik.period = "{$period}";
	piwik.currentDateStr = "{$date}";
</script>

<script type="text/javascript" src="libs/jquery/jquery.blockUI.js"></script>

<script type="text/javascript" src="libs/jquery/ui.mouse.js"></script>
<script type="text/javascript" src="libs/jquery/ui.sortable_modif.js"></script>


<script type="text/javascript" src="plugins/Dashboard/templates/Dashboard.js"></script>


<link rel="stylesheet" href="libs/jquery/tooltip/jquery.tooltip.css">
<link rel="stylesheet" href="plugins/Home/templates/datatable.css">


{literal}

<style type="text/css">
*{
	font-family: Georgia;
}

/*Overriding some dataTable css for better dashboard display*/
.parentDiv {
	width: 100%;
}

.parentDivActions {
	width: 100%;
}

table.dataTable {
	width: 100%;
}	
#dataTableFeatures {
	width: 100%;
}
/*--- end of dataTable.css modif*/

.col {
	float:left;
	width: 33%;
}

.hover {
	border: 2px dashed rgb(200,200,200);
}

.items {
    background: white;
}

.widget {
    border: 1px solid rgb(230,230,230);
    margin-top: 10px;
    margin-bottom: 10px;
    margin-right: 5px;
    margin-left: 5px;
}

.widgetHover {
	border: 1px solid rgb(200, 200, 200);
}

.handle {
	background: rgb(240,240,250);
	width: 100%;
	height: 20px;
	cursor: move;
	font-size: 10pt;
	font-weight: bold;
}

.widgetTitle {
	width: 80%;
	float: left;
}

.handleHover {
	background: rgb(200,200,230);
}

.widgetDiv {
	display: none;
}

.dummyItem {
	width: 100%;
	height: 1px;
	display: block;
}

.button {
	cursor: pointer;
}

#close.button {
	float: right;
	display: none;
}

.dialog {
	display: none;
}

.menu {
	display: none;    
	border: 3px solid rgb(230,230,230);
}

.helper {
	width: 33%;
	opacity: .6;
	filter : alpha(opacity=60); /*for IE*/
}

.dummyHandle {
	display: none;
}

.menuItem {
}

.menuSelected {
	border: 1px dotted;
	background: rgb(200,200,230);
}

.widgetLoading {
	cursor: wait;
	
}


.subMenu1 {
	float:left;
	width: 15%;
	cursor: pointer;
}
.subMenu2 {
	float:left;
	width: 40%;
	cursor: pointer;
}
.subMenu3 {
	float:left;
	clear: both;
}

</style>

{/literal}


<div class="sortDiv">
 
	<div class="dialog" id="confirm"> 
	        <h2>Are you sure you want to delete this widget from your dashboard ?</h2> 
	        <input type="button" id="yes" value="Yes" /> 
	        <input type="button" id="no" value="No" /> 
	</div> 

	<div class="button" id="addWidget">
		Add a widget...
	</div>
	
	<div class="menu" id="widgetChooser">
		<div class="subMenu1">
		</div>
		
		<div class="subMenu2">
		</div>
		
		<div class="subMenu3">
		</div>
	</div>

	<div class="col" id="1">
	</div>
  
	<div class="col" id="2">
	</div>
	
	<div class="col" id="3">
	</div>
</div>
