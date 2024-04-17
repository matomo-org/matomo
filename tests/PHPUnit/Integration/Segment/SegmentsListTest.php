<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Plugin\Segment;
use Piwik\Segment\SegmentsList;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SegmentsListTest
 * @group Segment
 */
class SegmentsListTest extends IntegrationTestCase
{
    public function testSegmentsList()
    {
        $list = new SegmentsList();

        // add a segment
        $segment = $this->getDummySegment('seg1');
        $list->addSegment($segment);
        $this->assertEquals([$segment], $list->getSegments());
        $this->assertEquals($segment, $list->getSegment('seg1'));

        // add another segment
        $segment2 = $this->getDummySegment('seg2');
        $list->addSegment($segment2);
        $this->assertEquals([$segment, $segment2], $list->getSegments());
        $this->assertEquals($segment, $list->getSegment('seg1'));
        $this->assertEquals($segment2, $list->getSegment('seg2'));

        // remove a segment
        $list->remove($segment->getCategoryId(), $segment->getSegment());
        $this->assertEquals([1 => $segment2], $list->getSegments());
        $this->assertNull($list->getSegment('seg1'));
        $this->assertEquals($segment2, $list->getSegment('seg2'));

        // remove segment by category
        $list->remove($segment2->getCategoryId());
        $this->assertEquals([], $list->getSegments());
        $this->assertNull($list->getSegment('seg1'));
        $this->assertNull($list->getSegment('seg2'));
    }

    public function testGlobalSegmentsList()
    {

        $list = SegmentsList::get();
        $segments = $list->getSegments();

        // there should be at least 100 segments in core
        $this->assertGreaterThan(99, count($segments));

        // check some specific segments exists
        $this->assertNotNull($list->getSegment('pageUrl'));
        $this->assertNotNull($list->getSegment('countryCode'));
        $this->assertNotNull($list->getSegment('countryName'));
        $this->assertNotNull($list->getSegment('actions'));
    }

    /**
     * @param $expr
     * @return Segment
     */
    protected function getDummySegment($expr)
    {
        $segment = new Segment();
        $segment->setName('Dummy');
        $segment->setCategory('Dummy Cat');
        $segment->setSegment($expr);
        return $segment;
    }
}
