<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Renderer;

use Piwik\Common;
use Piwik\DataTable\Renderer;
use Piwik\DataTable;

/**
 * JSON export.
 * Works with recursive DataTable (when a row can be associated with a subDataTable).
 *
 */
class Json extends Renderer
{
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
     * Computes the output for the given data table
     *
     * @param DataTable $table
     * @return string
     */
    protected function renderTable($table)
    {
        if (is_array($table)) {
            $array = $table;
            if (self::shouldWrapArrayBeforeRendering($array, $wrapSingleValues = true)) {
                $array = array($array);
            }

            foreach ($array as $key => $tab) {
                if ($tab instanceof DataTable\Map
                    || $tab instanceof DataTable
                    || $tab instanceof DataTable\Simple) {
                    $array[$key] = $this->convertDataTableToArray($tab);

                    if (!is_array($array[$key])) {
                        $array[$key] = array('value' => $array[$key]);
                    }
                }
            }
        } else {
            $array = $this->convertDataTableToArray($table);
        }

        if (!is_array($array)) {
            $array = array('value' => $array);
        }

        // convert datatable column/metadata values
        $this->convertDataTableColumnMetadataValues($array);

        // decode all entities
        $callback = function (&$value, $key) {
            if (is_string($value)) {
                $value = html_entity_decode($value, ENT_QUOTES, "UTF-8");
            };
        };
        array_walk_recursive($array, $callback);

        // silence "Warning: json_encode(): Invalid UTF-8 sequence in argument"
        $str = @json_encode($array);

        if ($str === false
            && json_last_error() === JSON_ERROR_UTF8
            && $this->canMakeArrayUtf8()) {
            $array = $this->makeArrayUtf8($array);
            $str = json_encode($array);
        }

        return $str;
    }

    private function canMakeArrayUtf8()
    {
        return function_exists('mb_convert_encoding');
    }

    private function makeArrayUtf8($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::makeArrayUtf8($value);
            }
        } elseif (is_string($array)) {
            return mb_convert_encoding($array, 'UTF-8', 'auto');
        }

        return $array;
    }

    public static function sendHeaderJSON()
    {
        Common::sendHeader('Content-Type: application/json; charset=utf-8');
    }

    private function convertDataTableColumnMetadataValues(&$table)
    {
        if (empty($table)) {
            return;
        }

        array_walk_recursive($table, function (&$value, $key) {
            if ($value instanceof DataTable) {
                $value = $this->convertDataTableToArray($value);
                $this->convertDataTableColumnMetadataValues($value);
            }
        });
    }
}
