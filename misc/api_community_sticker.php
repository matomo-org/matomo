<?php
$visitsAll = unserialize(file_get_contents('http://piwik.org/demo/?module=API&method=VisitsSummary.getUniqueVisitors&idSite=1&period=day&date=previous2&format=php'));
$downloads = unserialize(file_get_contents('http://piwik.org/demo/?module=API&method=Actions.getDownloads&idSite=1&period=day&date=previous2&format=php&expanded=1&filter_column_recursive=label&filter_pattern_recursive=http://piwik.org/last.zip'));

foreach($visitsAll as $date => $values)
{
	$nbVisits = $visitsAll[$date];
	$nbDownloads = $downloads[$date][0]['subtable'][0]['nb_uniq_visitors'];
	print("$date $nbVisits visits $nbDownloads downloads <br>");
}
