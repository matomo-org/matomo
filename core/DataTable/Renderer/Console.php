<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Renderer;

use Piwik\DataTable;
use Piwik\DataTable\Renderer;

/**
 * Simple output
 */
class Console extends Renderer
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
        return $this->renderTable($this->table);
    }

    /**
     * Sets the prefix to be used
     *
     * @param string $str new prefix
     */
    public function setPrefixRow($str)
    {
        $this->prefixRows = $str;
    }

    /**
     * Computes the output of the given array of data tables
     *
     * @param DataTable\Map $map data tables to render
     * @param string $prefix prefix to output before table data
     * @return string
     */
    protected function renderDataTableMap(DataTable\Map $map, $prefix)
    {
        $output = "Set<hr />";
        $prefix = $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        foreach ($map->getDataTables() as $descTable => $table) {
            $output .= $prefix . "<b>" . $descTable . "</b><br />";
            $output .= $prefix . $this->renderTable($table, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
            $output .= "<hr />";
        }
        return $output;
    }

    /**
     * Computes the given dataTable output and returns the string/binary
     *
     * @param DataTable $table data table to render
     * @param string $prefix prefix to output before table data
     * @return string
     */
    protected function renderTable($table, $prefix = "")
    {
        if (is_array($table)) {
            // convert array to DataTable

            $table = DataTable::makeFromSimpleArray($table);
        }

        if ($table instanceof DataTable\Map) {
            return $this->renderDataTableMap($table, $prefix);
        }

        if ($table->getRowsCount() == 0) {
            return "Empty table<br />\n";
        }

        static $depth = 0;
        $output = '';
        $i = 1;
        foreach ($table->getRows() as $row) {
            $dataTableMapBreak = false;
            $columns = array();
            foreach ($row->getColumns() as $column => $value) {
                if ($value instanceof DataTable\Map) {
                    $output .= $this->renderDataTableMap($value, $prefix);
                    $dataTableMapBreak = true;
                    break;
                }
                if (is_string($value)) {
                    $value = "'$value'";
                } elseif (is_array($value)) {
                    $value = var_export($value, true);
                }

                $columns[] = "'$column' => $value";
            }
            if ($dataTableMapBreak === true) {
                continue;
            }
            $columns = implode(", ", $columns);

            $metadata = array();
            foreach ($row->getMetadata() as $name => $value) {
                if (is_string($value)) {
                    $value = "'$value'";
                } elseif (is_array($value)) {
                    $value = var_export($value, true);
                }
                $metadata[] = "'$name' => $value";
            }
            $metadata = implode(", ", $metadata);

            $output .= str_repeat($this->prefixRows, $depth)
                . "- $i [" . $columns . "] [" . $metadata . "] [idsubtable = "
                . $row->getIdSubDataTable() . "]<br />\n";

            if (!is_null($row->getIdSubDataTable())) {
                $subTable = $row->getSubtable();
                if ($subTable) {
                    $depth++;
                    $output .= $this->renderTable($subTable, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                    $depth--;
                } else {
                    $output .= "-- Sub DataTable not loaded<br />\n";
                }
            }
            $i++;
        }

        $metadata = $table->getAllTableMetadata();
        if (!empty($metadata)) {
            $output .= "<hr />Metadata<br />";
            foreach ($metadata as $id => $metadataIn) {
                $output .= "<br />";
                $output .= $prefix . " <b>$id</b><br />";
                if (is_array($metadataIn)) {
                    foreach ($metadataIn as $name => $value) {
                        $output .= $prefix . $prefix . "$name => $value";
                    }
                }
            }
        }
        return $output;
    }
}
