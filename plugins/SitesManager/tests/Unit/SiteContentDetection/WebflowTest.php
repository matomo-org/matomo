<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Webflow;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class WebflowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new Webflow();
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

        yield 'data-wf-domain is found' => [
            true,
            "<html lang=\"en\" data-wf-domain='http://localhost'><head><title>A site</title><script>console.log('abc');</script></head><body>A site</body></html>",
            []
        ];

        yield 'data-wf-page is found' => [
            true,
            "<html lang=\"en\" data-wf-page='My webpage'><head><title>A site</title><script>console.log('abc');</script></head><body>A site</body></html>",
            []
        ];
    }
}
