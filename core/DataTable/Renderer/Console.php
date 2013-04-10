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
 * Simple output
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Renderer_Console extends Piwik_DataTable_Renderer
{
    /**
     * Prefix
     *
     * @var string
     */
    protected $prefixRows = '#';

    /**
     * Computes the dataTable output and returns the string/binary
     *
     * @return string
     */
    public function render()
    {
        $this->renderHeader();
        return $this->renderTable($this->table);
    }

    /**
     * Computes the exception output and returns the string/binary
     *
     * @return string
     */
    public function renderException()
    {
        $this->renderHeader();
        $exceptionMessage = $this->getExceptionMessage();
        return 'Error: ' . $exceptionMessage;
    }

    /**
     * Sets the prefix to be used
     *
     * @param string $str  new prefix
     */
    public function setPrefixRow($str)
    {
        $this->prefixRows = $str;
    }

    /**
     * Computes the output of the given array of data tables
     *
     * @param Piwik_DataTable_Array $tableArray  data tables to render
     * @param string $prefix      prefix to output before table data
     * @return string
     */
    protected function renderDataTableArray(Piwik_DataTable_Array $tableArray, $prefix)
    {
        $output = "Piwik_DataTable_Array<hr />";
        $prefix = $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        foreach ($tableArray->getArray() as $descTable => $table) {
            $output .= $prefix . "<b>" . $descTable . "</b><br />";
            $output .= $prefix . $this->renderTable($table, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
            $output .= "<hr />";
        }
        return $output;
    }

    /**
     * Computes the given dataTable output and returns the string/binary
     *
     * @param Piwik_DataTable $table   data table to render
     * @param string $prefix  prefix to output before table data
     * @return string
     */
    protected function renderTable($table, $prefix = "")
    {
        if (is_array($table)) // convert array to DataTable
        {
            $table = Piwik_DataTable::makeFromSimpleArray($table);
        }

        if ($table instanceof Piwik_DataTable_Array) {
            return $this->renderDataTableArray($table, $prefix);
        }

        if ($table->getRowsCount() == 0) {
            return "Empty table<br />\n";
        }

        static $depth = 0;
        $output = '';
        $i = 1;
        foreach ($table->getRows() as $row) {
            $dataTableArrayBreak = false;
            $columns = array();
            foreach ($row->getColumns() as $column => $value) {
                if ($value instanceof Piwik_DataTable_Array) {
                    $output .= $this->renderDataTableArray($value, $prefix);
                    $dataTableArrayBreak = true;
                    break;
                }
                if (is_string($value)) $value = "'$value'";
                elseif (is_array($value)) $value = var_export($value, true);

                $columns[] = "'$column' => $value";
            }
            if ($dataTableArrayBreak === true) {
                continue;
            }
            $columns = implode(", ", $columns);

            $metadata = array();
            foreach ($row->getMetadata() as $name => $value) {
                if (is_string($value)) $value = "'$value'";
                elseif (is_array($value)) $value = var_export($value, true);
                $metadata[] = "'$name' => $value";
            }
            $metadata = implode(", ", $metadata);

            $output .= str_repeat($this->prefixRows, $depth)
                . "- $i [" . $columns . "] [" . $metadata . "] [idsubtable = "
                . $row->getIdSubDataTable() . "]<br />\n";

            if (!is_null($row->getIdSubDataTable())) {
                if ($row->isSubtableLoaded()) {
                    $depth++;
                    $output .= $this->renderTable(
                        Piwik_DataTable_Manager::getInstance()->getTable(
                            $row->getIdSubDataTable()
                        ),
                        $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                    );
                    $depth--;
                } else {
                    $output .= "-- Sub DataTable not loaded<br />\n";
                }
            }
            $i++;
        }

        if (!empty($table->metadata)) {
            $output .= "<hr />Metadata<br />";
            foreach ($table->metadata as $id => $metadata) {
                $output .= "<br />";
                $output .= $prefix . " <b>$id</b><br />";
                foreach ($metadata as $name => $value) {
                    $output .= $prefix . $prefix . "$name => $value";
                }
            }
        }
        return $output;
    }
}
