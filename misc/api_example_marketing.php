<h1>Ratio of visitors that download Piwik</h1>
<style>
thead{
font-weight: bold;
}
</style>
<p>This report is automatically computed in PHP using the <a href='http://dev.piwik.org/trac/wiki/API/Reference'>Piwik APIs</a>. In <a href='http://dev.piwik.org/trac/browser/trunk/misc/api_example_marketing.php'>a few lines of simple PHP</a> (you could use any other language) you can get the data and generate this kind of report.
</p><p>This report is generated in order to see how <a href='http://piwik.org'>Piwik.org</a> visitors are interested in Piwik, which we determine by the ratio of visitors that download the software. We also report the number of visitors looking at the <a href='http://piwik.org/demo'>online demo</a>.</p>
<?php
$visitsDemo = unserialize(file_get_contents('http://piwik.org/demo/?module=API&method=Actions.getActions&idSite=1&period=day&date=previous8&format=php&filter_column=label&filter_pattern=demo'));
$visitsAll = unserialize(file_get_contents('http://piwik.org/demo/?module=API&method=VisitsSummary.getUniqueVisitors&idSite=1&period=day&date=previous8&format=php'));
$downloads = unserialize(file_get_contents('http://piwik.org/demo/?module=API&method=Actions.getDownloads&idSite=1&period=day&date=previous8&format=php&expanded=1&filter_column_recursive=label&filter_pattern_recursive=http://piwik.org/last.zip'));

$ratioSum = $ratioDemoSum = $count = 0;

print('<table border=1 cellpadding=4><thead><td>Date</td><td>Nb visitors</td><td>Visitors on /demo</td><td>Download last.zip</td><td>Ratio visitors/demo</td><td>Ratio visitors/download</td></thead>');
foreach($visitsDemo as $date => $values)
{
	$nbVisitsDemo = $values[0]['nb_uniq_visitors'];
	$nbVisits = $visitsAll[$date];
//	var_dump($downloads[$date]);
	$nbDownloads = $downloads[$date][0]['subtable'][0]['nb_uniq_visitors'];
	$ratio = round($nbDownloads * 100 / $nbVisits, 0);
	$ratioSum += $ratio; $count++;
	
	$ratioDemo = round($nbVisitsDemo * 100 / $nbVisits, 0);
	$ratioDemoSum += $ratioDemo;
		
	print("<tr><td>$date</td><td>$nbVisits</td><td>$nbVisitsDemo</td><td>$nbDownloads</td><td>$ratioDemo %</td><td>$ratio %</td></tr>");
}
print('</table>');

$averageRatio = round($ratioSum / $count, 0);
$averageRatioDemo = round($ratioDemoSum / $count, 0);
print("<p><b>Average visitors visiting the demo = $averageRatioDemo %</b></p>");
print("<p><b>Average visitors downloading piwik = $averageRatio %</b></p>");
