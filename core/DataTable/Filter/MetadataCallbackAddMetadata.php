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
 * Add a new metadata to the table based on the value resulting
 * from a callback function with the parameter being another metadata value
 *
 * For example for the searchEngine we have a "metadata" information that gives
 * the URL of the search engine. We use this URL to add a new "metadata" that gives
 * the path of the logo for this search engine URL (which has the format URL.png).
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_MetadataCallbackAddMetadata extends Piwik_DataTable_Filter
{
    private $metadataToRead;
    private $functionToApply;
    private $metadataToAdd;
    private $applyToSummaryRow;

    /**
     * @param Piwik_DataTable $table
     * @param string|array $metadataToRead
     * @param string $metadataToAdd
     * @param callback $functionToApply
     * @param bool $applyToSummaryRow
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
     * @param Piwik_DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $key => $row) {
            if (!$this->applyToSummaryRow && $key == Piwik_DataTable::ID_SUMMARY_ROW) {
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
