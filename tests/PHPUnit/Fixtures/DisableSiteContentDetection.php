<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Tests\Framework\Fixture;
use Piwik\SiteContentDetector;

/**
 * Fixture that disables site content detection by returning null values and preventing a live request
 *
 */
class DisableSiteContentDetection extends Fixture
{
    public $idSite = 1;

    public function setUp(): void
    {
        $scd = SiteContentDetector::getInstance();
        $scd->setTestData(
            [
                'consentManagerId' => null,
                'consentManagerName' => null,
                'consentManagerUrl' => null,
                'isConnected' => false,
                'ga3' => false,
                'ga4' => false,
                'gtm' => false
            ]);

    }

    public function tearDown(): void
    {
        // empty
    }

}
