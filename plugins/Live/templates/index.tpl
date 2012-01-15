{literal}
<script type="text/javascript" charset="utf-8">
$(document).ready(function() {
    $('#visitsLive').liveWidget({
        interval: {/literal}{$liveRefreshAfterMs}{literal},
        onUpdate: function(){
                      //updates the numbers of total visits in startbox
                      $("#visitsTotal").load("index.php?module=Live&idSite={/literal}{$idSite}{if !empty($liveTokenAuth)}&token_auth={$liveTokenAuth}{/if}{literal}&action=ajaxTotalVisitors");
                  },
        maxRows: 10,
        fadeInSpeed: 600,
        dataUrl: 'index.php?module=Live&idSite={/literal}{$idSite}{if !empty($liveTokenAuth)}&token_auth={$liveTokenAuth}{/if}{literal}&action=getLastVisitsStart'
    });
});
</script>
{/literal}

{include file="Live/templates/totalVisits.tpl"}

{$visitors}

<div class="visitsLiveFooter">
	<a title="Pause Live!" href="javascript:void(0);" onclick="onClickPause();"><img id="pauseImage" border="0" src="plugins/Live/templates/images/pause_disabled.gif" /></a>
	<a title="Start Live!" href="javascript:void(0);" onclick="onClickPlay();"><img id="playImage" border="0" src="plugins/Live/templates/images/play.gif" /></a>
	{if !$disableLink}
		&nbsp; <a class="rightLink" href="javascript:piwik.dashboardObject.closeWidgetDialog();broadcast.propagateAjax('module=Live&action=getVisitorLog')">{'Live_LinkVisitorLog'|translate}</a>
	{/if}
</div>
