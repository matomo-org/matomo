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

        // decode all entities
        $callback = function (&$value, $key) {
            if (is_string($value)) {
                $value = html_entity_decode($value, ENT_QUOTES, "UTF-8");
            };
        };
        array_walk_recursive($array, $callback);

        $str = json_encode($array);

        return $str;
    }

    public static function sendHeaderJSON()
    {
        Common::sendHeader('Content-Type: application/json; charset=utf-8');
    }

    private function convertDataTableToArray($table)
    {
        $renderer = new Php();
        $renderer->setTable($table);
        $renderer->setRenderSubTables($this->isRenderSubtables());
        $renderer->setSerialize(false);
        $renderer->setHideIdSubDatableFromResponse($this->hideIdSubDatatable);
        $array = $renderer->flatRender();

        return $array;
    }
}
