<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Archive\Chunk;
use Piwik\DataAccess\ArchiveWriter;

/**
 * @group ArchiveWriterTest
 * @group Archive
 * @group Core
 */
class ArchiveWriterTest extends \PHPUnit\Framework\TestCase
{
    private $recordName = 'Actions_Action_url';

    public function testInsertBlobRecordNoBlobsGivenShouldInsertNothing()
    {
        $this->assertInsertBlobRecordInsertedRecordsInBulk(array(), array());
    }

    public function testInsertBlobRecordOnlyRootTableGivenShouldNotMoveRootTableIntoAChunk()
    {
        $blobs    = array(0 => $this->getSerializedBlob());
        $expected = array(array($this->recordName, $this->getSerializedBlob()));

        $this->assertInsertBlobRecordInsertedRecordsInBulk($expected, $blobs);
    }

    public function testInsertBlobRecordRootAndSubTablesGivenOnlyAfewSubtables()
    {
        $blobs = $this->generateBlobs(0, 45);

        $expectedBlobs = array(
            array($this->recordName, $this->getSerializedBlob('_0')),
            array($this->recordName . '_chunk_0_99', serialize($this->generateBlobs(1, 44)))
        );

        $this->assertInsertBlobRecordInsertedRecordsInBulk($expectedBlobs, $blobs);
    }

    public function testInsertBlobRecordRootAndSubTablesGivenShouldOnlySplitSubtablesIntoAChunk()
    {
        $blobs = $this->generateBlobs(0, 1145);

        $expectedBlobs = array(
            array($this->recordName, $this->getSerializedBlob('_0')),
            array($this->recordName . '_chunk_0_99'     , serialize($this->generateBlobs(1, Chunk::NUM_TABLES_IN_CHUNK - 1))), // does not start with zero as zero is root table
            array($this->recordName . '_chunk_100_199'  , serialize($this->generateBlobs(100, Chunk::NUM_TABLES_IN_CHUNK))),
            array($this->recordName . '_chunk_200_299'  , serialize($this->generateBlobs(200, Chunk::NUM_TABLES_IN_CHUNK))),
            array($this->recordName . '_chunk_300_399'  , serialize($this->generateBlobs(300, Chunk::NUM_TABLES_IN_CHUNK))),
            array($this->recordName . '_chunk_400_499'  , serialize($this->generateBlobs(400, Chunk::NUM_TABLES_IN_CHUNK))),
            array($this->recordName . '_chunk_500_599'  , serialize($this->generateBlobs(500, Chunk::NUM_TABLES_IN_CHUNK))),
            array($this->recordName . '_chunk_600_699'  , serialize($this->generateBlobs(600, Chunk::NUM_TABLES_IN_CHUNK))),
            array($this->recordName . '_chunk_700_799'  , serialize($this->generateBlobs(700, Chunk::NUM_TABLES_IN_CHUNK))),
            array($this->recordName . '_chunk_800_899'  , serialize($this->generateBlobs(800, Chunk::NUM_TABLES_IN_CHUNK))),
            array($this->recordName . '_chunk_900_999'  , serialize($this->generateBlobs(900, Chunk::NUM_TABLES_IN_CHUNK))),
            array($this->recordName . '_chunk_1000_1099', serialize($this->generateBlobs(1000, Chunk::NUM_TABLES_IN_CHUNK))),
            array($this->recordName . '_chunk_1100_1199', serialize($this->generateBlobs(1100, 45))),
        );

        $this->assertInsertBlobRecordInsertedRecordsInBulk($expectedBlobs, $blobs);
    }

    public function testInsertBlobRecordShouldInsertASingleRecordIfNotAnArrayOfBlobsIsGiven()
    {
        $blob = $this->getSerializedBlob('_root');

        $this->assertInsertBlobRecordInsertedASingleRecord($blob, $blob);
    }

    private function generateBlobs($startIndex, $numberOfEntries)
    {
        $blobs = array();

        for ($i = 0; $i < $numberOfEntries; $i++) {
            $subtableId = $startIndex + $i;
            // we need to append something to make sure it actually moves the correct blob into the correct chunk
            $blobs[$subtableId] = $this->getSerializedBlob('_' . $subtableId);
        }

        return $blobs;
    }

    private function getSerializedBlob($appendix = '')
    {
        return 'a:1:{i:0;a:3:{i:0;a:0:{}i:1;a:0:{}i:3;N;}}' . $appendix;
    }

    private function assertInsertBlobRecordInsertedRecordsInBulk($expectedBlobs, $blobs)
    {
        $writer = $this->getMockBuilder('Piwik\DataAccess\ArchiveWriter')
            ->disableOriginalConstructor()
            ->onlyMethods(array('insertRecord', 'compress'))
            ->getMock();
        $writer->expects($this->exactly(count($expectedBlobs)))
               ->method('compress')
               ->will($this->returnArgument(0));

        foreach ($expectedBlobs as $index => $expectedBlob) {
            $writer->expects($this->at($index * 2 + 1))
                ->method('insertRecord')
                ->with($this->equalTo($expectedBlob[0]), $this->equalTo($expectedBlob[1]));
        }

        /** @var ArchiveWriter $writer */
        $writer->insertBlobRecord($this->recordName, $blobs);
    }

    private function assertInsertBlobRecordInsertedASingleRecord($expectedBlob, $blob)
    {
        $writer = $this->getMockBuilder('Piwik\DataAccess\ArchiveWriter')
            ->disableOriginalConstructor()
            ->onlyMethods(array('insertRecord', 'compress'))
            ->getMock();
        $writer->expects($this->once())
               ->method('compress')
               ->will($this->returnArgument(0));
        $writer->expects($this->once())
               ->method('insertRecord')
               ->with($this->recordName, $expectedBlob);

        /** @var ArchiveWriter $writer */
        $writer->insertBlobRecord($this->recordName, $blob);
    }
}
