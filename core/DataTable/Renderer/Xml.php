<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Renderer;

use Exception;
use Piwik\DataTable\Map;
use Piwik\DataTable\Renderer;
use Piwik\DataTable;
use Piwik\DataTable\Simple;
use Piwik\Piwik;

/**
 * XML export of a given DataTable.
 * See the tests cases for more information about the XML format (/tests/core/DataTable/Renderer.test.php)
 * Or have a look at the API calls examples.
 *
 * Works with recursive DataTable (when a row can be associated with a subDataTable).
 *
 */
class Xml extends Renderer
{
    /**
     * Computes the dataTable output and returns the string/binary
     *
     * @return string
     */
    public function render()
    {
        return '<?xml version="1.0" encoding="utf-8" ?>' . "\n" . $this->renderTable($this->table);
    }

    /**
     * Converts the given data table to an array
     *
     * @param DataTable|DataTable/Map $table  data table to convert
     * @return array
     */
    protected function getArrayFromDataTable($table)
    {
        if (is_array($table)) {
            return $table;
        }

        $renderer = new Php();
        $renderer->setRenderSubTables($this->isRenderSubtables());
        $renderer->setSerialize(false);
        $renderer->setTable($table);
        $renderer->setHideIdSubDatableFromResponse($this->hideIdSubDatatable);
        return $renderer->flatRender();
    }

    /**
     * Computes the output for the given data table
     *
     * @param DataTable|DataTable/Map $table
     * @param bool $returnOnlyDataTableXml
     * @param string $prefixLines
     * @return array|string
     * @throws Exception
     */
    protected function renderTable($table, $returnOnlyDataTableXml = false, $prefixLines = '')
    {
        $array = $this->getArrayFromDataTable($table);
        if ($table instanceof Map) {
            $out = $this->renderDataTableMap($table, $array, $prefixLines);

            if ($returnOnlyDataTableXml) {
                return $out;
            }
            $out = "<results>\n$out</results>";
            return $out;
        }

        // integer value of ZERO is a value we want to display
        if ($array != 0 && empty($array)) {
            if ($returnOnlyDataTableXml) {
                throw new Exception("Illegal state, what xml shall we return?");
            }
            $out = "<result />";
            return $out;
        }
        if ($table instanceof Simple) {
            if (is_array($array)) {
                $out = $this->renderDataTableSimple($array);
            } else {
                $out = $array;
            }
            if ($returnOnlyDataTableXml) {
                return $out;
            }

            if (is_array($array)) {
                $out = "<result>\n" . $out . "</result>";
            } else {
                $value = self::formatValueXml($out);
                if ($value === '') {
                    $out = "<result />";
                } else {
                    $out = "<result>" . $value . "</result>";
                }
            }
            return $out;
        }

        if ($table instanceof DataTable) {
            $out = $this->renderDataTable($array);
            if ($returnOnlyDataTableXml) {
                return $out;
            }
            $out = "<result>\n$out</result>";
            return $out;
        }

        if (is_array($array)) {
            $out = $this->renderArray($array, $prefixLines . "\t");
            if ($returnOnlyDataTableXml) {
                return $out;
            }
            return "<result>\n$out</result>";
        }
    }

    /**
     * Renders an array as XML.
     *
     * @param array $array The array to render.
     * @param string $prefixLines The string to prefix each line in the output.
     * @return string
     */
    private function renderArray($array, $prefixLines)
    {
        $isAssociativeArray = Piwik::isAssociativeArray($array);

        // check if array contains arrays, and if not wrap the result in an extra <row> element
        // (only check if this is the root renderArray call)
        // NOTE: this is for backwards compatibility. before, array's were added to a new DataTable.
        // if the array had arrays, they were added as multiple rows, otherwise it was treated as
        // one row. removing will change API output.
        $wrapInRow = $prefixLines === "\t"
            && self::shouldWrapArrayBeforeRendering($array, $wrapSingleValues = false, $isAssociativeArray);

        // render the array
        $result = "";
        if ($wrapInRow) {
            $result .= "$prefixLines<row>\n";
            $prefixLines .= "\t";
        }
        foreach ($array as $key => $value) {
            // based on the type of array & the key, determine how this node will look
            if ($isAssociativeArray) {
                if (strpos($key, '=') !== false) {
                    list($keyAttributeName, $key) = explode('=', $key, 2);

                    $prefix = "<row $keyAttributeName=\"$key\">";
                    $suffix = "</row>";
                    $emptyNode = "<row $keyAttributeName=\"$key\">";
                } elseif (!self::isValidXmlTagName($key)) {
                    $prefix = "<row key=\"$key\">";
                    $suffix = "</row>";
                    $emptyNode = "<row key=\"$key\"/>";
                } else {
                    $prefix = "<$key>";
                    $suffix = "</$key>";
                    $emptyNode = "<$key />";
                }
            } else {
                $prefix = "<row>";
                $suffix = "</row>";
                $emptyNode = "<row/>";
            }

            // render the array item
            if (is_array($value)) {
                $result .= $prefixLines . $prefix . "\n";
                $result .= $this->renderArray($value, $prefixLines . "\t");
                $result .= $prefixLines . $suffix . "\n";
            } elseif ($value instanceof DataTable
                || $value instanceof Map
            ) {
                if ($value->getRowsCount() == 0) {
                    $result .= $prefixLines . $emptyNode . "\n";
                } else {
                    $result .= $prefixLines . $prefix . "\n";
                    if ($value instanceof Map) {
                        $result .= $this->renderDataTableMap($value, $this->getArrayFromDataTable($value), $prefixLines);
                    } elseif ($value instanceof Simple) {
                        $result .= $this->renderDataTableSimple($this->getArrayFromDataTable($value), $prefixLines);
                    } else {
                        $result .= $this->renderDataTable($this->getArrayFromDataTable($value), $prefixLines);
                    }
                    $result .= $prefixLines . $suffix . "\n";
                }
            } else {
                $xmlValue = self::formatValueXml($value);
                if (strlen($xmlValue) != 0) {
                    $result .= $prefixLines . $prefix . $xmlValue . $suffix . "\n";
                } else {
                    $result .= $prefixLines . $emptyNode . "\n";
                }
            }
        }
        if ($wrapInRow) {
            $result .= substr($prefixLines, 0, strlen($prefixLines) - 1) . "</row>\n";
        }
        return $result;
    }

