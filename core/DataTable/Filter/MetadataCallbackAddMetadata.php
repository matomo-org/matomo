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
 * Executes a callback for each row of a {@link DataTable} and adds the result to the
 * row as a metadata value. Only metadata values are passed to the callback.
 *
 * **Basic usage example**
 *
 *     // add a logo metadata based on the url metadata
 *     $dataTable->filter('MetadataCallbackAddMetadata', array('url', 'logo', 'Piwik\Plugins\MyPlugin\getLogoFromUrl'));
 *
 * @api
 */
class MetadataCallbackAddMetadata extends BaseFilter
{
    private $metadataToRead;
    private $functionToApply;
    private $metadataToAdd;
    private $applyToSummaryRow;

    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable that will eventually be filtered.
     * @param string|array $metadataToRead The metadata to read from each row and pass to the callback.
     * @param string $metadataToAdd The name of the metadata to add.
     * @param callable $functionToApply The callback to execute for each row. The result will be
     *                                  added as metadata with the name `$metadataToAdd`.
     * @param bool $applyToSummaryRow True if the callback should be applied to the summary row, false
     *                                if otherwise.
     */
    public function __construct($table, $metadataToRead, $metadataToAdd, $functionToApply,
                                $applyToSummaryRow = true)
    {
        parent::__construct($table);
        $this->functionToApply = $functionToApply;

        if (!is_array($metadataToRead)) {
            $metadataToRead = array($metadataToRead);
        }

        $this->metadataToRead = $metadataToRead;
        $this->metadataToAdd = $metadataToAdd;
        $this->applyToSummaryRow = $applyToSummaryRow;
    }

    /**
     * See {@link MetadataCallbackAddMetadata}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $key => $row) {
            if (!$this->applyToSummaryRow && $key == DataTable::ID_SUMMARY_ROW) {
                continue;
            }

            $params = array();
            foreach ($this->metadataToRead as $name) {
                $params[] = $row->getMetadata($name);
            }

            $newValue = call_user_func_array($this->functionToApply, $params);
            if ($newValue !== false) {
                $row->addMetadata($this->metadataToAdd, $newValue);
            }
        }
    }
}
