<?php
if (!isset($_GET['setUserAgent']) && !isset($_SERVER['HTTP_USER_AGENT'])) die;

require_once dirname(__FILE__) . '/UserAgentParser.php';
echo "<h2>UserAgentParser php library test</h2>";
$testUserAgent = array(
    'my user agent'                       => '',
    'ie8 on win7'                         => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET CLR 3.0.04506; .NET CLR 3.5.21022; InfoPath.2; SLCC1; Zune 3.0)',
    'ie8 on vista (compatibility view)'   => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/4.0)',
    'ie8 on vista'                        => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)',
    'chrome on winxp'                     => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/1.0.154.48 Safari/525.19',
    'IE6 on winxp'                        => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648)',
    'safari on winxp'                     => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Version/3.1.2 Safari/525.21',
    'FF3 on winxp'                        => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6',
    'opera 9.63 on winxp'                 => 'Opera/9.63 (Windows NT 5.1; U; en) Presto/2.1.1',
    'Blackberry'                          => 'BlackBerry8700/4.1.0 Profile/MIDP-2.0 Configuration/CLDC-1.1',
    'opera 9.30 on Nintendo Wii'          => 'Opera/9.30 (Nintendo Wii; U; ; 2047-7; en)',
    'iphone'                              => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5G77 Safari/525.20',
    'iPod touch'                          => 'Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A100a Safari/419.3',
    'iPod'                                => 'Mozilla/5.0 (iPod; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11a Safari/525.20',
    'Android'                             => 'Mozilla/5.0 (Linux; U; Android 1.1; en-us; dream) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2',
    'PalmOS'                              => 'Mozilla/5.0 [en] (PalmOS; U; WebPro/3.5; Palm-Zi72) ',
    'safari on mac os X'                  => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_5; en-us) AppleWebKit/527.3+ (KHTML, like Gecko) Version/3.1.2 Safari/525.20.1',
    'opera 9.64 on win ME'                => 'Opera/9.64 (Windows ME; U; en) Presto/2.1.1',
    'opera 10.00 on XP'                   => 'Opera/9.80 (Windows NT 5.1; U; en) Presto/2.2.15 Version/10.00',
    'iron on win7'                        => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/531.0 (KHTML, like Gecko) Iron/3.0.189.0 Safari/531.0',
    'firefox 3.6 alpha on vista'          => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2a2pre) Gecko/20090826 Namoroka/3.6a2pre',
    'firefox 3.5 alpha on win7'           => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1b4pre) Gecko/20090420 Shiretoko/3.5b4pre (.NET CLR 3.5.30729)',
    'firefox nightly build'               => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:2.0a1pre) Gecko/2008060602 Minefield/4.0a1pre',
    'thunderbird 14.0 with lightning 1.6' => 'Mozilla/5.0 (Windows NT 5.1; rv:14.0) Gecko/20120713 Thunderbird/14.0 Lightning/1.6',
    'Windows 8'                           => 'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko',
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
