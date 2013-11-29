<?php
if (!isset($_GET['setUserAgent']) && !isset($_SERVER['HTTP_USER_AGENT'])) die;

require_once dirname(__FILE__) . '/UserAgentParser.php';
echo "<h2>UserAgentParser php library test</h2>";
$testUserAgent = array(
    'My User Agent'                       => '',
    'Firefox 28.0 on Windows 8.1'         => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0',
    'Firefox 25.0 on Linux'               => 'Mozilla/5.0 (X11; Linux x86_64; rv:25.0) Gecko/20100101 Firefox/25.0',
    'Firefox 25.0 on Android/Mobile'      => 'Mozilla/5.0 (Android; Mobile; rv:25.0) Gecko/25.0 Firefox/25.0',
    'Firefox 25.0 on Android/Tablet'      => 'Mozilla/5.0 (Android; Tablet; rv:25.0) Gecko/25.0 Firefox/25.0',
    'Firefox 25.0 on FirefoxOS'           => 'Mozilla/5.0 (Mobile; rv:25.0) Gecko/20100101 Firefox/25.0',
    'Thunderbird 24.1.1 on Linux'         => 'Mozilla/5.0 (X11; Linux x86_64; rv:24.0) Gecko/20100101 Thunderbird/24.1.1',
    'Google Chrome 31.0 on Linux'         => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36',
    'Google Chrome 31.0 on Android'       => 'Mozilla/5.0 (Linux; Android 4.4; Nexus 7 Build/KRT16S) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.59 Safari/537.36',
    'Opera 12.16 on Linux'                => 'Opera/9.80 (X11; Linux x86_64) Presto/2.12.388 Version/12.16',
    'Opera 18.0 on Windows 7'             => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36 OPR/18.0.1284.49',
    'Opera Next on Windows 7'             => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.48 Safari/537.36 OPR/18.0.1284.26 (Edition Next)',
    'Opera Dev on Windows 7'              => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1685.0 Safari/537.36 OPR/19.0.1324.0 (Edition Developer)',
    'Opera 18.0 on Android'               => 'Mozilla/5.0 (Linux; Android 4.4; Nexus 7 Build/KRT16S) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36 OPR/18.0.1290.66961',
    'Safari 7.0 on OS X 10.9'             => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9) AppleWebKit/537.71 (KHTML, like Gecko) Version/7.0 Safari/537.71',
    'Safari 7.0.4 on iPhone'              => 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11B554a Safari/9537.53',
    'Safari 7.0.4 on iPad'                => 'Mozilla/5.0 (iPad; CPU OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11B554a Safari/9537.53',
    'IE 11.0 on Windows 8.1'              => 'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko',
    'IE 10.0 on Windows 7'                => 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
    'IE 9.0 on Windows 7'                 => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)',
    'IE 8.0 on Windows 7'                 => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)',
    'Maxthon 4.1.3 on Windows 8'          => 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Maxthon/4.1.3.5000 Chrome/26.0.1410.43 Safari/537.1',
);
echo "Test with another user agent: ";
foreach ($testUserAgent as $name => $userAgent) {
    echo "<a href='?setUserAgent=" . urlencode($userAgent) . "'>$name</a>, ";
}
echo "<hr>";

if (isset($_GET['setUserAgent']) && !empty($_GET['setUserAgent'])) {
    echo "User Agent:";
    $userAgent = urldecode($_GET['setUserAgent']);
} else {
    echo "Your user agent:";
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
}
echo " <b>" . htmlentities($userAgent) . "</b><br><br>";

echo "Browser info:<pre>";
var_dump(UserAgentParser::getBrowser($userAgent));
echo "</pre>";

echo "Operating System info:<pre>";
var_dump(UserAgentParser::getOperatingSystem($userAgent));
echo "</pre>";

echo "<br><br><i>UserAgentParser doesn't detect your Operating System or Browser properly? <br>Please submit your user agent string and the expected result to hello at piwik.org. Patches are also welcome :-) Thanks!</i>";
