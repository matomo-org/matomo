echo "
Stress testing piwik.php
========================
"
ab -n500 -c50 "http://localhost/dev/piwiktrunk/piwik.php"
