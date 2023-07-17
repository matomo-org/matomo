<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleTagManager;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class GoogleTagManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new GoogleTagManager();
        self::assertSame($expected, $detection->detectByContent($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no GTM content' => [
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script></script></head><body>A site</body></html>",
            []
        ];

        yield 'GTM js code found' => [
            true,
            "<html lang=\"en\"><head><title>A site</title></head>
                     <!-- Google Tag Manager -->
                     <script type='hash84759fa843b-text/javascript'>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                     new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                     j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                     'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                     })(window,document,'script','dataLayer','GTM-NRTVJJC');</script>
                     <!-- End Google Tag Manager -->                     
                     </head><body>A site</body></html>",
            []
        ];

        yield 'gtm.start found' => [
            true,
            'it contains gtm.start somewhere',
            []
        ];

        yield 'googletagmanager.js found' => [
            true,
            'foo bar googletagmanager.js ffoo',
            []
        ];
    }
}
