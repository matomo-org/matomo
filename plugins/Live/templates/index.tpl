{assign var=showSitesSelection value=true}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}

<h1> Plugin Development version </h1>

{literal}
<script type="text/javascript" src="plugins/Live/templates/scripts/spy.js"></script>

<script type="text/javascript" charset="utf-8">
	$(document).ready(function() { 
		$('#visits').spy({ 
			limit: 10, 
			ajax: 'index.php?module=Live&idSite=1&action=getLastVisits', 
			timeout: 500, 
			customParameterName: 'minIdVisit', 
			customParameterValueCallback: lastIdVisit, 
			fadeInSpeed: 1400 }
		);
	});
	
	function lastIdVisit()
	{
		return $('#visits > div:lt(2) .idvisit').html();
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

</script>

<style>
#visits {
	text-align:left;
}
#visits .datetime, #visits .country, #visits .referer, #visits .settings, #visits .returning {
	float:left;
	margin-right:10px;
	overflow:hidden;
	padding-left:1px;
	max-width:700px;
}
#visits .datetime {
	width:110px;
}
#visits .country {
	width:30px;
}
#visits .referer {
	width:200px;
}
#visits .settings {
	width:100px;
}
#visits .returning {
	width:30px;
}
#visits .visit {
	border-bottom:1px solid #C1DAD7;
	background-color:#F9FAFA;
	padding:10px;
	line-height:24px;
	height:40px;
}
#visits .alt {
	background-color:#FFFFFF;
}
</style>
{/literal}

{$visitors}

<div>
	<a href="#?" onclick="onClickPause();"><img id="pauseImage" border="0" src="plugins/Live/templates/images/pause_disabled.gif"></a> 
	<a href="#?" onclick="onClickPlay();"><img id="playImage" border="0" src="plugins/Live/templates/images/play.gif"></a>
</div>
