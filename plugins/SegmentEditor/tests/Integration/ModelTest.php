<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SegmentEditor\tests\Integration;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\SegmentEditor\Model;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ModelTest extends IntegrationTestCase
{
    private $model;

    private $idSegment1;

    private $idSegment2;

    private $idSegment3;

    public function setUp()
    {
        parent::setUp();
        $this->model = new Model();
        $this->idSegment1 = $this->model->createSegment(array(
            'name' => 'Narnia',
            'definition' => 'country==Narnia',
            'login' => 'user1',
            'enable_only_idsite' => 0,
        ));
        $this->idSegment2 = $this->model->createSegment(array(
            'name' => 'Genovia',
            'definition' => 'country==Genovia',
            'auto_archive' => 1,
            'enable_only_idsite' => 0,
            'enable_all_users' => 1
        ));
        $this->idSegment3 = $this->model->createSegment(array(
            'name' => 'Hobbiton',
            'definition' => 'country==Hobbiton',
            'auto_archive' => 0,
            'login' => 'user2',
            'enable_only_idsite' => 1,
        ));
    }

    public function tearDown()
    {
        parent::tearDown();
        // Force a hard delete of segment
        $idsToDelete = $this->idSegment1 . ', ' . $this->idSegment2 . ', ' . $this->idSegment3;
        Db::query(
            "DELETE FROM " . Common::prefixTable('segment') . " WHERE idsegment IN ($idsToDelete)"
        );
    }

    public function test_deleteSegment_doesSoftDelete()
    {
        $preDeleteTimestamp = Date::getNowTimestamp();
        $this->model->deleteSegment($this->idSegment1);

        // None of the model methods should return it as it's deleted - so we need to get it manually from DB
        $result = Db::query(
            'SELECT * FROM ' . Common::prefixTable('segment') . ' WHERE idsegment = ' . $this->idSegment1
        );
        $row = $result->fetch();

        $this->assertNotEmpty($row);
        $this->assertEquals(1, $row['deleted']);
        $deletedTimestamp = Date::factory($row['ts_last_edit'])->getTimestamp();
        $this->assertGreaterThanOrEqual($deletedTimestamp, $preDeleteTimestamp);
    }

    public function test_getAllSegmentsAndIgnoreVisibility_withDeletedSegment()
    {
        $segments = $this->model->getAllSegmentsAndIgnoreVisibility();
        $this->assertEquals(3, count($segments));

        $this->model->deleteSegment($this->idSegment2);
        $segments = $this->model->getAllSegmentsAndIgnoreVisibility();

        $this->assertReturnedIdsMatch(array($this->idSegment1, $this->idSegment3), $segments);
    }

    public function test_getSegmentsToAutoArchive_withDeletedSegment()
    {
        $segments = $this->model->getSegmentsToAutoArchive();
        $this->assertEquals(1, count($segments));
        $this->assertReturnedIdsMatch(array($this->idSegment2), $segments);

        $this->model->deleteSegment($this->idSegment2);
        $segments = $this->model->getSegmentsToAutoArchive();

        $this->assertEmpty($segments);
    }

    public function test_getAllSegments_withDeletedSegment()
    {
        $segments = $this->model->getAllSegments('user1');
        $this->assertEquals(2, count($segments));

        $this->model->deleteSegment($this->idSegment1);
        $segments = $this->model->getAllSegments('user1');

        $this->assertReturnedIdsMatch(array($this->idSegment2), $segments);
    }

    public function test_getAllSegmentsForSite_withDeletedSegment()
    {
        $segments = $this->model->getAllSegmentsForSite(1, 'user1');
        $this->assertEquals(2, count($segments));
        $this->assertReturnedIdsMatch(array($this->idSegment1, $this->idSegment2), $segments);

        $this->model->deleteSegment($this->idSegment2);
        $segments = $this->model->getAllSegmentsForSite(1, 'user1');

        $this->assertReturnedIdsMatch(array($this->idSegment1), $segments);
    }

    public function test_getAllSegmentsForAllUsers_withDeletedSegment()
    {
        $segments = $this->model->getAllSegmentsForAllUsers();
        $this->assertEquals(3, count($segments));

        $this->model->deleteSegment($this->idSegment3);
        $segments = $this->model->getAllSegmentsForAllUsers();

        $this->assertReturnedIdsMatch(array($this->idSegment1, $this->idSegment2), $segments);
    }

    public function test_getSegmentByDefinition_withDeletedSegment()
    {
        $segment = $this->model->getSegmentByDefinition('Country==Genovia');
        $this->assertNotEmpty($segment);

        $this->model->deleteSegment($this->idSegment2);
        $segment = $this->model->getSegmentByDefinition('Country==Genovia');

        $this->assertEmpty($segment);
    }

    private function assertReturnedIdsMatch(array $expectedIds, array $resultSet)
    {
        $this->assertEquals(count($expectedIds), count($resultSet));

        $returnedIds = array_column($resultSet, 'idsegment');
        sort($returnedIds);

        $this->assertEquals(array_values($expectedIds), array_values($returnedIds));
    }
}