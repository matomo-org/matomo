<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\ArchiveProcessor\PluginsArchiver;
use Piwik\Cache;
use Piwik\EventDispatcher;
use Piwik\Plugin\Archiver;
use Piwik\Tests\Framework\Fixture;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ArchiveWithNoVisitsTest_MockArchiver extends Archiver
{
    public static $methodsCalled = array();

    public function aggregateDayReport()
    {
        self::$methodsCalled[] = 'aggregateDayReport';
    }

    public function aggregateMultipleReports()
    {
        self::$methodsCalled[] = 'aggregateMultipleReports';
    }
}

class ArchiveWithNoVisitsTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        Fixture::createWebsite('2011-01-01');

        ArchiveWithNoVisitsTest_MockArchiver::$methodsCalled = array();
    }

    public function test_getIdSitesToArchiveWhenNoVisits_CanBeUsedToTriggerArchiving_EvenIfSiteHasNoVisits()
    {
        // add our mock archiver instance
        // TODO: should use a dummy plugin that is activated for this test explicitly, but that can be tricky, especially in the future

        PluginsArchiver::$archivers['VisitsSummary'] = 'Piwik\Tests\Integration\ArchiveWithNoVisitsTest_MockArchiver';

        // initiate archiving w/o adding the event and make sure no methods are called
        VisitsSummaryAPI::getInstance()->get($idSite = 1, 'week', '2012-01-01');

        $this->assertEmpty(ArchiveWithNoVisitsTest_MockArchiver::$methodsCalled);

        // mark our only site as should archive when no visits
        $eventDispatcher = $this->getEventDispatcher();
        $eventDispatcher->addObserver('Archiving.getIdSitesToArchiveWhenNoVisits', function (&$idSites) {
            $idSites[] = 1;
        });

        Cache::getTransientCache()->flushAll();

        // initiate archiving and make sure both aggregate methods are called correctly
        VisitsSummaryAPI::getInstance()->get($idSite = 1, 'week', '2012-01-10');

        $expectedMethodCalls = array(
            'aggregateDayReport',
            'aggregateDayReport',
            'aggregateDayReport',
            'aggregateDayReport',
            'aggregateDayReport',
            'aggregateDayReport',
            'aggregateDayReport',
            'aggregateMultipleReports',
        );
        $this->assertEquals($expectedMethodCalls, ArchiveWithNoVisitsTest_MockArchiver::$methodsCalled);
    }

    /**
     * @return EventDispatcher
     */
    private function getEventDispatcher()
    {
        return self::$fixture->piwikEnvironment->getContainer()->get('Piwik\EventDispatcher');
    }
}
