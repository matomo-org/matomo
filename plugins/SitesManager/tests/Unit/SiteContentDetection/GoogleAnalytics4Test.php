<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleAnalytics4;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class GoogleAnalytics4Test extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new GoogleAnalytics4();
        self::assertSame($expected, $detection->detectByContent($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no GA4 content' => [
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script></script></head><body>A site</body></html>",
            []
        ];

        yield 'GA4 js code found' => [
            true,
            "<html lang=\"en\"><head><title>A site</title></head>
                     <!-- Google tag (gtag.js) -->
                    <script async src='https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID'></script>
                    <script>window.dataLayer = window.dataLayer || [];
                            function gtag(){window.dataLayer.push(arguments);}
                            gtag('js', new Date());
                            gtag('config', 'GA_TRACKING_ID');
                    </script>
                    </head><body>A site</body></html>",
            []
        ];

        yield 'G number found' => [
            true,
            "<html><head></head><body>G-12345ABC</body></html>",
            []
        ];

        yield 'GA4 properties found' => [
            true,
            "<html><head></head><body>properties/1234</body></html>",
            []
        ];
    }
}
