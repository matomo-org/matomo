{literal}
<script type="text/javascript" src="plugins/Live/templates/scripts/spy.js"></script>

<script type="text/javascript" charset="utf-8">

 
	$(document).ready(function() { 
		if($('#_spyTmp').size() == 0) {
			$('#visitsLive > div:gt(2)').fadeEachDown(); // initial fade
			$('#visitsLive').spy({ 
				limit: 10, 
				ajax: 'index.php?module=Live&idSite={/literal}{$idSite}{literal}&action=getLastVisitsStart', 
				fadeLast: 2, 
				isDupes : check_for_dupe,
				timeout: 20000,
				customParameterName: 'minIdVisit', 
				customParameterValueCallback: lastIdVisit,
				fadeInSpeed: 1400
			});
		}
	});
		
	// first I'm ensuring that 'last' has been initialised (with last.constructor == Object),
	// then prev.html() == last.html() will return true if the HTML is the same, or false,
	// if I have a different entry.
	function check_for_dupe(prev, last)
	{
		
		if (last.constructor == Object)	{
			return (prev.html() == last.html());
		}	
		else {
			return 0;
			
		}	
	}

	function lastIdVisit()
	{
		updateTotalVisits();
		updateVisitBox();
		return $('#visitsLive > div:lt(2) .idvisit').html();
	}

	var pauseImage = "plugins/Live/templates/images/pause.gif";
	var pauseDisabledImage = "plugins/Live/templates/images/pause_disabled.gif";
	var playImage = "plugins/Live/templates/images/play.gif";
	var playDisabledImage = "plugins/Live/templates/images/play_disabled.gif";
	
	function onClickPause()
	{
		$('#pauseImage').attr('src', pauseImage);
		$('#playImage').attr('src', playDisabledImage);
		return pauseSpy();
	}
	function onClickPlay()
	{
		$('#playImage').attr('src', playImage);
		$('#pauseImage').attr('src', pauseDisabledImage);
		return playSpy();
	}
	
	// updates the numbers of total visits in startbox
	function updateTotalVisits()
	{
		$("#visitsTotal").load("index.php?module=Live&idSite={/literal}{$idSite}{literal}&action=ajaxTotalVisitors");
	}
	
	// updates the visit table, to refresh the already presented visotors pages
	function updateVisitBox()
	{
		$("#visitsLive").load("index.php?module=Live&idSite={/literal}{$idSite}{literal}&action=getLastVisitsStart");
	}

</script>

<style>
#visitsLive {
	text-align:left;
	font-size:90%;
}
#visitsLive .datetime, #visitsLive .country, #visitsLive .referer, #visitsLive .settings, #visitsLive .returning , #visitsLive .countActions{
	border-bottom:1px solid #C1DAD7;
	border-right:1px solid #C1DAD7;
	padding:5px 5px 5px 12px;	
}

#visitsLive .datetime {
	background:#D4E3ED url(plugins/CoreHome/templates/images/bg_header.jpg) repeat-x scroll 0 0;
	border-top:1px solid #C1DAD7;
	color:#6D929B;
	margin:0;
	text-align:left;
}

#visitsLive .country {
	color:#4F6B72;
	background:#FFFFFF url(plugins/CoreHome/templates/images/bullet1.gif) no-repeat scroll 0 0;
}

#visitsLive .referer {
	background:#F9FAFA none repeat scroll 0 0;
	color:#797268;
}

#visitsLive .pagesTitle {
	display:block;
	float:left;
	padding-top: 3px;
}

#visitsLive .countActions {
	background:#FFFFFF none repeat scroll 0 0;
	color:#4F6B72;
}

#visitsLive .settings {
	background:#FFFFFF none repeat scroll 0 0;
	color:#4F6B72;
}

#visitsLive .returning {
	background:#F9FAFA none repeat scroll 0 0;
	color:#797268;
}

#visitsLive .visit {
}

#visitsLive .alt {
}

#visitsLive .actions {
	background:#F9FAFA none repeat scroll 0 0;
	color:#797268;
	padding:0px 5px 0px 12px;
}

</style>
{/literal}

<div id="visitsTotal">
	<table class="dataTable" cellspacing="0">
	<thead>
	<tr>
	<th id="label" class="sortable label" style="cursor: auto;">
	<div id="thDIV">Period<div></th>
	<th id="label" class="sortable label" style="cursor: auto;">
	<div id="thDIV">Visits<div></th>
	<th id="label" class="sortable label" style="cursor: auto;">
	<div id="thDIV">PageViews<div></th>
	<thead>
	<tr>
	<tr class="subDataTable">
	<td class="columnodd">Today</td>
	<td class="columnodd">{$visitorsCountToday}</td>
	<td class="columnodd">{$pisToday}</td>
	</tr>
	<tr class="subDataTable">
	<td class="columnodd">Last 30 minutes</td>
	<td class="columnodd">{$visitorsCountHalfHour}</td>
	<td class="columnodd">{$pisHalfhour}</td>
	</tr>
	</table>
</div>

<div id='visitsLive'>
{$visitors}
</div>

<div>
	<a href="javascript:void(0);" onclick="onClickPause();"><img id="pauseImage" border="0" src="plugins/Live/templates/images/pause_disabled.gif"></a> 
	<a href="javascript:void(0);" onclick="onClickPlay();"><img id="playImage" border="0" src="plugins/Live/templates/images/play.gif"></a>
</div>
