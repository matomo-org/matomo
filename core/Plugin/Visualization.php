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

namespace Piwik\Plugin;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Log;
use Piwik\Metrics;
use Piwik\MetricsFormatter;
use Piwik\NoAccessException;
use Piwik\Option;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\API\API;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Site;
use Piwik\View;
use Piwik\ViewDataTable\VisualizationPropertiesProxy;
use Piwik\Visualization\Config as VizConfig;
use Piwik\Visualization\Request as VizRequest;

/**
 * Base class for all DataTable visualizations. Different visualizations are used to
 * handle different values of the viewDataTable query parameter. Each one will display
 * DataTable data in a different way.
 *
 * TODO: must be more in depth
 */
class Visualization extends ViewDataTable
{
    const TEMPLATE_FILE = '';
    const CONFIGURE_VIEW_EVENT = 'Visualization.initView';

    final public function __construct($currentControllerAction, $apiMethodToRequestDataTable, $defaultReportProperties)
    {
        $templateFile = static::TEMPLATE_FILE;

        if (empty($templateFile)) {
            throw new \Exception('You have not defined a constant named TEMPLATE_FILE in your visualization class.');
        }

        parent::__construct($currentControllerAction, $apiMethodToRequestDataTable, $defaultReportProperties);

        $this->init();
    }

    protected function init()
    {
        // do your init stuff here, do not overwrite constructor
        // maybe setting my view properties $this->vizTitle
    }

    protected function buildView()
    {
        $this->configureVisualization();

        /**
         * This event is called before a visualization is created. Plugins can use this event to
         * override view properties for individual reports or visualizations.
         *
         * Themes can use this event to make sure reports look nice with their themes. Plugins
         * that provide new visualizations can use this event to make sure certain reports
         * are configured differently when viewed with the new visualization.
         */
        Piwik::postEvent(self::CONFIGURE_VIEW_EVENT, array($viewDataTable = $this));
        $this->overrideViewProperties();

        try {
            $this->beforeLoadDataTable();

            $this->loadDataTableFromAPI();
            $this->postDataTableLoadedFromAPI();

            $this->afterAllFilteresAreApplied();

        } catch (NoAccessException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::warning("Failed to get data from API: " . $e->getMessage());

            $loadingError = array('message' => $e->getMessage());
        }

        $view = new View("@CoreHome/_dataTable");

        if (!empty($loadingError)) {
            $view->error = $loadingError;
        }

        $vizView = new View(static::TEMPLATE_FILE);
        // TODO there used to be the Visualization class
        $vizView->assign(array_merge($this->requestConfig->getProperties(), $this->config->getProperties()));
        $view->visualization = $vizView;

        $view->visualizationCssClass = $this->getDefaultDataTableCssClass();

        if (null === $this->dataTable) {
            $view->dataTable = null;
        } else {
            // TODO: this hook seems inappropriate. should be able to find data that is requested for (by site/date) and check if that
            //       has data.
            if (method_exists($this, 'isThereDataToDisplay')) {
                $view->dataTableHasNoData = !$this->isThereDataToDisplay($this->dataTable, $this);
            }

            $view->dataTable = $this->dataTable;

            // if it's likely that the report data for this data table has been purged,
            // set whether we should display a message to that effect.
            $view->showReportDataWasPurgedMessage = $this->hasReportBeenPurged();
            $view->deleteReportsOlderThan = Option::get('delete_reports_older_than');
        }
        $view->idSubtable = $this->idSubtable;
        $view->clientSideParameters = $this->getClientSideParametersToSet();
        $view->clientSideProperties = $this->getClientSidePropertiesToSet();
        $view->properties = array_merge($this->requestConfig->getProperties(), $this->config->getProperties());
        $view->footerIcons = $this->config->footer_icons;
        $view->isWidget = Common::getRequestVar('widget', 0, 'int');

        return $view;
    }

