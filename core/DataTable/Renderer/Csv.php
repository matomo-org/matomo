<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Renderer;

use Piwik\Common;
use Piwik\DataTable\Renderer;
use Piwik\DataTable\Simple;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\ProxyHttp;

/**
 * CSV export
 *
 * When rendered using the default settings, a CSV report has the following characteristics:
 * The first record contains headers for all the columns in the report.
 * All rows have the same number of columns.
 * The default field delimiter string is a comma (,).
 * Formatting and layout are ignored.
 *
 */
class Csv extends Renderer
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
     * This string is also hardcoded in archive,sh
     */
    const NO_DATA_AVAILABLE = 'No data available';

    /**
     * Computes the dataTable output and returns the string/binary
     *
     * @return string
     */
    public function render()
    {
        $str = $this->renderTable($this->table);
        if (empty($str)) {
            return self::NO_DATA_AVAILABLE;
        }

        $this->renderHeader();

        $str = $this->convertToUnicode($str);
        return $str;
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
     * @param DataTable|array $table
     * @param array $allColumns
     * @return string
     */
    protected function renderTable($table, &$allColumns = array())
    {
        if (is_array($table)) // convert array to DataTable
        {
            $table = DataTable::makeFromSimpleArray($table);
        }

        if ($table instanceof DataTable\Map) {
            $str = $this->renderDataTableMap($table, $allColumns);
        } else {
            $str = $this->renderDataTable($table, $allColumns);
        }
        return $str;
    }

    /**
     * Computes the output of the given data table array
     *
     * @param DataTable\Map $table
     * @param array $allColumns
     * @return string
     */
    protected function renderDataTableMap($table, &$allColumns = array())
    {
        $str = '';
        foreach ($table->getDataTables() as $currentLinePrefix => $dataTable) {
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
     * @param DataTable|Simple $table
     * @param array $allColumns
     * @return string
     */
    protected function renderDataTable($table, &$allColumns = array())
    {
        if ($table instanceof Simple) {
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

        $csv = $this->makeArrayFromDataTable($table, $allColumns);

        // now we make sure that all the rows in the CSV array have all the columns
        foreach ($csv as &$row) {
            foreach ($allColumns as $columnName => $true) {
                if (!isset($row[$columnName])) {
                    $row[$columnName] = '';
                }
            }
        }

        $str = $this->buildCsvString($allColumns, $csv);
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

        foreach ($columnMetrics as &$value) {
            $value = $this->formatValue($value);
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
        $fileName = 'Piwik ' . Piwik::translate('General_Export');

        $period = Common::getRequestVar('period', false);
        $date = Common::getRequestVar('date', false);
        if ($period || $date) // in test cases, there are no request params set
        {
            if ($period == 'range') {
                $period = new Range($period, $date);
            } else if (strpos($date, ',') !== false) {
                $period = new Range('range', $date);
            } else {
                $period = Period\Factory::build($period, Date::factory($date));
            }

            $prettyDate = $period->getLocalizedLongString();

            $meta = $this->getApiMetaData();

            $fileName .= ' _ ' . $meta['name']
                . ' _ ' . $prettyDate . '.csv';
        }

        // silent fail otherwise unit tests fail
        Common::sendHeader('Content-Disposition: attachment; filename="' . $fileName . '"', true);
        ProxyHttp::overrideCacheControlHeaders();
    }

    /**
     * Flattens an array of column values so they can be outputted as CSV (which does not support
     * nested structures).
     */
    private function flattenColumnArray($columns, &$csvRow = array(), $csvColumnNameTemplate = '%s')
    {
        foreach ($columns as $name => $value) {
            $csvName = sprintf($csvColumnNameTemplate, $this->getCsvColumnName($name));

            if (is_array($value)) {
                // if we're translating column names and this is an array of arrays, the column name
                // format becomes a bit more complicated. also in this case, we assume $value is not
                // nested beyond 2 levels (ie, array(0 => array(0 => 1, 1 => 2)), but not array(
                // 0 => array(0 => array(), 1 => array())) )
                if ($this->translateColumnNames
                    && is_array(reset($value))
                ) {
                    foreach ($value as $level1Key => $level1Value) {
                        $inner = $name == 'goals' ? Piwik::translate('Goals_GoalX', $level1Key) : $name . ' ' . $level1Key;
                        $columnNameTemplate = '%s (' . $inner . ')';

                        $this->flattenColumnArray($level1Value, $csvRow, $columnNameTemplate);
                    }
                } else {
                    $this->flattenColumnArray($value, $csvRow, $csvName . '_%s');
                }
            } else {
                $csvRow[$csvName] = $value;
            }
        }

        return $csvRow;
    }

    private function getCsvColumnName($name)
    {
        if ($this->translateColumnNames) {
            return $this->translateColumnName($name);
        } else {
            return $name;
        }
    }

    /**
     * @param $allColumns
     * @param $csv
     * @return array
     */
    private function buildCsvString($allColumns, $csv)
    {
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
     * @param $table
     * @param $allColumns
     * @return array of csv data
     */
    private function makeArrayFromDataTable($table, &$allColumns)
    {
        $csv = array();
        foreach ($table->getRows() as $row) {
            $csvRow = $this->flattenColumnArray($row->getColumns());

            if ($this->exportMetadata) {
                $metadata = $row->getMetadata();
                foreach ($metadata as $name => $value) {
                    if ($name == 'idsubdatatable_in_db') {
                        continue;
                    }
                    //if a metadata and a column have the same name make sure they dont overwrite
                    if ($this->translateColumnNames) {
                        $name = Piwik::translate('General_Metadata') . ': ' . $name;
                    } else {
                        $name = 'metadata_' . $name;
                    }

                    $csvRow[$name] = $value;
                }
            }

            foreach ($csvRow as $name => $value) {
                $allColumns[$name] = true;
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
        return $csv;
    }

    /**
     * @param $str
     * @return string
     */
    private function convertToUnicode($str)
    {
        if ($this->convertToUnicode
            && function_exists('mb_convert_encoding')
        ) {
            $str = chr(255) . chr(254) . mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
        }
        return $str;
    }
}
