<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Squarespace;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class SquarespaceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new Squarespace();
        self::assertSame($expected, $detection->detectByContent($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no squarespace content' => [
            false,
            "<html lang=\"en\"><head><title>A site</title><script>console.log('abc');</script></head><body>A site</body></html>",
            []
        ];

        yield 'squarespace comment is found' => [
            true,
            "<html lang=\"en\"><head><title>A site</title><script>console.log('abc');</script></head><body><!-- This is Squarespace. -->A site</body></html>",
            []
        ];

        yield 'squarespace comment not correctly found' => [
            false,
            "<html lang=\"en\"><head><title>A site</title><script>console.log('abc');</script></head><body>This is Squarespace, or not?</body></html>",
            []
        ];
    }
}
