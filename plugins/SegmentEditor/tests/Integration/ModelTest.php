<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    private $idSegment4;

    public function setUp(): void
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

    public function tearDown(): void
    {
        parent::tearDown();
        // Force a hard delete of segment
        $idsToDelete = array($this->idSegment1, $this->idSegment2, $this->idSegment3);
        if ($this->idSegment4) {
            $idsToDelete[] = $this->idSegment4;
        }
        $idsToDelete = implode(',', $idsToDelete);
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

    public function test_getSegmentsDeletedSince_noDeletedSegments()
    {
        $date = Date::factory('now');
        $segments = $this->model->getSegmentsDeletedSince($date);
        $this->assertEmpty($segments);
    }

    public function test_getSegmentsDeletedSince_oneDeletedSegment()
    {
        $this->model->deleteSegment($this->idSegment3);

        $date = Date::factory('now')->subDay(1);
        $segments = $this->model->getSegmentsDeletedSince($date);

        $this->assertCount(1, $segments);
        $this->assertEquals('country==Hobbiton', $segments[0]['definition']);
    }

    public function test_getSegmentsDeletedSince_segmentDeletedTooLongAgo()
    {
        // Manually delete it to set timestamp 9 days in past
        $deletedAt = Date::factory('now')->subDay(9)->toString('Y-m-d H:i:s');
        $this->model->updateSegment($this->idSegment1, array(
            'deleted' => 1,
            'ts_last_edit' => $deletedAt
        ));

        // The segment deleted above should not be included as it was more than 8 days ago
        $date = Date::factory('now')->subDay(8);
        $segments = $this->model->getSegmentsDeletedSince($date);

        $this->assertEmpty($segments);
    }

    public function test_getSegmentsDeletedSince_duplicateSegment()
    {
        // Turn segment1 into a duplicate of segment2, except it's also deleted
        $this->model->updateSegment($this->idSegment1, array(
            'definition' => 'country==Genovia',
            'deleted' => 1,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        ));

        $date = Date::factory('now')->subDay(8);
        $segments = $this->model->getSegmentsDeletedSince($date);

        $this->assertEmpty($segments);
    }

    public function test_getSegmentsDeletedSince_duplicateSegmentDifferentIdSite()
    {
        // Turn segment2 into a duplicate of segment3, except for a different idsite and also deleted
        $this->model->updateSegment($this->idSegment2, array(
            'definition' => 'country==Hobbiton',
            'enable_only_idsite' => 2,
            'deleted' => 1,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        ));

        $date = Date::factory('now')->subDay(8);
        $segments = $this->model->getSegmentsDeletedSince($date);

        $this->assertCount(1, $segments);
        $this->assertEquals('country==Hobbiton', $segments[0]['definition']);
        $this->assertEquals(2, $segments[0]['enable_only_idsite']);
    }

    public function test_getSegmentsDeletedSince_duplicateSegmentAllSitesAndSingleSite()
    {
        // Turn segment2 into a duplicate of segment3, except for all sites and also deleted
        $this->model->updateSegment($this->idSegment2, array(
            'definition' => 'country==Hobbiton',
            'enable_only_idsite' => 0,
            'deleted' => 1,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        ));

        $date = Date::factory('now')->subDay(8);
        $segments = $this->model->getSegmentsDeletedSince($date);

        $this->assertCount(1, $segments);
        $this->assertEquals('country==Hobbiton', $segments[0]['definition']);
        $this->assertEquals(0, $segments[0]['enable_only_idsite']);
        $this->assertEquals(array(1), $segments[0]['idsites_to_preserve']);
    }

    public function test_getSegmentsDeletedSince_duplicateSegmentSingleSiteAndAllSites()
    {
        // Turn segment3 into a duplicate of segment1, except for a single site and deleted
        $this->model->updateSegment($this->idSegment3, array(
            'definition' => 'country==Narnia',
            'enable_only_idsite' => 1,
            'deleted' => 1,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        ));

        $date = Date::factory('now')->subDay(8);
        $segments = $this->model->getSegmentsDeletedSince($date);

        // There is still a live segment for all sites, so the deleted site-specific one is ignored
        $this->assertEmpty($segments);
    }

    public function test_getSegmentsDeletedSince_ExistingSiteSpecificAndAllSitesMatch()
    {
        // A deleted all-sites segment, with both an all-sites and a site-specific segment still present
        $this->model->updateSegment($this->idSegment1, array(
            'definition' => 'actions >= 1',
            'enable_only_idsite' => 0,
            'deleted' => 0,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        ));
        $this->model->updateSegment($this->idSegment2, array(
            'definition' => 'actions >= 1',
            'enable_only_idsite' => 0,
            'deleted' => 1,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        ));
        $this->model->updateSegment($this->idSegment3, array(
            'definition' => 'actions >= 1',
            'enable_only_idsite' => 3,
            'deleted' => 0,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        ));
        $this->idSegment4 = $this->model->createSegment(array(
            'definition' => 'actions >= 1',
            'enable_only_idsite' => 1,
            'deleted' => 1,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        ));

        $date = Date::factory('now')->subDay(8);
        $segments = $this->model->getSegmentsDeletedSince($date);

        $this->assertEmpty($segments);
    }

    public function test_getSegmentsDeletedSince_urlDecodedVersionOfSegment()
    {
        // Turn segment2 into a duplicate of segment3, except a urlencoded version
        $this->model->updateSegment($this->idSegment2, array(
            'definition' => 'country%3D%3DHobbiton',
            'enable_only_idsite' => 1,
            'deleted' => 1,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        ));

        $date = Date::factory('now')->subDay(8);
        $segments = $this->model->getSegmentsDeletedSince($date);

        // The two encoded and decoded version of the segments should be treated as duplicates
        // This means there segment has a non-deleted version so it's not returned
        $this->assertEmpty($segments);
    }

    public function test_getSegmentsDeletedSince_urlEncodedVersionOfSegment()
    {
        // segment1 => url decoded version, deleted
        $this->model->updateSegment($this->idSegment1, array(
            'definition' => 'country==Narnia',
            'deleted' => 1,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        ));
        // segment2 => url encoded version, not deleted
        $this->model->updateSegment($this->idSegment2, array(
            'definition' => 'country%3D%3DNarnia',
            'deleted' => 0,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        ));

        $date = Date::factory('now')->subDay(8);
        $segments = $this->model->getSegmentsDeletedSince($date);

        // The two encoded and decoded version of the segments should be treated as duplicates
        // This means there segment has a non-deleted version so it's not returned
        $this->assertEmpty($segments);
    }

    private function assertReturnedIdsMatch(array $expectedIds, array $resultSet)
    {
        $this->assertEquals(count($expectedIds), count($resultSet));

        $returnedIds = array_column($resultSet, 'idsegment');
        sort($returnedIds);

        $this->assertEquals(array_values($expectedIds), array_values($returnedIds));
    }
}
