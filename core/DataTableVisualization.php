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
            if (!class_exists($viz)) {
                throw new \Exception(
                    "Invalid visualization class '$viz' found in DataTableVisualization.getAvailableVisualizations.");
            }

            if (is_subclass_of($viz, __CLASS__)) {
                $vizId = $viz::getViewDataTableId();
                if (isset($result[$vizId])) {
                    throw new Exception("Visualization ID '$vizId' is already in use!");
                }

                $result[$vizId] = $viz;
            }
        }
        return $result;
    }

    /**
     * TODO
     */
    public static function getNonCoreVisualizations()
    {
        $result = array();
        foreach (self::getAvailableVisualizations() as $vizId => $vizClass) {
            if (strpos($vizClass, 'Piwik\\Plugins\\CoreVisualizations') === false) {
                $result[$vizId] = $vizClass;
            }
        }
        return $result;
    }

    /**
     * TODO
     */
    public static function getVisualizationInfoFor($visualizations)
    {
        $result = array();
        foreach ($visualizations as $vizId => $vizClass) {
            $result[$vizId] = array('table_icon' => $vizClass::FOOTER_ICON, 'title' => $vizClass::FOOTER_ICON_TITLE);
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