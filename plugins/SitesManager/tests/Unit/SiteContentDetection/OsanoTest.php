<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Osano;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class OsanoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $isConnected, $data, $headers)
    {
        $detection = new Osano();
        self::assertSame($expected, $detection->isDetected($data, $headers));
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

        yield 'no osano content' => [
            false,
            false,
            "<html lang=\"en\"><head><title>A site</title><script>console.log('abc');</script></head><body>A site</body></html>",
            []
        ];

        yield 'osano content found' => [
            true,
            false,
            '<html lang="en"><head><title>A site</title></head><script src="https://osano.com/uhs9879874hthg.js"></script></head><body>A site</body></html>',
            []
        ];

        yield 'osano connected' => [
            true,
            true,
            "<html lang='en'><head><title>A site</title></head><script src='https://osano.com/uhs9879874hthg.js'></script><script>Osano.cm.addEventListener('osano-cm-consent-changed', (change) => { console.log('cm-change'); consentSet(change); });</script></><body>A site</body></html>",
            []
        ];
    }
}
