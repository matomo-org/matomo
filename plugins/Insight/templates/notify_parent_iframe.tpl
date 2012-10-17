<html>
<head>
	<title></title>
</head>
<body>
	<script type="text/javascript">
		// go up two iframes to find the piwik window
		var piwikWindow = window.parent.parent;
		// notify piwik of location change
		// the location has been passed as the hash part of the url from the insight session
		piwikWindow.Piwik_Insight.setCurrentUrl(window.location.hash.substring(1));
	</script>
</body>
</html>