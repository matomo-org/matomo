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
 * Add a new 'metadata' column to the table based on the value resulting
 * from a callback function with the parameter being another column's (or several columns') value(s)
 *
 * For example from the "label" column we can to create an "icon" 'metadata' column
 * with the icon URI built from the label (LINUX => UserSettings/icons/linux.png)
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ColumnCallbackAddMetadata extends Piwik_DataTable_Filter
{
    private $columnsToRead;
    private $functionToApply;
    private $functionParameters;
    private $metadataToAdd;
    private $applyToSummaryRow;

    /**
     * @param Piwik_DataTable $table
     * @param string|array    $columnsToRead
     * @param string          $metadataToAdd
     * @param string          $functionToApply
     * @param array           $functionParameters
     * @param bool            $applyToSummaryRow
     */
    public function __construct($table, $columnsToRead, $metadataToAdd, $functionToApply = null,
                                $functionParameters = null, $applyToSummaryRow = true)
    {
        parent::__construct($table);

        if (!is_array($columnsToRead)) {
            $columnsToRead = array($columnsToRead);
        }
        $this->columnsToRead = $columnsToRead;

        $this->functionToApply = $functionToApply;
        $this->functionParameters = $functionParameters;
        $this->metadataToAdd = $metadataToAdd;
        $this->applyToSummaryRow = $applyToSummaryRow;
    }

    /**
     * Filters the given data table
     *
     * @param Piwik_DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $key => $row) {
            if (!$this->applyToSummaryRow && $key == Piwik_DataTable::ID_SUMMARY_ROW) {
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
