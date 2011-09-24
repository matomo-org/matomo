<div style="padding:1.5em;text-align:center">
	{"ExamplePlugin_PiwikForumReceivedVisits"|translate:$prettyDate:'<b class="piwikDownloadCount_cnt" >...</b>'}
</div>
{* 
 * loading piwik download stats from demo.piwik.org 
 *}
<script type="text/javascript">
{literal}
	$.ajax({
		url: "http://demo.piwik.org/?module=API&method=VisitsSummary.getVisits"
				+"&idSite=7&period="+piwik.period+"&date="+broadcast.getValueFromUrl('date')
				+"&token_auth=anonymous&format=json",
		dataType: 'jsonp', 
		jsonp: 'callback',
		success: function(data) {
			$('.piwikDownloadCount_cnt').html(data.value);
		}
	});
{/literal}
</script>