    /**
     * Hook called after the dataTable has been loaded from the API
     * Can be used to add, delete or modify the data freshly loaded
     *
     * @return bool
     */
    private function postDataTableLoadedFromAPI()
    {
        $columns = $this->dataTable->getColumns();
        $haveNbVisits = in_array('nb_visits', $columns);
        $haveNbUniqVisitors = in_array('nb_uniq_visitors', $columns);

        // default columns_to_display to label, nb_uniq_visitors/nb_visits if those columns exist in the
        // dataset. otherwise, default to all columns in dataset.
        if (empty($this->config->columns_to_display)) {
            if ($haveNbVisits
                || $haveNbUniqVisitors
            ) {
                $columnsToDisplay = array('label');

                // if unique visitors data is available, show it, otherwise just visits
                if ($haveNbUniqVisitors) {
                    $columnsToDisplay[] = 'nb_uniq_visitors';
                } else {
                    $columnsToDisplay[] = 'nb_visits';
                }
            } else {
                $columnsToDisplay = $columns;
            }

            $this->config->columns_to_display = array_filter($columnsToDisplay);
        }

        $this->removeEmptyColumnsFromDisplay();

        // default sort order to visits/visitors data
        if (empty($this->requestConfig->filter_sort_column)) {
            if ($haveNbUniqVisitors
                && in_array('nb_uniq_visitors', $this->config->columns_to_display)
            ) {
                $this->requestConfig->filter_sort_column = 'nb_uniq_visitors';
            } else {
                $this->requestConfig->filter_sort_column = 'nb_visits';
            }
            $this->requestConfig->filter_sort_order = 'desc';
        }

        // deal w/ table metadata
        if ($this->dataTable instanceof DataTable) {
            $this->config->metadata = $this->dataTable->getAllTableMetadata();

            if (isset($this->config->metadata[DataTable::ARCHIVED_DATE_METADATA_NAME])) {
                $this->config->metadata[DataTable::ARCHIVED_DATE_METADATA_NAME] =
                    $this->makePrettyArchivedOnText();
            }
        }

        list($priorityFilters, $otherFilters) = $this->getFiltersToRun();

        // First, filters that delete rows
        foreach ($priorityFilters as $filter) {
            $this->dataTable->filter($filter[0], $filter[1]);
        }

        $this->beforeGenericFiltersAreAppliedToLoadedDataTable();

        if (!$this->areGenericFiltersDisabled()) {
            // Second, generic filters (Sort, Limit, Replace Column Names, etc.)
            $requestArray = $this->request->getRequestArray();
            $request = \Piwik\API\Request::getRequestArrayFromString($requestArray);

            if ($this->config->enable_sort === false) {
                $request['filter_sort_column'] = $request['filter_sort_order'] = '';
            }

            $genericFilter = new \Piwik\API\DataTableGenericFilter($request);
            $genericFilter->filter($this->dataTable);
        }

        // queue other filters so they can be applied later if queued filters are disabled
        foreach ($otherFilters as $filter) {
            $this->dataTable->queueFilter($filter[0], $filter[1]);
        }

        // Finally, apply datatable filters that were queued (should be 'presentation' filters that
        // do not affect the number of rows)
        if (!$this->areQueuedFiltersDisabled()) {

            $this->dataTable->applyQueuedFilters();
        }

        $this->afterGenericFiltersAreAppliedToLoadedDataTable();

        return true;
    }

    private function removeEmptyColumnsFromDisplay()
    {
        if (empty($this->dataTable)) {
            return;
        }
        if ($this->dataTable instanceof DataTable\Map) {
            $emptyColumns = $this->dataTable->getMetadataIntersectArray(DataTable::EMPTY_COLUMNS_METADATA_NAME);
        } else {
            $emptyColumns = $this->dataTable->getMetadata(DataTable::EMPTY_COLUMNS_METADATA_NAME);
        }
        if (is_array($emptyColumns)) {
            foreach ($emptyColumns as $emptyColumn) {
                $key = array_search($emptyColumn, $this->config->columns_to_display);
                if ($key !== false) {
                    unset($this->config->columns_to_display[$key]);
                }
            }
            $this->config->columns_to_display = array_values($this->config->columns_to_display);
        }
    }

