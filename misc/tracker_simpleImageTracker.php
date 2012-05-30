<html><body>

This page loads a Simple Tracker request to Piwik website id=1

<img src='http://localhost/trunk/piwik.php?idsite=1&amp;rec=1'>

<?php
// -- Piwik Tracking API init --
require_once "../libs/PiwikTracker/PiwikTracker.php";
PiwikTracker::$URL = 'http://example.org/piwik/';
// Example 1: Tracks a pageview for Website id = {$IDSITE}
echo '<img src="'. str_replace("&","&amp;", Piwik_getUrlTrackPageView( $idSite = 1, $customTitle = 'This title will appear in the report Actions > Page titles')) . '" alt="" />';
?>
</body></html>