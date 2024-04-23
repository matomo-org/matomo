<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace PHPUnit\Integration\CronArchive;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CronArchive\ArchiveFilter;
use Piwik\Date;
use Piwik\Plugins\SegmentEditor\API as SegmentAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class ArchiveFilterTest extends IntegrationTestCase
{
    public function testArchiveFilterFiltersOutArchivesWhenForceReportIsSpecified()
    {
        $filter = new ArchiveFilter();
        $filter->setForceReport('MyPlugin.myName');

        $result = $filter->filterArchive(['date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => 1]);
        $this->assertEquals('report is not the same as value specified in --force-report', $result);

        $result = $filter->filterArchive(['date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => 1, 'plugin' => 'MyPlugin']);
        $this->assertEquals('report is not the same as value specified in --force-report', $result);

        $result = $filter->filterArchive(['date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => 1, 'plugin' => 'MyPlugin', 'report' => 'myOtherName']);
        $this->assertEquals('report is not the same as value specified in --force-report', $result);

        $result = $filter->filterArchive(['date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => 1, 'plugin' => 'MyOtherPlugin', 'report' => 'myName']);
        $this->assertEquals('report is not the same as value specified in --force-report', $result);

        $result = $filter->filterArchive(['date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => 1, 'plugin' => 'MyPlugin', 'report' => 'myName']);
        $this->assertFalse($result);
    }

    public function testSetSegmentsToForceFromSegmentIdsCorrectlyGetsSegmentDefinitionsFromSegmentIds()
    {
        Rules::setBrowserTriggerArchiving(false);
        Fixture::createWebsite('2014-12-12 00:01:02');
        SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);
        SegmentAPI::getInstance()->add('burb', 'actions>=3', 1, true, true);
        SegmentAPI::getInstance()->add('sub', 'actions>=4', 1, true, true);
        Rules::setBrowserTriggerArchiving(true);

        $cronarchive = new ArchiveFilter();
        $cronarchive->setSegmentsToForceFromSegmentIds(array(2, 4));

        $expectedSegments = array('actions>=2', 'actions>=4');
        $this->assertEquals($expectedSegments, array_values($cronarchive->getSegmentsToForce()));
    }

    public function testFilterArchiveFiltersSegmentArchivesForTodayIfSkippingSegmentsForToday()
    {
        Date::$now = strtotime('2020-03-04 04:05:06');

        Fixture::createWebsite('2014-12-12 00:01:02', 0, false, false, 1, null, null, 'America/Los_Angeles');

        $cronarchive = new ArchiveFilter();
        $cronarchive->setSkipSegmentsForToday(true);

        $result = $cronarchive->filterArchive(['idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'segment' => 'browserCode==IE']);
        $this->assertFalse($result);

        $result = $cronarchive->filterArchive(['idsite' => 1, 'period' => 1, 'date1' => '2020-03-03', 'segment' => 'browserCode==IE']);
        $this->assertEquals('skipping segment archives for today', $result);

        $result = $cronarchive->filterArchive(['idsite' => 1, 'period' => 1, 'date1' => '2020-03-02', 'segment' => 'browserCode==IE']);
        $this->assertFalse($result);

        $result = $cronarchive->filterArchive(['idsite' => 1, 'period' => 2, 'date1' => '2020-03-03', 'segment' => 'browserCode==IE']);
        $this->assertFalse($result);
    }

    public function testFilterArchiveFiltersSegmentArchivesIfSegmentArchivingIsDisabled()
    {
        $filter = new ArchiveFilter();
        $filter->setDisableSegmentsArchiving(true);

        $result = $filter->filterArchive(['segment' => 'actions>=1']);
        $this->assertEquals($result, 'segment archiving disabled');

        $result = $filter->filterArchive(['segment' => 'actions>=2']);
        $this->assertEquals($result, 'segment archiving disabled');
    }

    public function testFilterArchiveFiltersSegmentArchivesIfSegmentIsNotInSegmentsToForce()
    {
        Rules::setBrowserTriggerArchiving(false);
        Fixture::createWebsite('2014-12-12 00:01:02');
        SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);
        SegmentAPI::getInstance()->add('burb', 'actions>=3', 1, true, true);
        SegmentAPI::getInstance()->add('sub', 'actions>=4', 1, true, true);
        Rules::setBrowserTriggerArchiving(true);

        $filter = new ArchiveFilter();
        $filter->setSegmentsToForceFromSegmentIds([1, 3]);

        $result = $filter->filterArchive(['segment' => 'actions>=1', 'period' => 1]);
        $this->assertFalse($result);

        $result = $filter->filterArchive(['segment' => 'actions>=2', 'period' => 1]);
        $this->assertEquals('segment \'actions>=2\' is not in --force-idsegments', $result);

        $result = $filter->filterArchive(['segment' => 'actions>=3', 'period' => 1]);
        $this->assertFalse($result);

        $result = $filter->filterArchive(['segment' => 'actions>=4', 'period' => 1]);
        $this->assertEquals('segment \'actions>=4\' is not in --force-idsegments', $result);
    }

    public function testFilterArchiveFiltersArchivesWhoseDateIsNotWithinArchiveDateRange()
    {
        $filter = new ArchiveFilter();
        $filter->setRestrictToDateRange('2015-02-04,2015-03-01');

        $result = $filter->filterArchive(['segment' => '', 'date1' => '2015-02-04', 'date2' => '2015-02-04', 'period' => 1]);
        $this->assertFalse($result);

        $result = $filter->filterArchive(['segment' => '', 'date1' => '2015-03-01', 'date2' => '2015-03-01', 'period' => 1]);
        $this->assertFalse($result);

        $result = $filter->filterArchive(['segment' => '', 'date1' => '2015-02-01', 'date2' => '2015-02-05', 'period' => 1]);
        $this->assertFalse($result);

        $result = $filter->filterArchive(['segment' => '', 'date1' => '2015-02-01', 'date2' => '2015-02-05', 'period' => 1]);
        $this->assertFalse($result);

        $result = $filter->filterArchive(['segment' => '', 'date1' => '2015-01-02', 'date2' => '2015-09-05', 'period' => 1]);
        $this->assertFalse($result);

        $result = $filter->filterArchive(['segment' => '', 'date1' => '2015-01-02', 'date2' => '2015-01-04', 'period' => 1]);
        $this->assertEquals($result, 'archive date range (2015-01-02,2015-01-04) is not within --force-date-range');

        $result = $filter->filterArchive(['segment' => '', 'date1' => '2015-06-02', 'date2' => '2015-06-05', 'period' => 1]);
        $this->assertEquals($result, 'archive date range (2015-06-02,2015-06-05) is not within --force-date-range');
    }

    public function testFilterArchiveFiltersArchivesWhosePeriodIsNotInForcePeriods()
    {
        $filter = new ArchiveFilter();
        $filter->setRestrictToPeriods(['day, month']);

        $result = $filter->filterArchive(['date1' => '2015-03-04', 'date2' => '2015-03-12', 'period' => 1]);
        $this->assertFalse($result);

        $result = $filter->filterArchive(['date1' => '2015-03-04', 'date2' => '2015-03-12', 'period' => 2]);
        $this->assertEquals($result, '');

        $result = $filter->filterArchive(['date1' => '2015-03-04', 'date2' => '2015-03-12', 'period' => 3]);
        $this->assertFalse($result);

        $result = $filter->filterArchive(['date1' => '2015-03-04', 'date2' => '2015-03-12', 'period' => 4]);
        $this->assertEquals($result, '');

        $result = $filter->filterArchive(['date1' => '2015-03-04', 'date2' => '2015-03-12', 'period' => 5]);
        $this->assertFalse($result);
    }

    public function testFilterArchiveDoesNotFilterArchivesThatPass()
    {
        Rules::setBrowserTriggerArchiving(false);
        Fixture::createWebsite('2014-12-12 00:01:02');
        SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);
        Rules::setBrowserTriggerArchiving(true);

        $filter = new ArchiveFilter();

        $result = $filter->filterArchive([
            'segment' => '',
            'date1' => '2015-03-04',
            'date2' => '2015-03-05',
            'period' => 1,
        ]);
        $this->assertFalse($result);

        $filter->setSegmentsToForceFromSegmentIds(array(2));
        $filter->setRestrictToDateRange('2015-02-04,2015-03-01');
        $filter->setRestrictToPeriods(['day, month']);

        $result = $filter->filterArchive([
            'segment' => 'actions>=2',
            'date1' => '2015-02-01',
            'date2' => '2015-02-28',
            'period' => 3,
        ]);
        $this->assertFalse($result);
    }
}
