<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Replace a metadata value with a new value resulting
 * from the function called with the metadata's value
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_MetadataCallbackReplace extends Piwik_DataTable_Filter_ColumnCallbackReplace
{
    /**
     * @param Piwik_DataTable $table
     * @param array|string $metadataToFilter
     * @param callback $functionToApply
     * @param null|array $functionParameters
     * @param array $extraColumnParameters
     */
    public function __construct($table, $metadataToFilter, $functionToApply, $functionParameters = null,
                                $extraColumnParameters = array())
    {
        parent::__construct($table, $metadataToFilter, $functionToApply, $functionParameters, $extraColumnParameters);
    }

    /**
     * @param Piwik_DataTable_Row $row
     * @param string $metadataToFilter
     * @param mixed $newValue
     */
    protected function setElementToReplace($row, $metadataToFilter, $newValue)
    {
        $row->setMetadata($metadataToFilter, $newValue);
    }

    /**
     * @param Piwik_DataTable_Row $row
     * @param string $metadataToFilter
     * @return array|false|mixed
     */
    protected function getElementToReplace($row, $metadataToFilter)
    {
        return $row->getMetadata($metadataToFilter);
    }
}
