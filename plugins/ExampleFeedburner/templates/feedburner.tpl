
<script type="text/javascript">
	var idSite = {$idSite};
{literal}

function initFeedburner()
{

	function getName()
	{
		return $("#feedburnerName").val();
	}
	$("#feedburnerName").on("keyup", function(e) {
		if(isEnterKey(e)) { 
			$("#feedburnerSubmit").click(); 
		} 
	}); 
	$("#feedburnerSubmit").click( function(){
		var feedburnerName = getName();
		$.get('?module=ExampleFeedburner&action=saveFeedburnerName&idSite='+idSite+'&name='+feedburnerName);
		$(this).parents('[widgetId]').dashboardWidget('reload');
		initFeedburner();
	});
}
$(document).ready(function(){
	initFeedburner();
});
</script>
<style type="text/css">
.metric { font-weight:bold;text-align:left; }
.feedburner td { padding:0 3px; }
</style>
{/literal}

{if !is_array($fbStats)}
	<p style='margin-top:20px'>{$fbStats}</p>
{else}
<table class='feedburner' align="center" cellpadding="2" style='text-align:center'>
	<tr>
		<td></td>
		<td style="text-decoration:underline;">{'General_Previous'|translate}</td>
		<td style="text-decoration:underline;">{'General_Yesterday'|translate}</td>
		<td></td>
	</tr>
	<tr>
		<td class='metric'>Circulation</td>
		<td>{$fbStats[0][0]}</td>
		<td>{$fbStats[0][1]}</td>
		<td>{$fbStats[0][2]}</td>
	</tr>
	<tr>
		<td class='metric'>Reach</td>
		<td>{$fbStats[2][0]}</td>
		<td>{$fbStats[2][1]}</td>
		<td>{$fbStats[2][2]}</td>
	</tr>
	<tr>
		<td class='metric'>Hits</td>
		<td>{$fbStats[1][0]}</td>
		<td>{$fbStats[1][1]}</td>
		<td>{$fbStats[1][2]}</td>
	</tr>
</table>
{/if}

<div class='center entityContainer'>
	<input id="feedburnerName" type="text" value="{$feedburnerFeedName}" />
	<input id="feedburnerSubmit" type="submit" value="{'General_Ok'|translate}" />
	<a style='margin-left:10px' class='entityInlineHelp' href='?module=Proxy&action=redirect&url={"http://piwik.org/faq/how-to/#faq_99"|escape:"url"}' target='_blank'>{'ExampleFeedburner_Help'|translate}</a>
</div>
