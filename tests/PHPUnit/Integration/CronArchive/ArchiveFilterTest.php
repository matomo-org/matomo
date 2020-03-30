<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace PHPUnit\Unit\CronArchive;

use Piwik\CronArchive\ArchiveFilter;
use Piwik\Plugins\SegmentEditor\API as SegmentAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ArchiveFilterTest extends IntegrationTestCase
{
    public function test_setSegmentsToForceFromSegmentIds_CorrectlyGetsSegmentDefinitions_FromSegmentIds()
    {
        Fixture::createWebsite('2014-12-12 00:01:02');
        SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);
        SegmentAPI::getInstance()->add('burb', 'actions>=3', 1, true, true);
        SegmentAPI::getInstance()->add('sub', 'actions>=4', 1, true, true);

        $cronarchive = new ArchiveFilter();
        $cronarchive->setSegmentsToForceFromSegmentIds(array(2, 4));

        $expectedSegments = array('actions>=2', 'actions>=4');
        $this->assertEquals($expectedSegments, array_values($cronarchive->getSegmentsToForce()));
    }

    public function test_filterArchive_filtersSegmentArchives_IfSegmentArchivingIsDisabled()
    {
        Fixture::createWebsite('2014-12-12 00:01:02');
        SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);
        SegmentAPI::getInstance()->add('burb', 'actions>=3', 1, true, true);
        SegmentAPI::getInstance()->add('sub', 'actions>=4', 1, true, true);

        $filter = new ArchiveFilter();
        $filter->setSegmentsToForceFromSegmentIds([1, 3]);

        $result = $filter->filterArchive(['segment' => 'actions>=1']);
        $this->assertFalse($result);

        $result = $filter->filterArchive(['segment' => 'actions>=2']);
        $this->assertTrue($result);

        $result = $filter->filterArchive(['segment' => 'actions>=3']);
        $this->assertFalse($result);

        $result = $filter->filterArchive(['segment' => 'actions>=4']);
        $this->assertTrue($result);
    }

    public function test_filterArchive_filtersSegmentArchives_IfSegmentIsNotInSegmentsToForce()
    {
        // TODO
    }

    public function test_filterArchive_filtersArchivesWhoseDateIsNotWithinArchiveDateRange()
    {
        // TODO
    }

    public function test_filterArchive_filtersArchivesWhosePeriodIsNotInForcePeriods()
    {
        // TODO
    }
    // TODO: other tests
}