{literal}
<script type="text/javascript" charset="utf-8">

$(document).ready(function() {
	initSpy();
});

function initSpy()
{
	if($('#_spyTmp').size() == 0) {
		//$('#visitsLive > div:gt(2)').fadeEachDown(); // initial fade
		$('#visitsLive').spy({
			limit: 10,
			ajax: 'index.php?module=Live&idSite={/literal}{$idSite}{if !empty($liveTokenAuth)}&token_auth={$liveTokenAuth}{/if}{literal}&action=getLastVisitsStart',
			fadeLast: 2,
			isDupe: check_for_dupe,
			timeout: {/literal}{$liveRefreshAfterMs}{literal},
			customParameterName: 'minTimestamp',
			customParameterValueCallback: lastMaxTimestamp,
			fadeInSpeed: 600,
			appendTo: 'div#content'
		});
	}
}

//updates the numbers of total visits in startbox
function updateTotalVisits()
{
	$("#visitsTotal").load("index.php?module=Live&idSite={/literal}{$idSite}{if !empty($liveTokenAuth)}&token_auth={$liveTokenAuth}{/if}{literal}&action=ajaxTotalVisitors");
}
</script>
{/literal}

{include file="Live/templates/totalVisits.tpl"}

<div id='visitsLive'>
{$visitors}
</div>

<div class="visitsLiveFooter">
	<a title="Pause Live!" href="javascript:void(0);" onclick="onClickPause();"><img id="pauseImage" border="0" src="plugins/Live/templates/images/pause_disabled.gif" /></a>
	<a title="Start Live!" href="javascript:void(0);" onclick="onClickPlay();"><img id="playImage" border="0" src="plugins/Live/templates/images/play.gif" /></a>
	{if !$disableLink}
		&nbsp; <a class="rightLink" href="javascript:piwik.dashboardObject.closeWidgetDialog();broadcast.propagateAjax('module=Live&action=getVisitorLog')">{'Live_LinkVisitorLog'|translate}</a>
	{/if}
</div>
