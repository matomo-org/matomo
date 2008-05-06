<?php
$translations = array(
	'API_QuickDocumentation' => 
		"<h2>API quick documentation</h2>".
		"<p>If you don't have data for today you can first <a href='misc/generateVisits.php' target=_blank>generate some data</a> using the Visits Generator script.</p>".
		"<p>You can try the different formats available for every method. It is very easy to extract any data you want from piwik!</p>".
		"<p><b>For more information have a look at the <a href='http://dev.piwik.org/trac/wiki/API'>official API Documentation</a> or the <a href='http://dev.piwik.org/trac/wiki/API/Reference'>API Reference</a>.</b></P>".
		"<h2>User authentication</h2>".
		"<p>If you want to <b>request the data in your scripts, in a crontab, etc. </b> you need to add the parameter <code><u>&token_auth=%s</u></code> to the API calls URLs that require authentication.</p>".
		"<p>This token_auth is as secret as your login and password, <b>do not share it!</p>",
	'API_LoadedAPIs' => 'Loaded successfully %s APIs',
);
