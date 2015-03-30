<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Archive\Chunk;
use Piwik\Tests\Framework\TestCase\UnitTestCase;

/**
 * @group ChunkTest
 * @group Chunk
 * @group Archive
 * @group Core
 */
class ChunkTest extends UnitTestCase
{
    /**
     * @var Chunk
     */
    private $chunk;

    public function setUp()
    {
        parent::setUp();
        $this->chunk = new Chunk();
    }

    /**
     * @dataProvider getBlobIdForTableDataProvider
     */
    public function test_getBlobIdForTable_shouldSplitChunksIntoBitsOf100($expectedChunk, $tableId)
    {
        $this->assertEquals('chunk_' . $expectedChunk, $this->chunk->getBlobIdForTable($tableId));
    }

    public function getBlobIdForTableDataProvider()
    {
        return array(
            array($expectedChunk = '0_99', $tableId = 0),
            array('0_99', 1),
            array('0_99', 45),
            array('0_99', 99),
            array('100_199', 100),
            array('100_199', 101),
            array('100_199', 134),
            array('100_199', 199),
            array('200_299', 200),
            array('1000_1099', 1000),
            array('9900_9999', 9999),
            array('10000_10099', 10000),
        );
    }

    /**
     * @dataProvider isBlobIdAChunkDataProvider
     */
    public function test_isBlobIdAChunk($isChunk, $blobId)
    {
        $this->assertSame($isChunk, $this->chunk->isBlobIdAChunk($blobId));
    }

    public function isBlobIdAChunkDataProvider()
    {
        return array(
            array($isChunk = true, $blobId = 'chunk_0_99'),
            array(true, 'chunk_100_199'),
            // following 2 are not really a chunk but we accept it as such for simpler/faster implementation
            array(true, 'chunk_0'),
            array(true, 'chunk_999'),
            array(false, 'chunk0'),
            array(false, 'chunk999'),
            array(false, '0'),
            array(false, '5'),
            array(false, 5),
            array(false, '_5'),
        );
    }

    /**
     * @dataProvider isRecordNameAChunkDataProvider
     */
    public function test_isRecordNameAChunk_shouldSplitChunksIntoBitsOf100($isChunk, $recordName)
    {
        $this->assertSame($isChunk, $this->chunk->isRecordNameAChunk($recordName));
    }

    public function isRecordNameAChunkDataProvider()
    {
        return array(
            array($isChunk = true, $recordName = 'Actions_ActionsUrl_chunk_0_99'),
            array(true, 'Actions_ActionsUrl_chunk_100_199'),
            array(true, 'Actions_ActionsUrl_chunk_1000_1099'),
            array(false, 'Actions_ActionsUrl_chunk_0'), // it is no range, should contain something after "chunk_0" eg "chunk_0_99"
            array(false, 'Actions_ActionsUrl_chunk_9999'),
            array(false, 'Actions_ActionsUrl_chunk_4'),
            array(false, 'Actions_ActionsUrl_chunk_ActionsTest_4'), // should end with _chunk_NUMERIC
            array(false, 'Actions_ActionsUrl_chunk_4_ActionsTest'), // should end with _chunk_NUMERIC
            array(false, 'Actions_ActionsUrl_chunk9999'),
            array(false, 'Actions_ActionsUrlchunk_9999'),
            array(false, 'chunk_9999'),
            array(false, 'chunk_9999'),
        );
    }

    public function test_moveArchiveBlobsIntoChunks_NoChunksGiven()
    {
        $this->assertSame(array(), $this->chunk->moveArchiveBlobsIntoChunks(array()));
    }

    /**
     * @dataProvider isRecordNameAChunkDataProvider
     */
    public function test_moveArchiveBlobsIntoChunks_shouldSplitBlobsIntoChunks()
    {
        $array = array_fill(0, 245, 'test');
        $expected = array(
            'chunk_0_99' => array_fill(0, Chunk::NUM_TABLES_IN_CHUNK, 'test'),
            'chunk_100_199' => array_fill(100, Chunk::NUM_TABLES_IN_CHUNK, 'test'),
            'chunk_200_299' => array_fill(200, 45, 'test'),
        );

        $this->assertSame($expected, $this->chunk->moveArchiveBlobsIntoChunks($array));
    }

    /**
     * @dataProvider getRecordNameWithoutChunkAppendixDataProvider
     */
    public function test_getRecordNameWithoutChunkAppendix_shouldSplitChunksIntoBitsOf100($realName, $recordName)
    {
        $this->assertSame($realName, $this->chunk->getRecordNameWithoutChunkAppendix($recordName));
    }

    public function getRecordNameWithoutChunkAppendixDataProvider()
    {
        return array(
            array($isChunk = 'Actions_ActionsUrl', $recordName = 'Actions_ActionsUrl_chunk_0_99'),
            array('Actions_ActionsUrl', 'Actions_ActionsUrl_chunk_9900_9999'),
            array('Actions_ActionsUrl', 'Actions_ActionsUrl_chunk_400_499'),
            // the following are not chunks so we do return the full record name
            array('Actions_ActionsUrl_chunk_0', 'Actions_ActionsUrl_chunk_0'),
            array('Actions_ActionsUrl_chunk_9999', 'Actions_ActionsUrl_chunk_9999'),
            array('Actions_ActionsUrl_chunk_4', 'Actions_ActionsUrl_chunk_4'),
            array('Actions_ActionsUrl_chunk_ActionsTest_4', 'Actions_ActionsUrl_chunk_ActionsTest_4'),
            array('Actions_ActionsUrl_chunk_4_ActionsTest', 'Actions_ActionsUrl_chunk_4_ActionsTest'),
            array('Actions_ActionsUrl_chunk9999', 'Actions_ActionsUrl_chunk9999'),
            array('Actions_ActionsUrlchunk_9999', 'Actions_ActionsUrlchunk_9999'),
            array('chunk_9999', 'chunk_9999'),
            array('chunk_9999', 'chunk_9999'),
        );
    }

}