    /**
     * Computes the output for the given data table array
     *
     * @param Map $table
     * @param array $array
     * @param string $prefixLines
     * @return string
     */
    protected function renderDataTableMap($table, $array, $prefixLines = "")
    {
        // CASE 1
        //array
        //  'day1' => string '14' (length=2)
        //  'day2' => string '6' (length=1)
        $firstTable = current($array);
        if (!is_array($firstTable)) {
            $xml = '';
            $nameDescriptionAttribute = $table->getKeyName();
            foreach ($array as $valueAttribute => $value) {
                if (empty($value)) {
                    $xml .= $prefixLines . "\t<result $nameDescriptionAttribute=\"$valueAttribute\" />\n";
                } elseif ($value instanceof Map) {
                    $out = $this->renderTable($value, true);
                    //TODO somehow this code is not tested, cover this case
                    $xml .= "\t<result $nameDescriptionAttribute=\"$valueAttribute\">\n$out</result>\n";
                } else {
                    $xml .= $prefixLines . "\t<result $nameDescriptionAttribute=\"$valueAttribute\">" . self::formatValueXml($value) . "</result>\n";
                }
            }
            return $xml;
        }

        $subTables = $table->getDataTables();
        $firstTable = current($subTables);

        // CASE 2
        //array
        //  'day1' =>
        //    array
        //      'nb_uniq_visitors' => string '18'
        //      'nb_visits' => string '101'
        //  'day2' =>
        //    array
        //      'nb_uniq_visitors' => string '28'
        //      'nb_visits' => string '11'
        if ($firstTable instanceof Simple) {
            $xml = '';
            $nameDescriptionAttribute = $table->getKeyName();
            foreach ($array as $valueAttribute => $dataTableSimple) {
                if (count($dataTableSimple) == 0) {
                    $xml .= $prefixLines . "\t<result $nameDescriptionAttribute=\"$valueAttribute\" />\n";
                } else {
                    if (is_array($dataTableSimple)) {
                        $dataTableSimple = "\n" . $this->renderDataTableSimple($dataTableSimple, $prefixLines . "\t") . $prefixLines . "\t";
                    }
                    $xml .= $prefixLines . "\t<result $nameDescriptionAttribute=\"$valueAttribute\">" . $dataTableSimple . "</result>\n";
                }
            }
            return $xml;
        }

        // CASE 3
        //array
        //  'day1' =>
        //    array
        //      0 =>
        //        array
        //          'label' => string 'phpmyvisites'
        //          'nb_uniq_visitors' => int 11
        //          'nb_visits' => int 13
        //      1 =>
        //        array
        //          'label' => string 'phpmyvisits'
        //          'nb_uniq_visitors' => int 2
        //          'nb_visits' => int 2
        //  'day2' =>
        //    array
        //      0 =>
        //        array
        //          'label' => string 'piwik'
        //          'nb_uniq_visitors' => int 121
        //          'nb_visits' => int 130
        //      1 =>
        //        array
        //          'label' => string 'piwik bis'
        //          'nb_uniq_visitors' => int 20
        //          'nb_visits' => int 120
        if ($firstTable instanceof DataTable) {
            $xml = '';
            $nameDescriptionAttribute = $table->getKeyName();
            foreach ($array as $keyName => $arrayForSingleDate) {
                $dataTableOut = $this->renderDataTable($arrayForSingleDate, $prefixLines . "\t");
                if (empty($dataTableOut)) {
                    $xml .= $prefixLines . "\t<result $nameDescriptionAttribute=\"$keyName\" />\n";
                } else {
                    $xml .= $prefixLines . "\t<result $nameDescriptionAttribute=\"$keyName\">\n";
                    $xml .= $dataTableOut;
                    $xml .= $prefixLines . "\t</result>\n";
                }
            }
            return $xml;
        }

        if ($firstTable instanceof Map) {
            $xml = '';
            $tables = $table->getDataTables();
            $nameDescriptionAttribute = $table->getKeyName();
            foreach ($tables as $valueAttribute => $tableInArray) {
                $out = $this->renderTable($tableInArray, true, $prefixLines . "\t");
                $xml .= $prefixLines . "\t<result $nameDescriptionAttribute=\"$valueAttribute\">\n" . $out . $prefixLines . "\t</result>\n";
            }
            return $xml;
        }

        return '';
    }

