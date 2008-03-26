<h1>Ratio of visitors that try the Piwik online demo</h1>
<p>This report is automatically computed in PHP using the <a href='http://dev.piwik.org/trac/wiki/API/Reference'>Piwik APIs</a>. In <a href='http://dev.piwik.org/trac/browser/trunk/misc/api_example_marketing.php'>a few lines of simple PHP</a> (you could use any other language) you can get the data and generate this kind of report.
</p><p>This report is generated in order to see how <a href='http://piwik.org'>Piwik.org</a> visitors are interested in Piwik, which we determine by the ratio of visitors that look the <a href='http://piwik.org/demo'>online demo</a>.</p>
<?php
$visitsDemo = file_get_contents('http://piwik.org/demo/?module=API&method=Actions.getActions&idSite=1&period=day&date=previous7&format=php&filter_column=label&filter_pattern=demo');
//$visitsDemo = file_get_contents('http://piwik.org/demo/?module=API&method=Actions.getActions&idSite=1&period=day&date=previous7&format=php&filter_column=label&filter_pattern=demo');
$visitsAll = file_get_contents('http://piwik.org/demo/?module=API&method=VisitsSummary.getUniqueVisitors&idSite=1&period=day&date=previous7&format=php');

$visitsDemo = unserialize($visitsDemo);
$visitsAll = unserialize($visitsAll);

$ratioSum = $count = 0;

print('<table border=1 cellpadding=4><thead><td>Date</td><td>Nb visits</td><td>Nb visits on /demo</td><td>Ratio</td></thead>');
foreach($visitsDemo as $date => $values)
{
	$nbVisitsDemo = $values[0]['nb_uniq_visitors'];
	$nbVisits = $visitsAll[$date];
	$ratio = round($nbVisitsDemo * 100 / $nbVisits, 0);
	$ratioSum += $ratio; $count++;
	
	print("<tr><td>$date</td><td>$nbVisits</td><td>$nbVisitsDemo</td><td>$ratio %</td></tr>");
}
print('</table>');

$averageRatio = round($ratioSum / $count, 0);
print("<p><b>Average visitors visiting the online demo = $averageRatio %</b></p>");
