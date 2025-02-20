<?php
// -- Matomo Tracking API init --
require_once '../../vendor/matomo/matomo-php-tracker/MatomoTracker.php';
MatomoTracker::$URL = 'http://localhost/matomo-master/';
// Example 1: Tracks a pageview for Website id = {$IDSITE}
$trackingURL = Matomo_getUrlTrackPageView($idSite = 16, $customTitle = 'This title will appear in the report Actions > Page titles');

echo <<<'EOD'
<html>
<body>
<!-- Matomo -->
<script type="text/javascript">
    var _paq = _paq || [];
    (function() {
        var u="//localhost/matomo-master/";
        _paq.push(["setTrackerUrl", u+"matomo.php"]);
        _paq.push(["setSiteId", "16"]);
        var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0];
        g.type="text/javascript"; g.async=true; g.defer=true; g.src=u+"matomo.js"; s.parentNode.insertBefore(g,s);
    })();
</script>
<!-- End Matomo Code -->

This page loads a Simple Tracker request to Matomo website id=1
EOD;

echo '<img src="' . htmlentities($trackingURL, ENT_COMPAT | ENT_HTML401, 'UTF-8') . '" alt="" />';

echo <<<'EOD'
</body>
</html>
EOD;
