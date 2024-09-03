<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Resolution
 * @group Plugins
 */
class ResolutionTrackingTest extends IntegrationTestCase
{
    public function testResolutionStoredForFirstPageViewOnlyIfNotUnknown()
    {
        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');
        $tracker->setResolution(999, 999);
        Fixture::checkResponse($tracker->doTrackPageView('home page'));

        $tracker->setForceVisitDateTime('2020-01-01 05:00:02');
        $tracker->setResolution(111, 111);
        Fixture::checkResponse($tracker->doTrackPageView('second page'));

        $resolution = Db::fetchAll('SELECT config_resolution FROM ' . Common::prefixTable('log_visit'));
        self::assertEquals([['config_resolution' => '999x999']], $resolution);
    }

    public function testResolutionUpdatedIfInitiallyTrackedUnknown()
    {
        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');
        $tracker->setResolution(null, null);
        Fixture::checkResponse($tracker->doTrackPageView('home page'));

        $tracker->setForceVisitDateTime('2020-01-01 05:00:02');
        $tracker->setResolution(111, 111);
        Fixture::checkResponse($tracker->doTrackPageView('second page'));

        $resolution = Db::fetchAll('SELECT config_resolution FROM ' . Common::prefixTable('log_visit'));
        self::assertEquals([['config_resolution' => '111x111']], $resolution);
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
