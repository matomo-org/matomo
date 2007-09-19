<?php
// the token will be given in the User page in piwik
// but we can build it because we know the piwik internals
$token = md5('root'.'nintendo');

// we call the REST API and request the 100 first keywords for the last month for the idsite=1
$url = "http://localhost/dev/piwiktrunk/";
$url .= "?module=API&method=Referers.getKeywords&idSite=1&period=month&date=yesterday";
$url .= "&format=PHP&filter_limit=100";
$url .= "&token=$token";

$fetched = file_get_contents($url);
$content = @unserialize($fetched);

// case error
if(!$content)
{
	print("Error, content fetched = ".$fetched);
}

print("<h2>Keywords for the last month</h2>");
foreach($content as $row)
{
	$keyword = urldecode($row['columns']['label']);
	$hits = $row['columns']['nb_visits'];
	
	print("<b>$keyword</b> ($hits hits)<br>");
}

