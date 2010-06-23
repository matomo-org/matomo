
<script type="text/javascript">
	var idSite = {$idSite};
{literal}
	$(document).ready(function(){ 
	function getName()
	{
		return $("#feedburnerName").val();
	}
	$("#feedburnerSubmit").click( function(){
		var feedburnerName = getName();
		$.get('?module=ExampleFeedburner&action=saveFeedburnerName&idSite='+idSite+'&name='+feedburnerName);
		piwik.dashboardObject.reloadEnclosingWidget($(this));
	});
});
</script>
<style>
.metric { font-weight:bold;text-align:left; }
.feedburner td { padding:0px 3px; } 
</style>
{/literal}

{if !is_array($fbStats)}
	{$fbStats}
{else}
<table class='feedburner' align="center" cellpadding="2" style='text-align:center'>
	<tr>
		<td></td>
		<td style="text-decoration:underline;">Previous</td>
		<td style="text-decoration:underline;">Yesterday</td>
		<td></td>
	</tr>
	<tr>
		<td class='metric'>Circulation</td>
		<td>{$fbStats[0][0]}</td>
		<td>{$fbStats[0][1]}</td>
		<td>{$fbStats[0][2]}</td>
	</tr>
	<tr>
		<td class='metric'>Hits</td>
		<td>{$fbStats[1][0]}</td>
		<td>{$fbStats[1][1]}</td>
		<td>{$fbStats[1][2]}</td>
	</tr>
	<tr>
		<td class='metric'>Reach</td>
		<td>{$fbStats[2][0]}</td>
		<td>{$fbStats[2][1]}</td>
		<td>{$fbStats[2][2]}</td>
	</tr>
</table>
{/if}

<center>
<input id="feedburnerName" type="text" value="{$feedburnerFeedName}" />
<input id="feedburnerSubmit" type="submit" value="ok" />
</center>
