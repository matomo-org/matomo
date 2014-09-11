<?php
exit; // REMOVE this line to run the script

// this token is used to authenticate your API request.
// You can get the token on the API page inside your Piwik interface
$token_auth = 'anonymous';

// we call the REST API and request the 100 first keywords for the last month for the idsite=7
$url = "http://demo.piwik.org/";
$url .= "?module=API&method=Referrers.getKeywords";
$url .= "&idSite=7&period=month&date=yesterday";
$url .= "&format=PHP&filter_limit=20";
$url .= "&token_auth=$token_auth";

$fetched = file_get_contents($url);
$content = unserialize($fetched);

// case error
if (!$content) {
    print("Error, content fetched = " . $fetched);
}

print("<h1>Keywords for the last month</h1>");
foreach ($content as $row) {
    $keyword = htmlspecialchars(html_entity_decode(urldecode($row['label']), ENT_QUOTES), ENT_QUOTES);
    $hits = $row['nb_visits'];

    print("<b>$keyword</b> ($hits hits)<br>");
}

