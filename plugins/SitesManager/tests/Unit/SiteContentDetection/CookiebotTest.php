<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Cookiebot;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class CookiebotTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testRunSiteDetectionByContent($expected, $isConnected, $data, $headers)
    {
        $detection = new Cookiebot();
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

        yield 'no cookiebot content' => [
            false,
            false,
            "<html lang=\"en\"><head><title>A site</title><script>console.log('abc');</script></head><body>A site</body></html>",
            []
        ];

        yield 'cookiebot content found' => [
            true,
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script src='://cookiebot.com/bla.js'></script></head><body>A site</body></html>",
            []
        ];

        yield 'cookiebot connected' => [
            true,
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script src='://cookiebot.com/bla.js'></script><script>
typeof _paq === 'undefined' || typeof Cookiebot === 'undefined'
</script></head><body>A site</body></html>",
            []
        ];
    }
}
