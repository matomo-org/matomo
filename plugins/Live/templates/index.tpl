{literal}
<script type="text/javascript" charset="utf-8">
$(document).ready(function() {
    $('#visitsLive').liveWidget({
        interval: {/literal}{$liveRefreshAfterMs}{literal},
        onUpdate: function(){
            //updates the numbers of total visits in startbox
            var ajaxRequest = new ajaxHelper();
            ajaxRequest.setFormat('html');
            ajaxRequest.addParams({
                module: 'Live',
                action: 'ajaxTotalVisitors'
            }, 'GET');
            ajaxRequest.setCallback(function (r) {
                $("#visitsTotal").html(r);
            });
            ajaxRequest.send(false);
        },
        maxRows: 10,
        fadeInSpeed: 600,
        dataUrlParams: {
            module: 'Live',
            action: 'getLastVisitsStart'
        }
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
		&nbsp; <a class="rightLink" href="javascript:broadcast.propagateAjax('module=Live&action=getVisitorLog')">{'Live_LinkVisitorLog'|translate}</a>
	{/if}
</div>
