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
    public static function getClientSideParameters()
    {
        if (isset(static::$clientSideParameters)) {
            return static::$clientSideParameters;
        } else {
            return array();
        }
    }

    /**
     * TODO
     */
    public static function getClientSideProperties()
    {
        if (isset(static::$clientSideProperties)) {
            return static::$clientSideProperties;
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

    /**
     * TODO
     */
    public static function getVisualizationClassLineage($klass)
    {
        $klasses = array_merge(array($klass), class_parents($klass, $autoload = false));

        $idx = array_search('Piwik\\DataTableVisualization', $klasses);
        if ($idx !== false) {
            unset($klasses[$idx]);
        }

        return array_reverse($klasses);
    }

    /**
     * TODO
     */
    public static function getVisualizationIdsWithInheritance($klass)
    {
        $klasses = self::getVisualizationClassLineage($klass);
        return array_map(array('Piwik\\Piwik', 'getUnnamespacedClassName'), $klasses);
    }
}