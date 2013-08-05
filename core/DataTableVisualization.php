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
            $result = array();

            $lineage = static::getThisVisualizationClassLineage();
            foreach ($lineage as $klass) {
                if (isset($klass::$clientSideParameters)) {
                    $result = array_merge($result, $clientSideParameters);
                }
            }
            
            return $result;
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
            $result = array();

            $lineage = static::getThisVisualizationClassLineage();
            foreach ($lineage as $klass) {
                if (isset($klass::$clientSideProperties)) {
                    $result = array_merge($result, $clientSideProperties);
                }
            }
            
            return $result;
        } else {
            return array();
        }
    }

    /**
     * TODO
     */
    public static function getViewDataTableId()
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
    protected static function getThisVisualizationClassLineage()
    {
        return self::getVisualizationClassLineage(__CLASS__);
    }

    /**
     * TODO
     */
    public static function getVisualizationIdsWithInheritance($klass)
    {
        $klasses = self::getVisualizationClassLineage($klass);
        return array_map(array('Piwik\\Piwik', 'getUnnamespacedClassName'), $klasses);
    }

    /**
     * TODO
     */
    public static function getAvailableVisualizations()
    {
        $visualizations = array();
        Piwik_PostEvent('DataTableVisualization.getAvailable', array(&$visualizations));

        $result = array();
        foreach ($visualizations as $viz) {
            if (is_subclass_of($viz, __CLASS__)) {
                $result[$viz::getViewDataTableId()] = $viz;
            }
        }
        return $result;
    }

    /**
     * TODO
     */
    public static function getClassFromId($id)
    {
        $visualizationClasses = self::getAvailableVisualizations();
        if (!isset($visualizationClasses[$id])) {
            throw new \Exception("Invalid DataTable visualization ID: '$id'.");
        }
        return $visualizationClasses[$id];
    }
}