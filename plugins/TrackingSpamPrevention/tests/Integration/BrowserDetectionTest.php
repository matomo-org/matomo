<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration;

use Piwik\Plugins\TrackingSpamPrevention\BrowserDetection;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Version;

/**
 * @group TrackingSpamPrevention
 * @group BrowserDetectionTest
 * @group BrowserDetection
 * @group Plugins
 */
class BrowserDetectionTest extends IntegrationTestCase
{
    /** @var BrowserDetection */
    private $browser;

    public function setUp(): void
    {
        parent::setUp();

        $this->browser = new BrowserDetection();
    }

    /**
     * @dataProvider getHeadlessBrowsersProvider
     */
    public function test_isHeadlessBrowser($expected, $userAgent)
    {
        $this->assertSame($expected, $this->browser->isHeadlessBrowser($userAgent));
    }

    public function getHeadlessBrowsersProvider()
    {
        return [
            [false, ''],
            [false, null],
            [false, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36'],
            [false, 'Mozilla/5.0 (Android 4.4; Mobile; rv:41.0) Gecko/41.0 Firefox/41.0'],
            [true, 'Mozilla/5.0 (Macintosh; Intel Mac OS X) AppleWebKit/534.34 (KHTML, like Gecko) PhantomJS/1.9.8 Safari/534.34'],
            [true, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/79.0.3945.0 Safari/537.36'],
            [true, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) electron/1.0.0 Chrome/53.0.2785.113 Electron/1.4.3 Safari/537.36'],
        ];
    }

    /**
     * @dataProvider getServerSideLibraries
     */
    public function test_isLibrary($expected, $userAgent)
    {
        $this->assertSame($expected, $this->browser->isLibrary($userAgent));
    }

    public function getServerSideLibraries()
    {
        $isMatomoPhp8Compatible = version_compare(Version::VERSION, '4.7.0-b1', '>=') || version_compare(PHP_VERSION, '8.0.0', '<');
        return [
            [false, ''],
            [false, null],
            [false, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36'],
            [false, 'Mozilla/5.0 (Android 4.4; Mobile; rv:41.0) Gecko/41.0 Firefox/41.0'],
            [$isMatomoPhp8Compatible, 'curl/7.68.0'],
            [$isMatomoPhp8Compatible, 'Wget/1.20.3 (linux-gnu)'],
        ];
    }
}