    /**
     * Computes the output for the given data array
     *
     * @param array $array
     * @param string $prefixLine
     * @return string
     */
    protected function renderDataTable($array, $prefixLine = "")
    {
        $columnsHaveInvalidChars = $this->areTableLabelsInvalidXmlTagNames(reset($array));

        $out = '';
        foreach ($array as $rowId => $row) {
            if (!is_array($row)) {
                $value = self::formatValueXml($row);
                if (strlen($value) == 0) {
                    $out .= $prefixLine . "\t\t<$rowId />\n";
                } else {
                    $out .= $prefixLine . "\t\t<$rowId>" . $value . "</$rowId>\n";
                }
                continue;
            }

            // Handing case idgoal=7, creating a new array for that one
            $rowAttribute = '';
            if (strstr($rowId, '=') !== false) {
                $rowAttribute = explode('=', $rowId);
                $rowAttribute = " " . $rowAttribute[0] . "='" . $rowAttribute[1] . "'";
            }
            $out .= $prefixLine . "\t<row$rowAttribute>";

            if (count($row) === 1
                && key($row) === 0
            ) {
                $value = self::formatValueXml(current($row));
                $out .= $prefixLine . $value;
            } else {
                $out .= "\n";
                foreach ($row as $name => $value) {
                    // handle the recursive dataTable case by XML outputting the recursive table
                    if (is_array($value)) {
                        $value = "\n" . $this->renderDataTable($value, $prefixLine . "\t\t");
                        $value .= $prefixLine . "\t\t";
                    } else {
                        $value = self::formatValueXml($value);
                    }

                    list($tagStart, $tagEnd) = $this->getTagStartAndEndFor($name, $columnsHaveInvalidChars);

                    if (strlen($value) == 0) {
                        $out .= $prefixLine . "\t\t<$tagStart />\n";
                    } else {
                        $out .= $prefixLine . "\t\t<$tagStart>" . $value . "</$tagEnd>\n";
                    }
                }
                $out .= "\t";
            }
            $out .= $prefixLine . "</row>\n";
        }
        return $out;
    }

    /**
     * Computes the output for the given data array (representing a simple data table)
     *
     * @param $array
     * @param string $prefixLine
     * @return string
     */
    protected function renderDataTableSimple($array, $prefixLine = "")
    {
        if (!is_array($array)) {
            $array = array('value' => $array);
        }

        $columnsHaveInvalidChars = $this->areTableLabelsInvalidXmlTagNames($array);

        $out = '';
        foreach ($array as $keyName => $value) {
            $xmlValue = self::formatValueXml($value);
            list($tagStart, $tagEnd) = $this->getTagStartAndEndFor($keyName, $columnsHaveInvalidChars);
            if (strlen($xmlValue) == 0) {
                $out .= $prefixLine . "\t<$tagStart />\n";
            } else {
                $out .= $prefixLine . "\t<$tagStart>" . $xmlValue . "</$tagEnd>\n";
            }
        }
        return $out;
    }

    /**
     * Returns true if a string is a valid XML tag name, false if otherwise.
     *
     * @param string $str
     * @return bool
     */
    private static function isValidXmlTagName($str)
    {
        static $validTagRegex = null;

        if ($validTagRegex === null) {
            $invalidTagChars = "!\"#$%&'()*+,\\/;<=>?@[\\]\\\\^`{|}~";
            $invalidTagStartChars = $invalidTagChars . "\\-.0123456789";
            $validTagRegex = "/^[^" . $invalidTagStartChars . "][^" . $invalidTagChars . "]*$/";
        }

        $result = preg_match($validTagRegex, $str);
        return !empty($result);
    }

    private function areTableLabelsInvalidXmlTagNames($rowArray)
    {
        if (!empty($rowArray)) {
            foreach ($rowArray as $name => $value) {
                if (!self::isValidXmlTagName($name)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getTagStartAndEndFor($keyName, $columnsHaveInvalidChars)
    {
        if ($columnsHaveInvalidChars) {
            $tagStart = "col name=\"" . self::formatValueXml($keyName) . "\"";
            $tagEnd = "col";
        } else {
            $tagStart = $tagEnd = $keyName;
        }

        return array($tagStart, $tagEnd);
    }
}
