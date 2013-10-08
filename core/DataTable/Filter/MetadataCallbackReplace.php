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
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * Replace a metadata value with a new value resulting
 * from the function called with the metadata's value
 *
 * @package Piwik
 * @subpackage DataTable
 */
class MetadataCallbackReplace extends ColumnCallbackReplace
{
    /**
     * @param DataTable $table
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
     * @param Row $row
     * @param string $metadataToFilter
     * @param mixed $newValue
     */
    protected function setElementToReplace($row, $metadataToFilter, $newValue)
    {
        $row->setMetadata($metadataToFilter, $newValue);
    }

    /**
     * @param Row $row
     * @param string $metadataToFilter
     * @return array|bool|mixed
     */
    protected function getElementToReplace($row, $metadataToFilter)
    {
        return $row->getMetadata($metadataToFilter);
    }
}
