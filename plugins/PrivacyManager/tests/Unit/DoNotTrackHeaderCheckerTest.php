<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Unit;

use Piwik\Plugins\PrivacyManager\Config;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;

/**
 * Class DoNotTrackHeaderCheckerTest
 * @group DoNotTrackHeaderCheckerTest
 */
class DoNotTrackHeaderCheckerTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->cleanupServerGlobals();

        $this->setUserAgentToChrome();
    }

    public function tearDown(): void
    {
        $this->cleanupServerGlobals();
    }

    public function test_isDoNotTrackFound_whenDntNotActivated()
    {
        $dntChecker = $this->makeDntHeaderChecker();

        $this->assertFalse($dntChecker->isActive());
        $this->assertFalse($dntChecker->isDoNotTrackFound());
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
        $this->assertTrue($dntChecker->isDoNotTrackFound());
    }

    /**
     * @dataProvider getHeader_DntIsNotActivated
     */
    public function test_isDoNotTrackFound_whenDntActivated_BrowserDoesNotHaveDntHeader($headerName, $headerValue)
    {
        $dntChecker = $this->makeDntHeaderCheckerEnabled();

        $_SERVER[$headerName] = $headerValue;
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
