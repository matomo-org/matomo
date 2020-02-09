<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\Integration;

use Piwik\Plugins\SegmentEditor\SegmentList;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Exception;

/**
 * @group SegmentListTest
 * @group SegmentList
 * @group SegmentEditor
 * @group Plugins
 */
class SegmentListTest extends IntegrationTestCase
{
    /**
     * @var SegmentList
     */
    private $list;

    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2012-01-01 00:00:00');
        $this->list   = new SegmentList();
    }

    public function test_findSegment_shouldFindSegmentByName_IfNameExists()
    {
        $segmentName = 'pageUrl';

        $segment = $this->list->findSegment($segmentName, $this->idSite);
        self::assertIsArray($segment);
        $this->assertSame($segmentName, $segment['segment']);
    }

    public function test_findSegment_shouldNotFindSegmentByName_IfNameDoesNotExist()
    {
        $segment = $this->list->findSegment('aNyNotExisTinGSegmEnt', $this->idSite);
        $this->assertNull($segment);
    }

    public function test_findSegment_ShouldThrowException_IfNotEnoughPermission()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasViewAccess');

        FakeAccess::clearAccess($superUser = false, array(1));

        $segment = $this->list->findSegment('pageUrl', 999);
        $this->assertNull($segment);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }

}
