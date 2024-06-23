<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\MatomoTagManager;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class MatomoTagManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new MatomoTagManager();
        self::assertSame($expected, $detection->isDetected($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no MTM content' => [
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script></script></head><body>A site</body></html>",
            []
        ];

        yield 'MTM js code found' => [
            true,
            "<html lang=\"en\"><head><title>A site</title></head>
                <!-- Matomo Tag Manager -->
                <script>
                var _mtm = window._mtm = window._mtm || [];
                _mtm.push({'mtm.startTime': (new Date().getTime()), 'event': 'mtm.Start'});
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.async=true; g.src='https://dev.matomo.io/js/container_O1azR7hx.js'; s.parentNode.insertBefore(g,s);
                </script>
                <!-- End Matomo Tag Manager -->                  
                </head><body>A site</body></html>",
            []
        ];
    }
}
