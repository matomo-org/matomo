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
 * Base class for all DataTable visualizations. Different visualizations are used to
 * handle different values of the viewDataTable query parameter. Each one will display
 * DataTable data in a different way.
 * 
 * TODO: must be more in depth
 */
abstract class DataTableVisualization
{
    /**
     * This event is used to gather all available DataTable visualizations. Callbacks
     * should add visualization class names to the incoming array.
     * 
     * Callback Signature: function (&$visualizations) {}
     */
    const GET_AVAILABLE_EVENT = 'DataTableVisualization.getAvailable';

    /**
     * Rendering function. Must return the view HTML.
     * 
     * @param DataTable|DataTable\Map $dataTable The data.
     * @param array $properties The view properties.
     * @return string The visualization HTML.
     */
    public abstract function render($dataTable, $properties);

    /**
     * Default implementation of getDefaultPropertyValues static function.
     * 
     * @return array
     */
    public static function getDefaultPropertyValues()
    {
        return array();
    }

    /**
     * Returns the array of view properties that a DataTable visualization will require
     * to be both visible to client side JavaScript, and passed along as query parameters
     * in every AJAX request.
     * 
     * Derived DataTableVisualizations can specify client side parameters by declaring
     * a static $clientSideParameters field.
     * 
     * @return array
     */
    public static function getClientSideParameters()
    {
        if (isset(static::$clientSideParameters)) {
            $result = array();

            $lineage = static::getVisualizationClassLineage(get_called_class());
            foreach ($lineage as $klass) {
                if (isset($klass::$clientSideParameters)) {
                    $result = array_merge($result, $klass::$clientSideParameters);
                }
            }
            
            return array_unique($result);
        } else {
            return array();
        }
    }

    /**
     * Returns an array of view property names that a DataTable visualization will
     * require to be visible to client side JavaScript. Unlike 'client side parameters',
     * these will not be passed with AJAX requests as query parameters.
     * 
     * Derived DataTableVisualizations can specify client side properties by declaring
     * a static $clientSideProperties field.
     * 
     * @return array
     */
    public static function getClientSideProperties()
    {
        if (isset(static::$clientSideProperties)) {
            $result = array();

            $lineage = static::getVisualizationClassLineage(get_called_class());
            foreach ($lineage as $klass) {
                if (isset($klass::$clientSideProperties)) {
                    $result = array_merge($result, $klass::$clientSideProperties);
                }
            }
            
            return array_unique($result);
        } else {
            return array();
        }
    }

    /**
     * Returns the viewDataTable ID for this DataTable visualization. Derived classes
     * should declare a const ID field with the viewDataTable ID.
     * 
     * @return string
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
     * Returns the list of parents for a DataTableVisualization class excluding the
     * DataTableVisualization class and above.
     * 
     * @param string $klass The class name of the DataTableVisualization.
     * @return DataTableVisualization[]  The list of parent classes in order from highest
     *                                   ancestor to the descended class.
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
     * Returns the viewDataTable IDs of a visualization's class lineage.
     * 
     * @see self::getVisualizationClassLineage
     * 
     * @param string $klass The visualization class.
     *
     * @return array
     */
    public static function getVisualizationIdsWithInheritance($klass)
    {
        $klasses = self::getVisualizationClassLineage($klass);

        $result = array();
        foreach ($klasses as $klass) {
            $result[] = $klass::getViewDataTableId();
        }
        return $result;
    }

    /**
     * Returns all registered visualization classes. Uses the 'DataTableVisualization.getAvailable'
     * event to retrieve visualizations.
     * 
     * @return array Array mapping visualization IDs with their associated visualization classes.
     * @throws \Exception If a visualization class does not exist or if a duplicate visualization ID
     *                   is found.
     */
    public static function getAvailableVisualizations()
    {
        /** @var self[] $visualizations */
        $visualizations = array();
        Piwik_PostEvent(self::GET_AVAILABLE_EVENT, array(&$visualizations));

        $result = array();
        foreach ($visualizations as $viz) {
            if (!class_exists($viz)) {
                throw new \Exception(
                    "Invalid visualization class '$viz' found in DataTableVisualization.getAvailableVisualizations.");
            }

            if (is_subclass_of($viz, __CLASS__)) {
                $vizId = $viz::getViewDataTableId();
                if (isset($result[$vizId])) {
                    throw new \Exception("Visualization ID '$vizId' is already in use!");
                }

                $result[$vizId] = $viz;
            }
        }
        return $result;
    }

    /**
     * Returns all available visualizations that are not part of the CoreVisualizations plugin.
     * 
     * @return array Array mapping visualization IDs with their associated visualization classes.
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
     * Returns an array mapping visualization IDs with information necessary for adding the
     * visualizations to the footer of DataTable views.
     * 
     * @param array $visualizations An array mapping visualization IDs w/ their associated classes.
     * @return array
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
     * Returns the visualization class by it's viewDataTable ID.
     * 
     * @param string $id The visualization ID.
     * @return string The visualization class name.
     * @throws \Exception if $id is not a valid visualization ID.
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