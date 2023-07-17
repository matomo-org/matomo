<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\CookieYes;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class CookieYesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $isConnected, $data, $headers)
    {
        $detection = new CookieYes();
        self::assertSame($expected, $detection->detectByContent($data, $headers));
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

        yield 'no cookieyes content' => [
            false,
            false,
            "<html lang=\"en\"><head><title>A site</title><script>console.log('abc');</script></head><body>A site</body></html>",
            []
        ];

        yield 'cookieyes content found' => [
            true,
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script src='://cookieyes.com/bla.js'></script></head><body>A site</body></html>",
            []
        ];

        yield 'cookieyes connected' => [
            true,
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script src='://cookieyes.com/bla.js'></script><script>
document.addEventListener(\"cookieyes_consent_update\", function (eventData) { });
</script></head><body>A site</body></html>",
            []
        ];
    }
}
