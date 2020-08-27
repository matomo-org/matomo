<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Archive;

/**
 * This class is used to split blobs of DataTables into chunks. Each blob used to be stored under one blob in the
 * archive table. For better efficiency we do now combine multiple DataTable into one blob entry.
 *
 * Chunks are identified by having the recordName $recordName_chunk_0_99, $recordName_chunk_100_199 (this chunk stores
 * the subtable 100-199).
 */
class Chunk
{
    const ARCHIVE_APPENDIX_SUBTABLES = 'chunk';
    const NUM_TABLES_IN_CHUNK = 100;

    /**
     * Gets the record name to use for a given tableId/subtableId.
     *
     * @param string $recordName eg 'Actions_ActionsUrl'
     * @param int    $tableId    eg '5' for tableId '5'
     * @return string            eg 'Actions_ActionsUrl_chunk_0_99' as the table should be stored under this blob id.
     */
    public function getRecordNameForTableId($recordName, $tableId)
    {
        $chunk = (floor($tableId / self::NUM_TABLES_IN_CHUNK));
        $start = $chunk * self::NUM_TABLES_IN_CHUNK;
        $end   = $start + self::NUM_TABLES_IN_CHUNK - 1;

        return $recordName . $this->getAppendix() . $start . '_' . $end;
    }

    /**
     * Moves the given blobs into chunks and assigns a proper record name containing the chunk number.
     *
     * @param string $recordName The original archive record name, eg 'Actions_ActionsUrl'
     * @param array  $blobs  An array containing a mapping of tableIds to blobs. Eg array(0 => 'blob', 1 => 'subtableBlob', ...)
     * @return array        An array where each blob is moved into a chunk, indexed by recordNames.
     *                      eg array('Actions_ActionsUrl_chunk_0_99'    => array(0 => 'blob', 1 => 'subtableBlob', ...),
     *                               'Actions_ActionsUrl_chunk_100_199' => array(...))
     */
    public function moveArchiveBlobsIntoChunks($recordName, $blobs)
    {
        $chunks = array();

        foreach ($blobs as $tableId => $blob) {
            $name = $this->getRecordNameForTableId($recordName, $tableId);

            if (!array_key_exists($name, $chunks)) {
                $chunks[$name] = array();
            }

            $chunks[$name][$tableId] = $blob;
        }

        return $chunks;
    }

    /**
     * Detects whether a recordName like 'Actions_ActionUrls_chunk_0_99' or 'Actions_ActionUrls' belongs to a
     * chunk or not.
     *
     * To be a valid recordName that belongs to a chunk it must end with '_chunk_NUMERIC_NUMERIC'.
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
     * When having a record like 'Actions_ActionUrls_chunk_0_99" it will return the raw recordName 'Actions_ActionUrls'.
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

    /**
     * Returns the string that is appended to the original record name. This appendix identifes a record name is a
     * chunk.
     * @return string
     */
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
