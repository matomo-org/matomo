<?php
// Example file to demonstrate MatomoTracker.php
// See https://matomo.org/docs/tracking-api/
require_once '../../vendor/matomo/matomo-php-tracker/MatomoTracker.php';
MatomoTracker::$URL = 'http://localhost/trunk/';

$matomoTracker = new MatomoTracker($idSite = 1);
// You can manually set the Visitor details (resolution, time, plugins)
// See all other ->set* functions available in the MatomoTracker class
$matomoTracker->setResolution(1600, 1400);

// Sends Tracker request via http
$matomoTracker->doTrackPageView('Document title of current page view');
// You can also track Goal conversions
$matomoTracker->doTrackGoal($idGoal = 1, $revenue = 42);

echo 'done';