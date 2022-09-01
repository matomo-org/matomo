<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth\tests\Fixtures;

use Piwik\Tests\Framework\Fixture;

/**
 * Generates tracker testing data for our TwoFactorAuthTest
 *
 * This Simple fixture adds one website and tracks one visit with couple pageviews and an ecommerce conversion
 */
class SimpleFixtureTrackFewVisits extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;

    public function setUp(): void
    {
        $this->setUpWebsite();
        Fixture::createSuperUser(true);
        $this->createSuperUser = true;
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime, $ecommerce = 1);
            $this->assertSame($this->idSite, $idSite);
        }
    }

}