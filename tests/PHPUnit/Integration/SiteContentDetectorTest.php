<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Plugins\SitesManager\SiteContentDetection\Cloudflare;
use Piwik\Plugins\SitesManager\SiteContentDetection\Osano;
use Piwik\Plugins\SitesManager\SiteContentDetection\ReactJs;
use Piwik\Plugins\SitesManager\SiteContentDetection\Wordpress;
use Piwik\SiteContentDetector;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group SiteContentDetectorTest
*/
class SiteContentDetectorTest extends IntegrationTestCase
{


    public function testSiteWithMultipleDetections()
    {
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, [
            'data' => "<html lang='en'><head><title>A site</title></head><script src='https://localhost.com/js/react.min.js'></script><script src='https://osano.com/uhs9879874hthg.js'></script><script>Osano.cm.addEventListener('osano-cm-consent-changed', (change) => { console.log('cm-change'); consentSet(change); });</script></><body>A site<img src='/wp-content/uploads/images.gif'</body></html>",
            'headers' => [
                'CF-RAY' => 'test'
            ],
        ]);

        self::assertTrue($scd->wasDetected(Osano::getId()));
        self::assertTrue($scd->wasDetected(Wordpress::getId()));
        self::assertTrue($scd->wasDetected(ReactJs::getId()));
        self::assertTrue($scd->wasDetected(Cloudflare::getId()));
        self::assertContains(Osano::getId(), $scd->connectedContentManagers);
    }
}
