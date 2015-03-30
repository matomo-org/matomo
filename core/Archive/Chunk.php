<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Archive;

use Piwik\DataTable;

/**
 * This class is used to hold and transform archive data for the Archive class.
 *
 * Archive data is loaded into an instance of this type, can be indexed by archive
 * metadata (such as the site ID, period string, etc.), and can be transformed into
 * DataTable and Map instances.
 */
class Chunk
{
    const ARCHIVE_APPENDIX_SUBTABLES = 'chunk';
    const NUM_TABLES_IN_CHUNK = 100;

    /**
     * Get's the BlobId to use for a given tableId/subtableId.
     *
     * @param int $tableId  eg '5' for tableId '5'
     * @return string       eg 'chunk_0' as the table should be within this chunk.
     */
    public function getBlobIdForTable($tableId)
    {
        $chunk = (floor($tableId / self::NUM_TABLES_IN_CHUNK));
        $start = $chunk * self::NUM_TABLES_IN_CHUNK;
        $end   = $start + self::NUM_TABLES_IN_CHUNK - 1;

        return self::ARCHIVE_APPENDIX_SUBTABLES . '_' . $start . '_' . $end;
    }

    /**
     * Checks whether a BlobId belongs to a chunk or not.
     * @param string|int $blobId   eg "1" (for subtableId "1", not a chunk) or "chunk_4" which is a blob id for a chunk
     * @return bool  true of it starts with "chunk_"
     */
    public function isBlobIdAChunk($blobId)
    {
        return strpos($blobId, self::ARCHIVE_APPENDIX_SUBTABLES . '_') === 0;
    }

    /**
     * Moves the given blobs into chunks and assigns a proper blobId.
     *
     * @param array $blobs  An array containg a mapping of tableIds to blobs. Eg array(0 => 'blob', 1 => 'subtableBlob', ...)
     * @return array        An array where each blob is moved into a chunk, indexed by BlobId.
     *                      eg array('chunk_0' => array(0 => 'blob', 1 => 'subtableBlob', ...), 'chunk_1' => array(...))
     */
    public function moveArchiveBlobsIntoChunks($blobs)
    {
        $chunks = array();

        foreach ($blobs as $tableId => $blob) {
            $blobId = $this->getBlobIdForTable($tableId);

            if (!array_key_exists($blobId, $chunks)) {
                $chunks[$blobId] = array();
            }

            $chunks[$blobId][$tableId] = $blob;
        }

        return $chunks;
    }

    /**
     * Detects whether a recordName like 'Actions_ActionUrls_chunk_5' or 'Actions_ActionUrls' belongs to a chunk or not.
     *
     * To be a valid recordName that belongs to a chunk it must end with '_chunk_NUMERIC'.
     *
     * @param string $recordName
     * @return bool
     */
    public function isRecordNameAChunk($recordName)
    {
        $posAppendix = $this->getEndPosOfChunkAppendix($recordName);

        if (false === $posAppendix) {
            return false;
        }

        // will contain "0_99" of "chunk_0_99"
        $blobId = substr($recordName, $posAppendix);

        return $this->isChunkRange($blobId);
    }

    private function isChunkRange($blobId)
    {
        $blobId = explode('_', $blobId);

        return 2 === count($blobId) && is_numeric($blobId[0]) && is_numeric($blobId[1]);
    }

    /**
     * When having a record like 'Actions_ActionUrls_chunk_5" it will return the raw recordName 'Actions_ActionUrls'.
     *
     * @param  string $recordName
     * @return string
     */
    public function getRecordNameWithoutChunkAppendix($recordName)
    {
        if (!$this->isRecordNameAChunk($recordName)) {
            return $recordName;
        }

        $posAppendix = $this->getStartPosOfChunkAppendix($recordName);

        if (false === $posAppendix) {
            return $recordName;
        }

        return substr($recordName, 0, $posAppendix);
    }

    public function getAppendix()
    {
        return '_' . self::ARCHIVE_APPENDIX_SUBTABLES . '_';
    }

    private function getStartPosOfChunkAppendix($recordName)
    {
        return strpos($recordName, $this->getAppendix());
    }

    private function getEndPosOfChunkAppendix($recordName)
    {
        $pos = strpos($recordName, $this->getAppendix());

        if ($pos === false) {
            return false;
        }

        return $pos + strlen($this->getAppendix());
    }
}