    /**
     * Returns true if generic filters have been disabled, false if otherwise.
     *
     * @return bool
     */
    private function areGenericFiltersDisabled()
    {
        // if disable_generic_filters query param is set to '1', generic filters are disabled
        if (Common::getRequestVar('disable_generic_filters', '0', 'string') == 1) {
            return true;
        }

        if (isset($this->config->disable_generic_filters) && true === $this->config->disable_generic_filters
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if queued filters have been disabled, false if otherwise.
     *
     * @return bool
     */
    private function areQueuedFiltersDisabled()
    {
        return isset($this->config->disable_queued_filters) && $this->config->disable_queued_filters;
    }

    /**
     * Returns prettified and translated text that describes when a report was last updated.
     *
     * @return string
     */
    private function makePrettyArchivedOnText()
    {
        $dateText = $this->config->metadata[DataTable::ARCHIVED_DATE_METADATA_NAME];
        $date = Date::factory($dateText);
        $today = mktime(0, 0, 0);
        if ($date->getTimestamp() > $today) {
            $elapsedSeconds = time() - $date->getTimestamp();
            $timeAgo = MetricsFormatter::getPrettyTimeFromSeconds($elapsedSeconds);

            return Piwik::translate('CoreHome_ReportGeneratedXAgo', $timeAgo);
        }

        $prettyDate = $date->getLocalized("%longYear%, %longMonth% %day%") . $date->toString('S');
        return Piwik::translate('CoreHome_ReportGeneratedOn', $prettyDate);
    }

    /**
     * Returns true if it is likely that the data for this report has been purged and if the
     * user should be told about that.
     *
     * In order for this function to return true, the following must also be true:
     * - The data table for this report must either be empty or not have been fetched.
     * - The period of this report is not a multiple period.
     * - The date of this report must be older than the delete_reports_older_than config option.
     * @return bool
     */
    public function hasReportBeenPurged()
    {
        $strPeriod = Common::getRequestVar('period', false);
        $strDate = Common::getRequestVar('date', false);

        if ($strPeriod !== false
            && $strDate !== false
            && (is_null($this->dataTable)
                || (!empty($this->dataTable) && $this->dataTable->getRowsCount() == 0))
        ) {
            // if range, only look at the first date
            if ($strPeriod == 'range') {
                $idSite = Common::getRequestVar('idSite', '');
                if (intval($idSite) != 0) {
                    $site = new Site($idSite);
                    $timezone = $site->getTimezone();
                } else {
                    $timezone = 'UTC';
                }

                $period = new Range('range', $strDate, $timezone);
                $reportDate = $period->getDateStart();
            } // if a multiple period, this function is irrelevant
            else if (Period::isMultiplePeriod($strDate, $strPeriod)) {
                return false;
            } // otherwise, use the date as given
            else {
                $reportDate = Date::factory($strDate);
            }

            $reportYear = $reportDate->toString('Y');
            $reportMonth = $reportDate->toString('m');

            if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated('PrivacyManager')
                && PrivacyManager::shouldReportBePurged($reportYear, $reportMonth)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns array of properties that should be visible to client side JavaScript. The data
     * will be available in the data-props HTML attribute of the .dataTable div.
     *
     * @return array Maps property names w/ property values.
     */
    private function getClientSidePropertiesToSet()
    {
        $result = array();

        foreach ($this->getClientSideConfigProperties() as $name) {
            if (property_exists($this->requestConfig, $name)) {
                $result[$name] = $this->convertForJson($this->requestConfig->$name);
            } else if (property_exists($this->config, $name)) {
                $result[$name] = $this->convertForJson($this->config->$name);
            } else if (property_exists($this, $name)) {
                $result[$name] = $this->convertForJson($this->$name);
            }
        }

        return $result;
    }

    /**
     * Returns the list of view properties that should be sent with the HTML response
     * as JSON. These properties are visible to the UI JavaScript, but are not passed
     * with every request.
     *
     * @return array
     */
    public function getClientSideConfigProperties()
    {
        return $this->getPropertyNameListWithMetaProperty(VizConfig::$clientSideProperties, __FUNCTION__);
    }

    /**
     * Returns the list of view properties that should be sent with the HTML response
     * and resent by the UI JavaScript in every subsequent AJAX request.
     *
     * @return array
     */
    public function getClientSideRequestParameters()
    {
        return $this->getPropertyNameListWithMetaProperty(VizRequest::$clientSideParameters, __FUNCTION__);
    }

    /**
     * This functions reads the customization values for the DataTable and returns an array (name,value) to be printed in Javascript.
     * This array defines things such as:
     * - name of the module & action to call to request data for this table
     * - optional filters information, eg. filter_limit and filter_offset
     * - etc.
     *
     * The values are loaded:
     * - from the generic filters that are applied by default @see Piwik_API_DataTableGenericFilter.php::getGenericFiltersInformation()
     * - from the values already available in the GET array
     * - from the values set using methods from this class (eg. setSearchPattern(), setLimit(), etc.)
     *
     * @return array eg. array('show_offset_information' => 0, 'show_...
     */
    protected function getClientSideParametersToSet()
    {
        // build javascript variables to set
        $javascriptVariablesToSet = array();

        foreach ($this->config->custom_parameters as $name => $value) {
            $javascriptVariablesToSet[$name] = $value;
        }

        foreach ($_GET as $name => $value) {
            try {
                $requestValue = Common::getRequestVar($name);
            } catch (\Exception $e) {
                $requestValue = '';
            }
            $javascriptVariablesToSet[$name] = $requestValue;
        }

        foreach ($this->getClientSideRequestParameters() as $name) {
            if (isset($javascriptVariablesToSet[$name])) {
                continue;
            }

            if (property_exists($this->requestConfig, $name)) {
                $javascriptVariablesToSet[$name] = $this->convertForJson($this->requestConfig->$name);
            } else if (property_exists($this->config, $name)) {
                $javascriptVariablesToSet[$name] = $this->convertForJson($this->config->$name);
            } else if (property_exists($this, $name)) {
                $javascriptVariablesToSet[$name] = $this->convertForJson($this->$name);
            }
        }

        if ($this->dataTable instanceof DataTable) {
            // we override the filter_sort_column with the column used for sorting,
            // which can be different from the one specified (eg. if the column doesn't exist)
            $javascriptVariablesToSet['filter_sort_column'] = $this->dataTable->getSortedByColumnName();
            // datatable can return "2" but we want to write "nb_visits" in the js
            if (isset(Metrics::$mappingFromIdToName[$javascriptVariablesToSet['filter_sort_column']])) {
                $javascriptVariablesToSet['filter_sort_column'] = Metrics::$mappingFromIdToName[$javascriptVariablesToSet['filter_sort_column']];
            }
        }

        $javascriptVariablesToSet['module'] = $this->currentControllerName;
        $javascriptVariablesToSet['action'] = $this->currentControllerAction;
        if (!isset($javascriptVariablesToSet['viewDataTable'])) {
            $javascriptVariablesToSet['viewDataTable'] = $this->getViewDataTableId();
        }

        if ($this->dataTable &&
            // Set doesn't have the method
            !($this->dataTable instanceof DataTable\Map)
            && empty($javascriptVariablesToSet['totalRows'])
        ) {
            $javascriptVariablesToSet['totalRows'] = $this->dataTable->getRowsCountBeforeLimitFilter();
        }

        $deleteFromJavascriptVariables = array(
            'filter_excludelowpop',
            'filter_excludelowpop_value',
        );
        foreach ($deleteFromJavascriptVariables as $name) {
            if (isset($javascriptVariablesToSet[$name])) {
                unset($javascriptVariablesToSet[$name]);
            }
        }

        $rawSegment = \Piwik\API\Request::getRawSegmentFromRequest();
        if (!empty($rawSegment)) {
            $javascriptVariablesToSet['segment'] = $rawSegment;
        }

        return $javascriptVariablesToSet;
    }

    public function configureVisualization()
    {
        // our stuff goes in here
        // like $properties->showFooterColumns = true;
    }

    public function beforeLoadDataTable()
    {
        // change request --> $requestProperties...
        // like defining filter_column
        // $requestProperties->filterColumn = 54;
        // $requestProperties->setFilterColumn();
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {

    }

    public function afterGenericFiltersAreAppliedToLoadedDataTable()
    {

    }

    public function afterAllFilteresAreApplied()
    {
        // filter and format requested data here
        // $dataTable ...

        // $this->generator = new GeneratorFoo($dataTable);
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
    protected function getPropertyNameListWithMetaProperty($staticFieldName)
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
        }

        return array();
    }

    private function getFiltersToRun()
    {
        $priorityFilters = array();
        $presentationFilters = array();

        foreach ($this->config->filters as $filterInfo) {
            if ($filterInfo instanceof \Closure) {
                $nameOrClosure = $filterInfo;
                $parameters = array();
                $priority = false;
            } else {
                @list($nameOrClosure, $parameters, $priority) = $filterInfo;
            }

            if ($nameOrClosure instanceof \Closure) {
                $parameters[] = $this;
            }

            if ($priority) {
                $priorityFilters[] = array($nameOrClosure, $parameters);
            } else {
                $presentationFilters[] = array($nameOrClosure, $parameters);
            }
        }

        return array($priorityFilters, $presentationFilters);
    }

}