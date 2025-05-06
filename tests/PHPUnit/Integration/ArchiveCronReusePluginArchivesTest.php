<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\Goals\API as GoalsAPI;
use Piwik\Plugins\SegmentEditor\API as SegmentApi;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group ArchiveCronTest
 */
class ArchiveCronReusePluginArchivesTest extends IntegrationTestCase
{
    /**
     * @var Date
     */
    private $dateTime;

    /**
     * @var int
     */
    private $idSegment;

    /**
     * @var int
     */
    private $idSite;

    /**
     * @var string
     */
    private $segmentDef;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();

        $this->dateTime = Date::factory('2020-06-06 12:00:00');
        $this->segmentDef = 'actions>=1';

        $this->idSite = Fixture::createWebsite('2015-01-01 00:01:02');
        $this->idSegment = SegmentApi::getInstance()->add('segment', $this->segmentDef, $this->idSite, false, true);

        GoalsAPI::getInstance()->addGoal($this->idSite, 'test goal', 'url', 'http', 'contains');
    }

    public function testSegmentArchivingUsesExistingPluginArchivesForAllPluginsArchiveOfHigherPeriod()
    {
        // create two browser archived visits for two days
        $this->setBrowserArchivingActive(true);
        $this->createVisitAndPerformBrowserArchiving($this->dateTime);
        $this->createVisitAndPerformBrowserArchiving($this->dateTime->addDay(1));

        // perform cron archiving for the week
        $this->setBrowserArchivingActive(false);
        $this->performCronArchivingForWeek();

        // The "nb_conversions" report should return 2!
        $dataTable = VisitsSummaryAPI::getInstance()->get($this->idSite, 'week', $this->dateTime->toString('Y-m-d'), $this->segmentDef);
        self::assertEquals(2, $dataTable->getFirstRow()->getColumn('nb_visits'));
        $dataTable = GoalsAPI::getInstance()->get($this->idSite, 'week', $this->dateTime->toString('Y-m-d'), $this->segmentDef);
        self::assertEquals(0, $dataTable->getFirstRow()->getColumn('nb_conversions'));

        // force rearchiving of the days using cron to verify 2 conversions for the week
        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $invalidator->markArchivesAsInvalidated(
            [$this->idSite],
            [$this->dateTime->toString(), $this->dateTime->addDay(1)->toString()],
            'day',
            new Segment($this->segmentDef, [$this->idSite])
        );

        $cronArchive = new CronArchive();
        $cronArchive->init();
        $cronArchive->run();

        $dataTable = VisitsSummaryAPI::getInstance()->get($this->idSite, 'week', $this->dateTime->toString('Y-m-d'), $this->segmentDef);
        self::assertEquals(2, $dataTable->getFirstRow()->getColumn('nb_visits'));
        $dataTable = GoalsAPI::getInstance()->get($this->idSite, 'week', $this->dateTime->toString('Y-m-d'), $this->segmentDef);
        self::assertEquals(2, $dataTable->getFirstRow()->getColumn('nb_conversions'));
    }

    private function createVisitAndPerformBrowserArchiving(Date $dateTime): void
    {
        // track visit
        $tracker = Fixture::getTracker($this->idSite, $dateTime->getTimestamp());
        Fixture::checkResponse($tracker->doTrackPageView('page ' . $dateTime->getTimestamp()));

        // purge invalidations, otherwise a report request will invalidate other plugins
        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $invalidator->forgetRememberedArchivedReportsToInvalidateForSite($this->idSite);

        Db::query('DELETE FROM ' . Common::prefixTable('archive_invalidations'));

        // perform browser archiving
        $dataTable = VisitsSummaryAPI::getInstance()->get($this->idSite, 'day', $dateTime->toString('Y-m-d'), $this->segmentDef);
        self::assertEquals(1, $dataTable->getFirstRow()->getColumn('nb_visits'));
        $dataTable = GoalsAPI::getInstance()->get($this->idSite, 'day', $dateTime->toString('Y-m-d'), $this->segmentDef);
        self::assertEquals(1, $dataTable->getFirstRow()->getColumn('nb_conversions'));
    }

    private function performCronArchivingForWeek(): void
    {
        // reconfigure segment to allow cron archiving
        $segmentApi = SegmentApi::getInstance();
        $segmentInfo = $segmentApi->get($this->idSegment);
        $segmentApi->update(
            $segmentInfo['idsegment'],
            $segmentInfo['name'],
            $segmentInfo['definition'],
            $segmentInfo['enable_only_idsite'],
            true,
            $segmentInfo['enable_all_users']
        );

        // invalidate the week
        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $invalidator->markArchivesAsInvalidated(
            [$this->idSite],
            [$this->dateTime->toString()],
            'week',
            new Segment($this->segmentDef, [$this->idSite])
        );

        // perform cron archiving
        $cronArchive = new CronArchive();
        $cronArchive->init();
        $cronArchive->run();
    }

    private function setBrowserArchivingActive(bool $setActive): void
    {
        $enforceConfig = $setActive ? 0 : 1;

        self::$fixture->getTestEnvironment()->overrideConfig('General', 'browser_archiving_disabled_enforce', $enforceConfig);
        self::$fixture->getTestEnvironment()->save();

        Config::getInstance()->General['browser_archiving_disabled_enforce'] = $enforceConfig;
        Rules::setBrowserTriggerArchiving($setActive);
    }
}
