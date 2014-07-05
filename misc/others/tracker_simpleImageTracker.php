<?php
// -- Piwik Tracking API init --
require_once "../../libs/PiwikTracker/PiwikTracker.php";
PiwikTracker::$URL = 'http://localhost/piwik-master/';
// Example 1: Tracks a pageview for Website id = {$IDSITE}
$trackingURL = Piwik_getUrlTrackPageView($idSite = 16, $customTitle = 'This title will appear in the report Actions > Page titles');

?>
<html>
<body>
<!-- Piwik -->
<script type="text/javascript">
    var _paq = _paq || [];
    (function() {
        var u="//localhost/piwik-master/";
        _paq.push(["setTrackerUrl", u+"piwik.php"]);
        _paq.push(["setSiteId", "16"]);
        var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0];
        g.type="text/javascript"; g.async=true; g.defer=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);
    })();
</script>
<!-- End Piwik Code -->

This page loads a Simple Tracker request to Piwik website id=1

<?php
echo '<img src="' . htmlentities($trackingURL) . '" alt="" />';
?>
</body>
</html>