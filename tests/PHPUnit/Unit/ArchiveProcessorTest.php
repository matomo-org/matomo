<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Archive\Chunk;
use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\DataTable;
use Piwik\Segment;
use Piwik\Tests\Framework\Mock\Site;
use Piwik\Tests\Framework\TestCase\UnitTestCase;
use Piwik\Period\Factory as PeriodFactory;

/**
 * @group ArchiveProcessorTest
 * @group ArchiveProcessor
 * @group Archive
 * @group Core
 */
class ArchiveProcessorTest extends UnitTestCase
{

    public function test_insertBlobRecord_NoBlobsGiven()
    {
        $this->assertInsertBlobRecordPassesBlobsToArchiveWriter(array(), array());
    }

    public function test_insertBlobRecord_OnlyRootTableGiven_ShouldNotMoveRootTableIntoAChunk()
    {
        $blobs = array(0 => $this->getSerializedBlob());
        $this->assertInsertBlobRecordPassesBlobsToArchiveWriter($blobs, $blobs);
    }

    public function test_insertBlobRecord_RootAndSubTablesGiven_OnlyAfewSubtables()
    {
        $blobs = $this->generateBlobs(0, 45);

        $expectedBlobs = array(
            0         => $this->getSerializedBlob('_0'),
            'chunk_0_99' => serialize($this->generateBlobs(1, 44)), // does not start with zero as zero is root table
        );

        $this->assertInsertBlobRecordPassesBlobsToArchiveWriter($expectedBlobs, $blobs);
    }

    public function test_insertBlobRecord_RootAndSubTablesGiven_ShouldOnlySplitSubtablesIntoAChunk()
    {
        $blobs = $this->generateBlobs(0, 1145);

        $expectedBlobs = array(
            0 => $this->getSerializedBlob('_0'),
            'chunk_0_99'      => serialize($this->generateBlobs(1, Chunk::NUM_TABLES_IN_CHUNK - 1)), // does not start with zero as zero is root table
            'chunk_100_199'   => serialize($this->generateBlobs(100, Chunk::NUM_TABLES_IN_CHUNK)),
            'chunk_200_299'   => serialize($this->generateBlobs(200, Chunk::NUM_TABLES_IN_CHUNK)),
            'chunk_300_399'   => serialize($this->generateBlobs(300, Chunk::NUM_TABLES_IN_CHUNK)),
            'chunk_400_499'   => serialize($this->generateBlobs(400, Chunk::NUM_TABLES_IN_CHUNK)),
            'chunk_500_599'   => serialize($this->generateBlobs(500, Chunk::NUM_TABLES_IN_CHUNK)),
            'chunk_600_699'   => serialize($this->generateBlobs(600, Chunk::NUM_TABLES_IN_CHUNK)),
            'chunk_700_799'   => serialize($this->generateBlobs(700, Chunk::NUM_TABLES_IN_CHUNK)),
            'chunk_800_899'   => serialize($this->generateBlobs(800, Chunk::NUM_TABLES_IN_CHUNK)),
            'chunk_900_999'   => serialize($this->generateBlobs(900, Chunk::NUM_TABLES_IN_CHUNK)),
            'chunk_1000_1099' => serialize($this->generateBlobs(1000, Chunk::NUM_TABLES_IN_CHUNK)),
            'chunk_1100_1199' => serialize($this->generateBlobs(1100, 45)),
        );

        $this->assertInsertBlobRecordPassesBlobsToArchiveWriter($expectedBlobs, $blobs);
    }

    public function test_insertBlobRecord_ShouldBeAbleToHandleAString()
    {
        $serialized = $this->getSerializedBlob();

        $this->assertInsertBlobRecordPassesBlobsToArchiveWriter($serialized, $serialized);
    }

    private function generateBlobs($startIndex, $numberOfEntries)
    {
        $blobs = array();

        for ($i = 0; $i < $numberOfEntries; $i++) {
            $subtableId = $startIndex + $i;
            // we need to append something to make sure it actually moves the correct blob into the correct chunk
            $blobs[$subtableId] = $this->getSerializedBlob('_'. $subtableId);
        }

        return $blobs;
    }

    private function getSerializedBlob($appendix = '')
    {
        return 'a:1:{i:0;a:3:{i:0;a:0:{}i:1;a:0:{}i:3;N;}}' . $appendix;
    }

    private function assertInsertBlobRecordPassesBlobsToArchiveWriter($expectedBlobs, $blobs)
    {
        $recordName = 'Actions_Action_url';

        $writer = $this->getMock('Piwik\DataAccess\ArchiveWriter', array('insertBlobRecord'), array(), '', false);
        $writer->expects($this->once())
            ->method('insertBlobRecord')
            ->with($recordName, $expectedBlobs);

        $processor = $this->createProcessor($writer);
        $processor->insertBlobRecord($recordName, $blobs);
    }

    private function createArchiveProcessorParamaters()
    {
        $oPeriod = PeriodFactory::makePeriodFromQueryParams('UTC', 'day', '2015-01-01');

        $segment = new Segment(false, array(1));
        $params  = new Parameters(new Site(1), $oPeriod, $segment);

        return $params;
    }

    private function createProcessor($writer)
    {
        $params  = $this->createArchiveProcessorParamaters();

        return new ArchiveProcessor($params, $writer);
    }
}