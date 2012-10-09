echo "
Stress testing piwik.php
========================
"
ab -n5000 -c50 "http://localhost/dev/piwiktrunk/piwik.php?url=http%3A%2F%2Flocalhost%2Fdev%2Fpiwiktrunk%2F&action_name=&idsite=1&res=1280x1024&col=24&h=18&m=46&s=59&fla=1&dir=0&qt=1&realp=1&pdf=0&wma=1&java=1&cookie=1&title=&urlref="
