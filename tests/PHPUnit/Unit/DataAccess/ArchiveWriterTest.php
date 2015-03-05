<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataTable;
use Piwik\Segment;
use Piwik\Tests\Framework\Mock\Site;
use Piwik\Tests\Framework\TestCase\UnitTestCase;
use Piwik\Period\Factory as PeriodFactory;

/**
 * @group ArchiveWriterTest
 * @group ArchiveWriter
 * @group Archive
 * @group Core
 */
class ArchiveWriterTest extends UnitTestCase
{
    private $recordName = 'Actions_Action_url';

    public function test_insertBlobRecord_NoBlobsGiven_ShouldInsertNothing()
    {
        $this->assertInsertBlobRecordInsertedRecordsInBulk(array(), array());
    }

    public function test_insertBlobRecord_ShouldAppendTheRecordNameToSubtables()
    {
        $blobs = array(
            0 => $this->getSerializedBlob('_root'),
            1 => $this->getSerializedBlob('subtable1'),
            4 => $this->getSerializedBlob('subtable4'),
            5 => $this->getSerializedBlob('subtable5')
        );

        $expectedBlobs = array(
            array($this->recordName       , $this->getSerializedBlob('_root')),
            array($this->recordName . '_1', $this->getSerializedBlob('subtable1')),
            array($this->recordName . '_4', $this->getSerializedBlob('subtable4')),
            array($this->recordName . '_5', $this->getSerializedBlob('subtable5'))
        );

        $this->assertInsertBlobRecordInsertedRecordsInBulk($expectedBlobs, $blobs);
    }

    public function test_insertBlobRecord_ShouldAppendTheRecordNameToChunks()
    {
        $blobs = array(
            0 => $this->getSerializedBlob('_root'),
            'chunk_0' => $this->getSerializedBlob('chunk0'),
            'chunk_1' => $this->getSerializedBlob('chunk1'),
            'chunk_2' => $this->getSerializedBlob('chunk2')
        );

        $expectedBlobs = array(
            array($this->recordName             , $this->getSerializedBlob('_root')),
            array($this->recordName . '_chunk_0', $this->getSerializedBlob('chunk0')),
            array($this->recordName . '_chunk_1', $this->getSerializedBlob('chunk1')),
            array($this->recordName . '_chunk_2', $this->getSerializedBlob('chunk2'))
        );

        $this->assertInsertBlobRecordInsertedRecordsInBulk($expectedBlobs, $blobs);
    }

    public function test_insertBlobRecord_ShouldInsertASingleRecord_IfNotAnArrayOfBlobsIsGiven()
    {
        $blob = $this->getSerializedBlob('_root');

        $this->assertInsertBlobRecordInsertedASingleRecord($blob, $blob);
    }

    private function getSerializedBlob($appendix = '')
    {
        return 'a:1:{i:0;a:3:{i:0;a:0:{}i:1;a:0:{}i:3;N;}}' . $appendix;
    }

    private function assertInsertBlobRecordInsertedRecordsInBulk($expectedBlobs, $blobs)
    {
        $writer = $this->getMock('Piwik\DataAccess\ArchiveWriter', array('insertBulkRecords', 'compress'), array(), '', false);
        $writer->expects($this->exactly(count($blobs)))
               ->method('compress')
               ->will($this->returnArgument(0));
        $writer->expects($this->once())
               ->method('insertBulkRecords')
               ->with($expectedBlobs);

        /** @var ArchiveWriter $writer */
        $writer->insertBlobRecord($this->recordName, $blobs);
    }

    private function assertInsertBlobRecordInsertedASingleRecord($expectedBlob, $blob)
    {
        $writer = $this->getMock('Piwik\DataAccess\ArchiveWriter', array('insertRecord', 'compress'), array(), '', false);
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