<?php
require_once "UserAgentParser.php";
echo "<h2>UserAgentParser php library test</h2>";
$testUserAgent = array( 
	'my user agent' => '',
	'iphone' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5G77 Safari/525.20',
	'chrome on winxp' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/1.0.154.48 Safari/525.19',
	'IE6 on winxp' => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648)',
	'safari on winxp' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Version/3.1.2 Safari/525.21',
	'FF3 on winxp' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6',
);
echo "Test with another user agent: ";
foreach($testUserAgent as $name => $userAgent) {
	echo "<a href='?setUserAgent=".urlencode($userAgent)."'>$name</a>, ";
}
echo "<hr>";

if(isset($_GET['setUserAgent']) && !empty($_GET['setUserAgent'])) {
	echo "User Agent:";
	$userAgent = urldecode($_GET['setUserAgent']);
} else {
	echo "Your user agent:";
	$userAgent = $_SERVER['HTTP_USER_AGENT'];
}
echo " <b>".htmlentities($userAgent)."</b><br><br>";

echo "Browser info:<pre>";
var_dump(UserAgentParser::getBrowser($userAgent));
echo "</pre>";

echo "Operating System info:<pre>";
var_dump(UserAgentParser::getOperatingSystem($userAgent));
echo "</pre>";

echo "<br><br><i>UserAgentParser doesn't detect your Operating System or Browser properly? <br>Please submit your user agent string and the expected result to hello at piwik.org. Patches are also welcome :-) Thanks!</i>";
