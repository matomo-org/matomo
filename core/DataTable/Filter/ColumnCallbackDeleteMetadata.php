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
use Piwik\DataTable\BaseFilter;

/**
 * Executes a callback for each row of a {@link DataTable} and removes the defined metadata column from each row.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('ColumnCallbackDeleteMetadata', array('segmentValue'));
 *
 * @api
 */
class ColumnCallbackDeleteMetadata extends BaseFilter
{
    private $metadataToRemove;

    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable instance that will be filtered.
     * @param string $metadataToRemove The name of the metadata field that will be removed from each row.
     */
    public function __construct($table, $metadataToRemove)
    {
        parent::__construct($table);

        $this->metadataToRemove = $metadataToRemove;
    }

    /**
     * See {@link ColumnCallbackDeleteMetadata}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $this->enableRecursive(true);

        foreach ($table->getRows() as $row) {
            $row->deleteMetadata($this->metadataToRemove);

            $this->filterSubTable($row);
        }
    }
}
