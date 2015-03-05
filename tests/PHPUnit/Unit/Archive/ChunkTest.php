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
            array($expectedChunk = 0, $tableId = 0),
            array(0, 1),
            array(0, 45),
            array(0, 99),
            array(1, 100),
            array(1, 101),
            array(1, 134),
            array(1, 199),
            array(2, 200),
            array(10, 1000),
            array(99, 9999),
            array(100, 10000),
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
            array($isChunk = true, $blobId = 'chunk_0'),
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
            array($isChunk = true, $recordName = 'Actions_ActionsUrl_chunk_0'),
            array(true, 'Actions_ActionsUrl_chunk_9999'),
            array(true, 'Actions_ActionsUrl_chunk_4'),
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
            'chunk_0' => array_fill(0, Chunk::NUM_TABLES_IN_CHUNK, 'test'),
            'chunk_1' => array_fill(100, Chunk::NUM_TABLES_IN_CHUNK, 'test'),
            'chunk_2' => array_fill(200, 45, 'test'),
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
            array($isChunk = 'Actions_ActionsUrl', $recordName = 'Actions_ActionsUrl_chunk_0'),
            array('Actions_ActionsUrl', 'Actions_ActionsUrl_chunk_9999'),
            array('Actions_ActionsUrl', 'Actions_ActionsUrl_chunk_4'),
            array('Actions_ActionsUrl', 'Actions_ActionsUrl_chunk_ActionsTest_4'),
            array('Actions_ActionsUrl', 'Actions_ActionsUrl_chunk_4_ActionsTest'),
            // the following are not chunks so we do return the full record name
            array('Actions_ActionsUrl_chunk9999', 'Actions_ActionsUrl_chunk9999'),
            array('Actions_ActionsUrlchunk_9999', 'Actions_ActionsUrlchunk_9999'),
            array('chunk_9999', 'chunk_9999'),
            array('chunk_9999', 'chunk_9999'),
        );
    }

}