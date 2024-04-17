<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Cloudflare;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class CloudflareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new Cloudflare();
        self::assertSame($expected, $detection->isDetected($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no cloudflare headers' => [
            false,
            "<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>",
            ['server' => 'apache']
        ];

        yield 'cloudflare server header' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>",
            ['server' => 'cloudflare']
        ];

        yield 'cloudflare Server header' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>",
            ['Server' => 'cloudflare'],
        ];

        yield 'cloudflare SERVER header' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>",
            ['SERVER' => 'cloudflare'],
        ];

        yield 'cloudflare cf-ray header' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>",
            ['cf-ray' => 'test'],
        ];

        yield 'cloudflare Cf-Ray header' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>",
            ['Cf-Ray' => 'test'],
        ];

        yield 'cloudflare CF-RAY header' => [
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>",
            ['CF-RAY' => 'test'],
        ];
    }
}
