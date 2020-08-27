<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * Execute a callback for each row of a {@link DataTable} passing certain column values and metadata
 * as metadata, and replaces row metadata with the callback result.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('MetadataCallbackReplace', array('url', function ($url) {
 *         return $url . '#index';
 *     }));
 *
 * @api
 */
class MetadataCallbackReplace extends ColumnCallbackReplace
{
    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable that will eventually be filtered.
     * @param array|string $metadataToFilter The metadata whose values should be passed to the callback
     *                                       and then replaced with the callback's result.
     * @param callable $functionToApply The function to execute. Must take the metadata value as a parameter
     *                                  and return a value that will be used to replace the original.
     * @param array|null $functionParameters deprecated - use an [anonymous function](http://php.net/manual/en/functions.anonymous.php)
     *                                       instead.
     * @param array $extraColumnParameters Extra column values that should be passed to the callback, but
     *                                     shouldn't be replaced.
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
