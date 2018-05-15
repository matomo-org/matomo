<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests;


use Piwik\Plugins\PrivacyManager\Config;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;

/**
 * Class DoNotTrackHeaderCheckerTest
 * @group DoNotTrackHeaderCheckerTest
 */
class DoNotTrackHeaderCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->cleanupServerGlobals();

        $this->setUserAgentToChrome();
    }

    public function tearDown()
    {
        $this->cleanupServerGlobals();
    }

    public function test_isDoNotTrackFound_whenDntNotActivated()
    {
        $dntChecker = $this->makeDntHeaderChecker();

        $this->assertFalse( $dntChecker->isActive() );
        $this->assertFalse( $dntChecker->isDoNotTrackFound() );
    }

    public function getHeader_DntIsActivated()
    {
        return array(
            array('HTTP_X_DO_NOT_TRACK', '1'),
            array('HTTP_DNT', '1'),
            array('HTTP_DNT', '10'),
        );
    }

    public function getHeader_DntIsNotActivated()
    {
        return array(
            array('HTTP_DNT', '0'),
            array('HTTP_DNT', 'x'),
            array('HTTP_X_DO_NOT_TRACK', 'false'),
            array('HTTP_X_DO_NOT_TRACK', 'ok'),
            array('HTTP_X_DO_NOT_TRACK', '11'),
        );
    }

    /**
     * @dataProvider getHeader_DntIsActivated
     */
    public function test_isDoNotTrackFound_whenDntActivated_BrowserHasDntHeader($headerName, $headerValue)
    {
        $dntChecker = $this->makeDntHeaderCheckerEnabled();

        $_SERVER[$headerName] = $headerValue;
        $this->assertTrue( $dntChecker->isDoNotTrackFound() );
    }

    /**
     * @dataProvider getHeader_DntIsNotActivated
     */
    public function test_isDoNotTrackFound_whenDntActivated_BrowserDoesNotHaveDntHeader($headerName, $headerValue)
    {
        $dntChecker = $this->makeDntHeaderCheckerEnabled();

        $_SERVER[$headerName] = $headerValue;
        $this->assertFalse( $dntChecker->isDoNotTrackFound() );
    }

    public function getUserAgents_whereDNTIsAlwaysEnabled()
    {
        return array(
            // IE
            array('Mozilla/4.0 (compatible; MSIE 4.01; Mac_PowerPC)'),
            array('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; OfficeLiveConnector.1.4; OfficeLivePatch.1.3)'),
            array('Mozilla/5.0 (IE 11.0; Windows NT 6.3; Trident/7.0; .NET4.0E; .NET4.0C; rv:11.0) like Gecko'),

            // Maxthon
            array('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; MAXTHON 2.0)'),
            array('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; Maxthon/4.2.0.4000)'),
            array('Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Maxthon/4.4.3.1000 Chrome/30.0.1599.101 Safari/537.36'),

            // With capital letters
            array('Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) MAXTHON/4.4.3.1000 Chrome/30.0.1599.101 Safari/537.36'),
        );
    }

    /**
     * @dataProvider getUserAgents_whereDNTIsAlwaysEnabled
     */
    public function test_isDoNotTrackFound_whenDntActivated_InternetExplorerDoNotTrackIsIgnored($userAgent)
    {
        $dntChecker = $this->makeDntHeaderCheckerEnabled();

        $this->activateDoNotTrackInBrowser();

        $_SERVER['HTTP_USER_AGENT'] = $userAgent;
        $this->assertFalse($dntChecker->isDoNotTrackFound());
    }

    /**
     * @return Config
     */
    protected function makeConfig()
    {
        return new \Piwik\Plugins\PrivacyManager\tests\Framework\Mock\Config();
    }

    /**
     * @return DoNotTrackHeaderChecker
     */
    protected function makeDntHeaderChecker()
    {
        $config = $this->makeConfig();
        $config->doNotTrackEnabled = false;

        $dntChecker = new DoNotTrackHeaderChecker($config);

        $this->assertFalse($dntChecker->isActive());

        return $dntChecker;
    }

    protected function cleanupServerGlobals()
    {
        $_SERVER['HTTP_X_DO_NOT_TRACK'] = null;
        $_SERVER['HTTP_DNT'] = null;
        $_SERVER['HTTP_USER_AGENT'] = null;
    }

    /**
     * @return DoNotTrackHeaderChecker
     */
    protected function makeDntHeaderCheckerEnabled()
    {
        $dntChecker = $this->makeDntHeaderChecker();
        $dntChecker->activate();
        $this->assertTrue($dntChecker->isActive());
        return $dntChecker;
    }

    protected function setUserAgentToChrome()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 ArchLinux (X11; U; Linux x86_64; en-US) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.100 Safari/534.30';
    }

    protected function activateDoNotTrackInBrowser()
    {
        $_SERVER['HTTP_DNT'] = '1';
    }
}
 