<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\SitesManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SitesManager
 * @group SitesManager_Integration
 */
class TrackingTest extends IntegrationTestCase
{
    public function testTrackingOldVisitsResetsSiteCreatedTimeSoDataCanBeViewedInUI()
    {
        Fixture::createWebsite('2015-01-01 00:00:00');

        $createdTime = $this->getSiteCreatedTime($idSite = 1);
        $this->assertEquals('2014-12-31 00:00:00', $createdTime);

        $t = $this->getLocalTracker();
        $t->setForceVisitDateTime('2014-05-05 05:05:05');
        Fixture::checkResponse($t->doTrackPageView('page view'));

        $createdTime = $this->getSiteCreatedTime($idSite = 1);
        $this->assertEquals('2014-05-04 00:00:00', $createdTime);
    }

    public function testTrackingOldVisitThatIsExcludedDoesNotResetCreatedTime()
    {
        Fixture::createWebsite('2015-01-01 00:00:00');

        SitesManager\API::getInstance()->setGlobalExcludedIps('123.44.67.43');

        $createdTime = $this->getSiteCreatedTime($idSite = 1);
        $this->assertEquals('2014-12-31 00:00:00', $createdTime);

        $t = $this->getLocalTracker();
        $t->setForceVisitDateTime('2014-05-05 05:05:05');
        $t->setIp('123.44.67.43');
        Fixture::checkResponse($t->doTrackPageView('page view'));

        $createdTime = $this->getSiteCreatedTime($idSite = 1);
        $this->assertEquals('2014-12-31 00:00:00', $createdTime);
    }

    public function testTrackingOldVisitForSiteWithNoTsCreatedTimeDoesNotResetCreatedTime()
    {
        Fixture::createWebsite('2015-01-01 00:00:00');

        $this->unsetCreatedTime($idSite = 1);

        $createdTime = $this->getSiteCreatedTime($idSite = 1);
        $this->assertEquals(null, $createdTime);

        $t = $this->getLocalTracker();
        $t->setForceVisitDateTime('2014-08-06 07:53:09');
        Fixture::checkResponse($t->doTrackPageView('page view'));

        $createdTime = $this->getSiteCreatedTime($idSite = 1);
        $this->assertEquals(null, $createdTime);
    }

    private function getLocalTracker()
    {
        return self::$fixture->getTracker($idSite = 1, '2015-01-01', $defaultInit = true, $useLocalTracker = true);
    }

    private function getSiteCreatedTime($idSite)
    {
        return Db::fetchOne("SELECT ts_created FROM " . Common::prefixTable('site') . " WHERE idsite = ?", [$idSite]);
    }

    private function unsetCreatedTime($idSite)
    {
        Db::query("UPDATE " . Common::prefixTable('site') . " SET ts_created = NULL WHERE idsite = ?", [$idSite]);
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);

        $fixture->createSuperUser = true;
    }
}
