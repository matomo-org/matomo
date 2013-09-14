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
     * a static $clientSideParameters field that contains a list of view property
     * names.
     * 
     * @return array
     */
    public static function getClientSideParameters()
    {
        return self::getPropertyNameListWithMetaProperty('clientSideParameters');
    }

    /**
     * Returns an array of view property names that a DataTable visualization will
     * require to be visible to client side JavaScript. Unlike 'client side parameters',
     * these will not be passed with AJAX requests as query parameters.
     * 
     * Derived DataTableVisualizations can specify client side properties by declaring
     * a static $clientSideProperties field that contains a list of view property
     * names.
     * 
     * @return array
     */
    public static function getClientSideProperties()
    {
        return self::getPropertyNameListWithMetaProperty('clientSideProperties');
    }

    /**
     * Returns an array of view property names that can be overriden by query parameters.
     * If a query parameter is sent with the same name as a view property, the view
     * property will be set to the value of the query parameter.
     * 
     * Derived DataTableVisualizations can specify overridable properties by declaring
     * a static $overridableProperties field that contains a list of view property
     * names.
     */
    public static function getOverridableProperties()
    {
        return self::getPropertyNameListWithMetaProperty('overridableProperties');
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
     * @return string The visualization class name. If $id is not a valid ID, the HtmlTable visualization
     *                is returned.
     */
    public static function getClassFromId($id)
    {
        $visualizationClasses = self::getAvailableVisualizations();
        if (!isset($visualizationClasses[$id])) {
            return $visualizationClasses['table'];
        }
        return $visualizationClasses[$id];
    }

    /**
     * Helper function that merges the static field values of every class in this
     * classes inheritance hierarchy. Uses late-static binding.
     */
    private static function getPropertyNameListWithMetaProperty($staticFieldName)
    {
        if (isset(static::$$staticFieldName)) {
            $result = array();

            $lineage = static::getVisualizationClassLineage(get_called_class());
            foreach ($lineage as $klass) {
                if (isset($klass::$$staticFieldName)) {
                    $result = array_merge($result, $klass::$$staticFieldName);
                }
            }

            return array_unique($result);
        } else {
            return array();
        }
    }
}