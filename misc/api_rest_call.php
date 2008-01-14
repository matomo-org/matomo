<?php
// this token is used to authenticate your API request. 
// You can get the token on the API page inside your Piwik interface
$token_auth = '0b809661490d605bfd77f57ed11f0b14';

// we call the REST API and request the 100 first keywords for the last month for the idsite=1
$url = "http://127.0.0.1/svn-dev/trunk/";
$url .= "?module=API&method=Referers.getKeywords";
$url .= "&idSite=1&period=month&date=yesterday";
$url .= "&format=PHP&filter_limit=100";
$url .= "&token_auth=$token_auth";

$fetched = file_get_contents($url);
$content = unserialize($fetched);

// case error
if(!$content)
{
	print("Error, content fetched = ".$fetched);
}

print("<h1>Keywords for the last month</h1>");
foreach($content as $row)
{
	$keyword = urldecode($row['label']);
	$hits = $row['nb_visits'];
	
	print("<b>$keyword</b> ($hits hits)<br>");
}
?>