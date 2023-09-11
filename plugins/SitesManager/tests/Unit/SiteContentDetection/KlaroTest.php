<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Klaro;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class KlaroTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $isConnected, $data, $headers)
    {
        $detection = new Klaro();
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

        yield 'no klaro content' => [
            false,
            false,
            "<html lang=\"en\"><head><title>A site</title><script>console.log('abc');</script></head><body>A site</body></html>",
            []
        ];

        yield 'klaro.js found' => [
            true,
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script src='/klaro.js'></script></head><body>A site</body></html>",
            []
        ];

        yield 'kiprotect.com found' => [
            true,
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script src='//kiprotect.com/java.script'></script></head><body>A site</body></html>",
            []
        ];

        yield 'klaro connected' => [
            true,
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script src='/klaro.js'></script><script>KlaroWatcher()</script></head><body>A site</body></html>",
            []
        ];

        yield 'klaro connected 2' => [
            true,
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script src='//kiprotect.com/java.script'></script><script>var x = {
    title: 'Matomo',
};</script></head><body>A site</body></html>",
            []
        ];
    }
}
