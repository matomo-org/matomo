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

namespace Piwik\ViewDataTable;

use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\Piwik;
use Piwik\View;
use Piwik\ViewDataTable;
use Piwik\Visualization\Config;
use Piwik\Visualization\Request;

/**
 * Base class for all DataTable visualizations. Different visualizations are used to
 * handle different values of the viewDataTable query parameter. Each one will display
 * DataTable data in a different way.
 *
 * TODO: must be more in depth
 */
abstract class Visualization extends View
{
    const GET_AVAILABLE_EVENT = 'Visualization.addVisualizations';
    const TEMPLATE_FILE = '';

    /**
     * The view data table
     * @var ViewDataTable
     */
    protected $viewDataTable;

    final public function __construct($view)
    {
        $templateFile = static::TEMPLATE_FILE;

        if (empty($templateFile)) {
            throw new \Exception('You have not defined a constant named TEMPLATE_FILE in your visualization class.');
        }

        parent::__construct($templateFile);

        $this->viewDataTable = $view;
        $this->init();
    }

    protected function init()
    {
        // do your init stuff here, do not overwrite constructor
        // maybe setting my view properties $this->vizTitle
    }

    public function configureVisualization(Config $properties)
    {
        // our stuff goes in here
        // like $properties->showFooterColumns = true;
    }

    public function beforeLoadDataTable(Request $request, Config $properties)
    {
        // change request --> $requestProperties...
        // like defining filter_column
        // $requestProperties->filterColumn = 54;
        // $requestProperties->setFilterColumn();
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable(DataTableInterface $dataTable, Config $properties, Request $request)
    {

    }

    public function afterGenericFiltersAreAppliedToLoadedDataTable(DataTableInterface $dataTable, Config $properties, Request $request)
    {

    }

    public function afterAllFilteresAreApplied(DataTableInterface $dataTable, Config $properties, Request $request)
    {
        // filter and format requested data here
        // $dataTable ...

        // $this->generator = new GeneratorFoo($dataTable);
    }

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
     * Derived Visualizations can specify client side parameters by declaring
     * a static $clientSideParameters field that contains a list of view property
     * names.
     *
     * @return array
     */
    public static function getClientSideRequestParameters()
    {
        return self::getPropertyNameListWithMetaProperty('clientSideRequestParameters');
    }

    /**
     * Returns an array of view property names that a DataTable visualization will
     * require to be visible to client side JavaScript. Unlike 'client side parameters',
     * these will not be passed with AJAX requests as query parameters.
     *
     * Derived Visualizations can specify client side properties by declaring
     * a static $clientSideProperties field that contains a list of view property
     * names.
     *
     * @return array
     */
    public static function getClientSideConfigProperties()
    {
        return self::getPropertyNameListWithMetaProperty('clientSideConfigProperties');
    }

    /**
     * Returns an array of view property names that can be overriden by query parameters.
     * If a query parameter is sent with the same name as a view property, the view
     * property will be set to the value of the query parameter.
     *
     * Derived Visualizations can specify overridable properties by declaring
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
            return get_called_class();
        }
    }

    /**
     * Returns the list of parents for a Visualization class excluding the
     * Visualization class and above.
     *
     * @param string $klass The class name of the Visualization.
     * @return Visualization[]  The list of parent classes in order from highest
     *                                   ancestor to the descended class.
     */
    public static function getVisualizationClassLineage($klass)
    {
        $klasses = array_merge(array($klass), array_values(class_parents($klass, $autoload = false)));

        $idx = array_search('Piwik\\ViewDataTable\\Visualization', $klasses);
        if ($idx !== false) {
            $klasses = array_slice($klasses, 0, $idx);
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
     * Returns all registered visualization classes. Uses the 'Visualization.getAvailable'
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

        /**
         * This event is used to gather all available DataTable visualizations. Callbacks should add visualization
         * class names to the incoming array.
         */
        Piwik::postEvent(self::GET_AVAILABLE_EVENT, array(&$visualizations));

        $result = array();
        foreach ($visualizations as $viz) {
            if (!class_exists($viz)) {
                throw new \Exception(
                    "Invalid visualization class '$viz' found in Visualization.getAvailableVisualizations.");
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