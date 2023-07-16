<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\TarteAuCitron;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class TarteAuCitronTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testRunSiteDetectionByContent($expected, $isConnected, $data, $headers)
    {
        $detection = new TarteAuCitron();
        self::assertSame($expected, $detection->runSiteDetectionByContent($data, $headers));
        self::assertSame($isConnected, $detection->checkIsConnected($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            false,
            '',
            []
        ];

        yield 'no TarteAuCitron content' => [
            false,
            false,
            "<html lang=\"en\"><head><title>A site</title><script>console.log('abc');</script></head><body>A site</body></html>",
            []
        ];

        yield 'TarteAuCitron content found' => [
            true,
            false,
            '<html lang="en"><head><title>A site</title></head><script src="tarteaucitron.js"></script></head><body>A site</body></html>',
            []
        ];

        yield 'TarteAuCitron connected' => [
            true,
            true,
            "<html lang='en'><head><title>A site</title></head><script src='tarteaucitron.js'></script><script>tarteaucitron.user.matomoHost = 'http://localhost';</script></><body>A site</body></html>",
            []
        ];
    }
}
