<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Wix;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class WixTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new Wix();
        self::assertSame($expected, $detection->isDetected($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no webflow content' => [
            false,
            "<html lang=\"en\"><head><title>A site</title><script>console.log('abc');</script></head><body>A site</body></html>",
            []
        ];

        yield 'X-Wix-Published-Version is found' => [
            true,
            '<html lang="en"><head><title>A site</title><meta http-equiv="X-Wix-Published-Version" content="29"/><script>console.log(\'abc\');</script></head><body>A site</body></html>',
            []
        ];
    }
}
