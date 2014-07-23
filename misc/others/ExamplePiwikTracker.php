<?php
// Example file to demonstrate PiwikTracker.php
// See http://piwik.org/docs/tracking-api/
require_once '../../libs/PiwikTracker/PiwikTracker.php';
PiwikTracker::$URL = 'http://localhost/trunk/';

$piwikTracker = new PiwikTracker($idSite = 1);
// You can manually set the Visitor details (resolution, time, plugins)
// See all other ->set* functions available in the PiwikTracker class
$piwikTracker->setResolution(1600, 1400);

// Sends Tracker request via http
$piwikTracker->doTrackPageView('Document title of current page view');
// You can also track Goal conversions
$piwikTracker->doTrackGoal($idGoal = 1, $revenue = 42);

echo 'done';