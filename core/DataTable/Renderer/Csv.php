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
 * CSV export
 *
 * When rendered using the default settings, a CSV report has the following characteristics:
 * The first record contains headers for all the columns in the report.
 * All rows have the same number of columns.
 * The default field delimiter string is a comma (,).
 * Formatting and layout are ignored.
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Renderer_Csv extends Piwik_DataTable_Renderer
{
    /**
     * Column separator
     *
     * @var string
     */
    public $separator = ",";

    /**
     * Line end
     *
     * @var string
     */
    public $lineEnd = "\n";

    /**
     * 'metadata' columns will be exported, prefixed by 'metadata_'
     *
     * @var bool
     */
    public $exportMetadata = true;

    /**
     * Converts the content to unicode so that UTF8 characters (eg. chinese) can be imported in Excel
     *
     * @var bool
     */
    public $convertToUnicode = true;

    /**
     * idSubtable will be exported in a column called 'idsubdatatable'
     *
     * @var bool
     */
    public $exportIdSubtable = true;

    /**
     * Computes the dataTable output and returns the string/binary
     *
     * @return string
     */
    public function render()
    {
        $str = $this->renderTable($this->table);
        if (empty($str)) {
            return 'No data available';
        }

        $this->renderHeader();

        if ($this->convertToUnicode
            && function_exists('mb_convert_encoding')
        ) {
            $str = chr(255) . chr(254) . mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
        }
        return $str;
    }

    /**
     * Computes the exception output and returns the string/binary
     *
     * @return string
     */
    function renderException()
    {
        @header('Content-Type: text/html; charset=utf-8');
        $exceptionMessage = $this->getExceptionMessage();
        return 'Error: ' . $exceptionMessage;
    }

    /**
     * Enables / Disables unicode converting
     *
     * @param $bool
     */
    public function setConvertToUnicode($bool)
    {
        $this->convertToUnicode = $bool;
    }

    /**
     * Sets the column separator
     *
     * @param $separator
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    /**
     * Computes the output of the given data table
     *
     * @param Piwik_DataTable|array $table
     * @param array $allColumns
     * @return string
     */
    protected function renderTable($table, &$allColumns = array())
    {
        if (is_array($table)) // convert array to DataTable
        {
            $table = Piwik_DataTable::makeFromSimpleArray($table);
        }

        if ($table instanceof Piwik_DataTable_Array) {
            $str = $this->renderDataTableArray($table, $allColumns);
        } else {
            $str = $this->renderDataTable($table, $allColumns);
        }
        return $str;
    }

    /**
     * Computes the output of the given data table array
     *
     * @param Piwik_DataTable_Array $table
     * @param array $allColumns
     * @return string
     */
    protected function renderDataTableArray($table, &$allColumns = array())
    {
        $str = '';
        foreach ($table->getArray() as $currentLinePrefix => $dataTable) {
            $returned = explode("\n", $this->renderTable($dataTable, $allColumns));

            // get rid of the columns names
            $returned = array_slice($returned, 1);

            // case empty datatable we dont print anything in the CSV export
            // when in xml we would output <result date="2008-01-15" />
            if (!empty($returned)) {
                foreach ($returned as &$row) {
                    $row = $currentLinePrefix . $this->separator . $row;
                }
                $str .= "\n" . implode("\n", $returned);
            }
        }

        // prepend table key to column list
        $allColumns = array_merge(array($table->getKeyName() => true), $allColumns);

        // add header to output string
        $str = $this->getHeaderLine(array_keys($allColumns)) . $str;

        return $str;
    }

    /**
     * Converts the output of the given simple data table
     *
     * @param Piwik_DataTable_Simple $table
     * @param array $allColumns
     * @return string
     */
    protected function renderDataTable($table, &$allColumns = array())
    {
        if ($table instanceof Piwik_DataTable_Simple) {
            $row = $table->getFirstRow();
            if ($row !== false) {
                $columnNameToValue = $row->getColumns();
                if (count($columnNameToValue) == 1) {
                    // simple tables should only have one column, the value
                    $allColumns['value'] = true;

                    $value = array_values($columnNameToValue);
                    $str = 'value' . $this->lineEnd . $this->formatValue($value[0]);
                    return $str;
                }
            }
        }
        $csv = array();
        foreach ($table->getRows() as $row) {
            $csvRow = array();

            $columns = $row->getColumns();
            foreach ($columns as $name => $value) {
                //goals => array( 'idgoal=1' =>array(..), 'idgoal=2' => array(..))
                if (is_array($value)) {
                    foreach ($value as $key => $subValues) {
                        if (is_array($subValues)) {
                            foreach ($subValues as $subKey => $subValue) {
                                if ($this->translateColumnNames) {
                                    $subName = $name != 'goals' ? $name . ' ' . $key
                                        : Piwik_Translate('Goals_GoalX', $key);
                                    $columnName = $this->translateColumnName($subKey)
                                        . ' (' . $subName . ')';
                                } else {
                                    // goals_idgoal=1
                                    $columnName = $name . "_" . $key . "_" . $subKey;
                                }
                                $allColumns[$columnName] = true;
                                $csvRow[$columnName] = $subValue;
                            }
                        }
                    }
                } else {
                    $allColumns[$name] = true;
                    $csvRow[$name] = $value;
                }
            }

            if ($this->exportMetadata) {
                $metadata = $row->getMetadata();
                foreach ($metadata as $name => $value) {
                    if ($name == 'idsubdatatable_in_db') {
                        continue;
                    }
                    //if a metadata and a column have the same name make sure they dont overwrite
                    if ($this->translateColumnNames) {
                        $name = Piwik_Translate('General_Metadata') . ': ' . $name;
                    } else {
                        $name = 'metadata_' . $name;
                    }

                    $allColumns[$name] = true;
                    $csvRow[$name] = $value;
                }
            }

            if ($this->exportIdSubtable) {
                $idsubdatatable = $row->getIdSubDataTable();
                if ($idsubdatatable !== false
                    && $this->hideIdSubDatatable === false
                ) {
                    $csvRow['idsubdatatable'] = $idsubdatatable;
                }
            }

            $csv[] = $csvRow;
        }

        // now we make sure that all the rows in the CSV array have all the columns
        foreach ($csv as &$row) {
            foreach ($allColumns as $columnName => $true) {
                if (!isset($row[$columnName])) {
                    $row[$columnName] = '';
                }
            }
        }

        $str = '';

        // specific case, we have only one column and this column wasn't named properly (indexed by a number)
        // we don't print anything in the CSV file => an empty line
        if (sizeof($allColumns) == 1
            && reset($allColumns)
            && !is_string(key($allColumns))
        ) {
            $str .= '';
        } else {
            // render row names
            $str .= $this->getHeaderLine(array_keys($allColumns)) . $this->lineEnd;
        }

        // we render the CSV
        foreach ($csv as $theRow) {
            $rowStr = '';
            foreach ($allColumns as $columnName => $true) {
                $rowStr .= $this->formatValue($theRow[$columnName]) . $this->separator;
            }
            // remove the last separator
            $rowStr = substr_replace($rowStr, "", -strlen($this->separator));
            $str .= $rowStr . $this->lineEnd;
        }
        $str = substr($str, 0, -strlen($this->lineEnd));
        return $str;
    }

    /**
     * Returns the CSV header line for a set of metrics. Will translate columns if desired.
     *
     * @param array $columnMetrics
     * @return array
     */
    private function getHeaderLine($columnMetrics)
    {
        if ($this->translateColumnNames) {
            $columnMetrics = $this->translateColumnNames($columnMetrics);
        }
        return implode($this->separator, $columnMetrics);
    }

    /**
     * Formats/Escapes the given value
     *
     * @param mixed $value
     * @return string
     */
    protected function formatValue($value)
    {
        if (is_string($value)
            && !is_numeric($value)
        ) {
            $value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
        } elseif ($value === false) {
            $value = 0;
        }
        if (is_string($value)
            && (strpos($value, '"') !== false
                || strpos($value, $this->separator) !== false)
        ) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }

        // in some number formats (e.g. German), the decimal separator is a comma
        // we need to catch and replace this
        if (is_numeric($value)) {
            $value = (string)$value;
            $value = str_replace(',', '.', $value);
        }

        return $value;
    }

    /**
     * Sends the http headers for csv file
     */
    protected function renderHeader()
    {
        $fileName = 'Piwik ' . Piwik_Translate('General_Export');

        $period = Piwik_Common::getRequestVar('period', false);
        $date = Piwik_Common::getRequestVar('date', false);
        if ($period || $date) // in test cases, there are no request params set
        {
            if ($period == 'range') {
                $period = new Piwik_Period_Range($period, $date);
            } else if (strpos($date, ',') !== false) {
                $period = new Piwik_Period_Range('range', $date);
            } else {
                $period = Piwik_Period::factory($period, Piwik_Date::factory($date));
            }

            $prettyDate = $period->getLocalizedLongString();

            $meta = $this->getApiMetaData();

            $fileName .= ' _ ' . $meta['name']
                . ' _ ' . $prettyDate . '.csv';
        }

        // silent fail otherwise unit tests fail
        @header('Content-Type: application/vnd.ms-excel');
        @header('Content-Disposition: attachment; filename="' . $fileName . '"');
        Piwik::overrideCacheControlHeaders();
    }
}
