{literal}
<script type="text/javascript" charset="utf-8">

$(document).ready(function() {
	initSpy();
});

function initSpy()
{
	if($('#_spyTmp').size() == 0) {
		$('#visitsLive > div:gt(2)').fadeEachDown(); // initial fade
		$('#visitsLive').spy({
			limit: 10,
			ajax: 'index.php?module=Live&idSite={/literal}{$idSite}{literal}&action=getLastVisitsStart',
			fadeLast: 2,
			isDupe: check_for_dupe,
			timeout: 8000,
			customParameterName: 'minIdVisit',
			customParameterValueCallback: lastIdVisit,
			fadeInSpeed: 600
		});
	}
}

//updates the numbers of total visits in startbox
function updateTotalVisits()
{
	$("#visitsTotal").load("index.php?module=Live&idSite={/literal}{$idSite}{literal}&action=ajaxTotalVisitors");
}
//updates the visit table, to refresh the already presented visitors pages
function updateVisitBox()
{
	$("#visitsLive").load("index.php?module=Live&idSite={/literal}{$idSite}{literal}&action=getLastVisitsStart");
}
</script>
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
	</tr>
	</thead>
	<tbody>
	<tr>
	<tr class="">
	<td class="columnodd">Today</td>
	<td class="columnodd">{$visitorsCountToday}</td>
	<td class="columnodd">{$pisToday}</td>
	</tr>
	<tr class="">
	<td class="columnodd">Last 30 minutes</td>
	<td class="columnodd">{$visitorsCountHalfHour}</td>
	<td class="columnodd">{$pisHalfhour}</td>
	</tr>
	</tbody>
	</table>
</div>

<div id='visitsLive'>
{$visitors}
</div>

<div class="visitsLiveFooter">
	<a href="javascript:void(0);" onclick="onClickPause();"><img id="pauseImage" border="0" src="plugins/Live/templates/images/pause_disabled.gif" /></a>
	<a href="javascript:void(0);" onclick="onClickPlay();"><img id="playImage" border="0" src="plugins/Live/templates/images/play.gif" /></a>
	&nbsp; <a class="rightLink" href="javascript:broadcast.propagateAjax('module=Live&action=getLastVisitsDetails')">{'Live_LinkVisitorLog'|translate}</a>
</div>
