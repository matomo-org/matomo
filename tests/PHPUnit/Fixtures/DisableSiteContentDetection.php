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
use Piwik\Tests\Framework\Mock\FakeSiteContentDetector;

/**
 * Fixture that disables site content detection by returning null values and preventing a live request
 *
 */
class DisableSiteContentDetection extends Fixture
{

    public $idSite = 1;

    public function provideContainerConfig()
    {
        return [
            SiteContentDetector::class => \Piwik\DI::autowire(FakeSiteContentDetector::class)
        ];
    }

    public function setUp(): void
    {
        Fixture::createSuperUser();
        $this->setUpWebsites();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsites()
    {
        if (!self::siteCreated($idSite = 1)) {
            // Use example.org rather than piwik.net so that wordpress isn't detected
            self::createWebsite('2010-01-01', false, 'example.org', 'https://example.org');
        }
    }


}
