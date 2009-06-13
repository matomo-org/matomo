
<script type="text/javascript">
	var idSite = {$idSite};

{literal}
	$(document).ready(function(){ 
	
	function getName()
	{
		return $("#feedburnerName").val();
	}
	function loadIframe()
	{
		var feedburnerName = getName();
		$("#feedburnerIframe").html(
			'<iframe height=100px frameborder="0" marginheight="10" marginwidth="10" \
				src="http://www.feedburner.com/fb/ticker/api-ticker2.jsp?uris='+feedburnerName+'"></iframe>');
	}
	
	$("#feedburnerSubmit").click( function(){
		var feedburnerName = getName();
		$.get('index.php?module=ExampleFeedburner&action=saveFeedburnerName&idSite='+idSite+'&name='+feedburnerName);
		loadIframe();
		
	});
	
	loadIframe();
});
</script>
{/literal}			
<span id="feedburnerIframe"></span>

<center>
<input id="feedburnerName" type="text" value="{$feedburnerFeedName}">
<input id="feedburnerSubmit" type="submit" value="ok">
</center>

