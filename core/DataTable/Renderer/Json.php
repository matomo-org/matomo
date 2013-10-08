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
namespace Piwik\DataTable\Renderer;

use Piwik\Common;
use Piwik\DataTable\Renderer;
use Piwik\DataTable;
use Piwik\ProxyHttp;

/**
 * JSON export.
 * Works with recursive DataTable (when a row can be associated with a subDataTable).
 *
 * @package Piwik
 * @subpackage DataTable
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
        $this->renderHeader();
        return $this->renderTable($this->table);
    }

    /**
     * Computes the exception output and returns the string/binary
     *
     * @return string
     */
    function renderException()
    {
        $this->renderHeader();

        $exceptionMessage = $this->getExceptionMessage();
        $exceptionMessage = str_replace(array("\r\n", "\n"), "", $exceptionMessage);
        $exceptionMessage = '{"result":"error", "message":"' . $exceptionMessage . '"}';

        return $this->jsonpWrap($exceptionMessage);
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
        } else {
            $renderer = new Php();
            $renderer->setTable($table);
            $renderer->setRenderSubTables($this->isRenderSubtables());
            $renderer->setSerialize(false);
            $renderer->setHideIdSubDatableFromResponse($this->hideIdSubDatatable);
            $array = $renderer->flatRender();
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

        $str = Common::json_encode($array);

        return $this->jsonpWrap($str);
    }

    /**
     * @param $str
     * @return string
     */
    protected function jsonpWrap($str)
    {
        if (($jsonCallback = Common::getRequestVar('callback', false)) === false)
            $jsonCallback = Common::getRequestVar('jsoncallback', false);
        if ($jsonCallback !== false) {
            if (preg_match('/^[0-9a-zA-Z_]*$/D', $jsonCallback) > 0) {
                $str = $jsonCallback . "(" . $str . ")";
            }
        }

        return $str;
    }

    /**
     * Sends the http header for json file
     */
    protected function renderHeader()
    {
        self::sendHeaderJSON();
        ProxyHttp::overrideCacheControlHeaders();
    }

    public static function sendHeaderJSON()
    {
        @header('Content-Type: application/json; charset=utf-8');
    }
}
