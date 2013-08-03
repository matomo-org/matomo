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

namespace Piwik;

use Piwik\DataTable;

/**
 * TODO
 */
abstract class DataTableVisualization
{
    /**
     * TODO
     */
    public abstract function render($dataTable, $properties);

    /**
     * TODO
     */
    public static function getJavaScriptProperties()
    {
        if (isset(static::$javaScriptProperties)) {
            return static::$javaScriptProperties;
        } else {
            return array();
        }
    }

    /**
     * TODO
     */
    public static function getOverridableProperties()
    {
        if (isset(static::$overridableProperties)) {
            return static::$overridableProperties;
        } else {
            return array();
        }
    }

    /**
     * TODO
     */
    public static function getViewDataTableId($view)
    {
        if (defined('static::ID')) {
            return static::ID;
        } else {
            return Piwik::getUnnamespacedClassName($this);
        }
    }
}
