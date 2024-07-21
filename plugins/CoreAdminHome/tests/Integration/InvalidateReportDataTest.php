<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Plugins\CoreAdminHome\API as CoreAdminHomeAPI;
use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Plugins\VisitFrequency\API as VisitFrequencyAPI;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreAdminHome
 * @group CoreAdminHome_Integration
 */
class InvalidateReportDataTest extends IntegrationTestCase
{
    /**
     * @dataProvider getTimezones
     */
    public function testInvalidationOfDependentSegments($timezone)
    {
        Fixture::createSuperUser(true);
        $idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, false, false, 1, null, null, $timezone);

        $testDate = Date::today()->subDay(10);
        $timezoneTestDate = $testDate->subSeconds(Date::getUtcOffset($timezone));

        // disable browser archiving
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        Config::getInstance()->General['browser_archiving_disabled_enforce'] = 1;

        SegmentEditorAPI::getInstance()->add('fr segment', 'languageCode==fr', $idSite, true);

        // track a visitor
        $t = Fixture::getTracker($idSite, $timezoneTestDate->addHour(12)->getDatetime(), true);
        $t->setUserAgent('Mozilla/5.0 (compatible; MSIE 10.0; Windows Vista; Trident/5.0');
        $t->setIp('10.11.12.13');
        $t->setUrl('http://piwik.net/randomsite');
        $t->doTrackPageView('random site');

        // With a returning visit
        $t->setForceVisitDateTime($timezoneTestDate->addHour(17)->getDatetime());
        $t->setForceNewVisit();
        $t->doTrackPageView('random site');

        // track a second visitor
        $t = Fixture::getTracker($idSite, $timezoneTestDate->addHour(12.5)->getDatetime(), true);
        $t->setIp('20.21.22.23');
        $t->setUserAgent('Mozilla/5.0 (compatible; MSIE 10.0; Windows 8; Trident/5.0)');
        $t->setUrl('http://piwik.net/randomsite');
        $t->doTrackPageView('random site');

        $archiver = new CronArchive();
        $archiver->main();

        $result = VisitsSummaryAPI::getInstance()->get($idSite, 'week', $testDate->toString(), 'languageCode==fr');
        self::assertEquals(3, $result->getFirstRow()->getColumn('nb_visits'));

        $result = VisitFrequencyAPI::getInstance()->get($idSite, 'week', $testDate->toString(), 'languageCode==fr');
        self::assertEquals(1, $result->getFirstRow()->getColumn('nb_visits_returning'));
        self::assertEquals(2, $result->getFirstRow()->getColumn('nb_visits_new'));

        // Remove one visit
        $datasubject = StaticContainer::get(DataSubjects::class);
        $datasubject->deleteDataSubjectsWithoutInvalidatingArchives([['idvisit' => 1]]);

        // Invalidate the segment
        CoreAdminHomeAPI::getInstance()->invalidateArchivedReports($idSite, $testDate->toString(), 'day', 'languageCode==fr');

        // re-run archiving
        $archiver = new CronArchive();
        $archiver->main();

        $result = VisitsSummaryAPI::getInstance()->get($idSite, 'week', $testDate->toString(), 'languageCode==fr');
        self::assertEquals(2, $result->getFirstRow()->getColumn('nb_visits'));

        // check that metrics built with dependent segment archives are updated as well
        $result = VisitFrequencyAPI::getInstance()->get($idSite, 'week', $testDate->toString(), 'languageCode==fr');
        self::assertEquals(1, $result->getFirstRow()->getColumn('nb_visits_returning'));
        self::assertEquals(1, $result->getFirstRow()->getColumn('nb_visits_new'));
    }

    public function getTimezones()
    {
        return [['UTC-12'], ['UTC'], ['UTC+14']];
    }
}
