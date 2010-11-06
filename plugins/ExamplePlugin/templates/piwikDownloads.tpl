<div style="padding:1.5em;text-align:center">
	{"ExamplePlugin_PiwikHasBeenDownloaded"|translate:$prettyDate:'<b class="piwikDownloadCount_cnt" >...</b>'}
</div>
{* 
 * loading piwik download stats from demo.piwik.org 
 *}
<script type="text/javascript">
{literal}
	$.ajax({
		url: "http://demo.piwik.org/?module=API&method=Goals.getConversions"
				+"&idSite=1&idGoal=1&period="+piwik.period+"&date="+piwik.currentDateString
				+"&token_auth=anonymous&format=json",
		dataType: 'jsonp', 
		jsonp: 'jsoncallback',
		success: function(data) {
			$('.piwikDownloadCount_cnt').html(data.value);
		}
	});
{/literal}
</script>
