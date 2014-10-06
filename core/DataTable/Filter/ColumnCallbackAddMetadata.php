<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;

/**
 * Executes a callback for each row of a {@link DataTable} and adds the result as a new
 * row metadata value.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik\Plugins\MyPlugin\getLogoFromLabel'));
 *
 * @api
 */
class ColumnCallbackAddMetadata extends BaseFilter
{
    private $columnsToRead;
    private $functionToApply;
    private $functionParameters;
    private $metadataToAdd;
    private $applyToSummaryRow;

    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable instance that will be filtered.
     * @param string|array $columnsToRead The columns to read from each row and pass on to the callback.
     * @param string $metadataToAdd The name of the metadata field that will be added to each row.
     * @param callable $functionToApply The callback to apply for each row.
     * @param array $functionParameters deprecated - use an [anonymous function](http://php.net/manual/en/functions.anonymous.php)
     *                                  instead.
     * @param bool $applyToSummaryRow Whether the callback should be applied to the summary row or not.
     */
    public function __construct($table, $columnsToRead, $metadataToAdd, $functionToApply = null,
                                $functionParameters = null, $applyToSummaryRow = true)
    {
        parent::__construct($table);

        if (!is_array($columnsToRead)) {
            $columnsToRead = array($columnsToRead);
        }

        $this->columnsToRead      = $columnsToRead;
        $this->functionToApply    = $functionToApply;
        $this->functionParameters = $functionParameters;
        $this->metadataToAdd      = $metadataToAdd;
        $this->applyToSummaryRow  = $applyToSummaryRow;
    }

    /**
     * See {@link ColumnCallbackAddMetadata}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $key => $row) {
            if (!$this->applyToSummaryRow && $key == DataTable::ID_SUMMARY_ROW) {
                continue;
            }

            $parameters = array();
            foreach ($this->columnsToRead as $columnsToRead) {
                $parameters[] = $row->getColumn($columnsToRead);
            }

            if (!is_null($this->functionParameters)) {
                $parameters = array_merge($parameters, $this->functionParameters);
            }
            if (!is_null($this->functionToApply)) {
                $newValue = call_user_func_array($this->functionToApply, $parameters);
            } else {
                $newValue = $parameters[0];
            }
            if ($newValue !== false) {
                $row->addMetadata($this->metadataToAdd, $newValue);
            }
        }
    }
}
