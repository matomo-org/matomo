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
use Piwik\ViewDataTable\Manager as ViewDataTableManager;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Site;
use Piwik\View;

/**
 * Base class for all DataTable visualizations. A Visualization is a special kind of ViewDataTable that comes with some
 * handy hooks. Different visualizations are used to handle different values of the viewDataTable query parameter.
 * Each one will display DataTable data in a different way.
 *
 * TODO: must be more in depth
 * @api
 */
class Visualization extends ViewDataTable
{
    const TEMPLATE_FILE = '';

    private $templateVars = array();

    final public function __construct($controllerAction, $apiMethodToRequestDataTable)
    {
        $templateFile = static::TEMPLATE_FILE;

        if (empty($templateFile)) {
            throw new \Exception('You have not defined a constant named TEMPLATE_FILE in your visualization class.');
        }

        parent::__construct($controllerAction, $apiMethodToRequestDataTable);
    }

    protected function buildView()
    {
        $this->overrideSomeConfigPropertiesIfNeeded();

        try {

            $this->beforeLoadDataTable();

            $this->loadDataTableFromAPI(array('disable_generic_filters' => 1, 'disable_queued_filters' => 1));
            $this->postDataTableLoadedFromAPI();

            $requestPropertiesAfterLoadDataTable = $this->requestConfig->getProperties();

            $this->applyFilters();
            $this->afterAllFilteresAreApplied();
            $this->beforeRender();

            $this->logMessageIfRequestPropertiesHaveChanged($requestPropertiesAfterLoadDataTable);

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

        $view->assign($this->templateVars);
        $view->visualization         = $this;
        $view->visualizationTemplate = static::TEMPLATE_FILE;
        $view->visualizationCssClass = $this->getDefaultDataTableCssClass();

        if (null === $this->dataTable) {
            $view->dataTable = null;
        } else {
            $view->dataTableHasNoData = !$this->isThereDataToDisplay();
            $view->dataTable          = $this->dataTable;

            // if it's likely that the report data for this data table has been purged,
            // set whether we should display a message to that effect.
            $view->showReportDataWasPurgedMessage = $this->hasReportBeenPurged();
            $view->deleteReportsOlderThan         = Option::get('delete_reports_older_than');
        }

        $view->idSubtable  = $this->requestConfig->idSubtable;
        $view->clientSideParameters = $this->getClientSideParametersToSet();
        $view->clientSideProperties = $this->getClientSidePropertiesToSet();
        $view->properties  = array_merge($this->requestConfig->getProperties(), $this->config->getProperties());
        $view->footerIcons = $this->config->footer_icons;
        $view->isWidget    = Common::getRequestVar('widget', 0, 'int');

        return $view;
    }

    private function overrideSomeConfigPropertiesIfNeeded()
    {
        if (empty($this->config->footer_icons)) {
            $this->config->footer_icons = ViewDataTableManager::configureFooterIcons($this);
        }

        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated('Goals')) {
            $this->config->show_goals = false;
        }
    }

    /**
     * Assigns a template variable. All assigned variables are available in the twig view template afterwards. You can
     * assign either one variable by setting $vars and $value or an array of key/value pairs.
     *
     * @param array|string $vars
     * @param mixed  $value
     */
    public function assignTemplateVar($vars, $value = null)
    {
        if (is_string($vars)) {
            $this->templateVars[$vars] = $value;
        } elseif (is_array($vars)) {
            foreach ($vars as $key => $value) {
                $this->templateVars[$key] = $value;
            }
        }
    }

    protected function isThereDataToDisplay()
    {
        return true;
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
        $hasNbVisits       = in_array('nb_visits', $columns);
        $hasNbUniqVisitors = in_array('nb_uniq_visitors', $columns);

        // default columns_to_display to label, nb_uniq_visitors/nb_visits if those columns exist in the
        // dataset. otherwise, default to all columns in dataset.
        if (empty($this->config->columns_to_display)) {
            $this->config->setDefaultColumnsToDisplay($columns, $hasNbVisits, $hasNbUniqVisitors);
        }

        if (!empty($this->dataTable)) {
            $this->removeEmptyColumnsFromDisplay();
        }

        if (empty($this->requestConfig->filter_sort_column)) {
            $this->requestConfig->setDefaultSort($this->config->columns_to_display, $hasNbUniqVisitors);
        }

        // deal w/ table metadata
        if ($this->dataTable instanceof DataTable) {
            $this->config->metadata = $this->dataTable->getAllTableMetadata();

            if (isset($this->config->metadata[DataTable::ARCHIVED_DATE_METADATA_NAME])) {
                $this->config->report_last_updated_message = $this->makePrettyArchivedOnText();
            }
        }
    }

    private function applyFilters()
    {
        list($priorityFilters, $otherFilters) = $this->config->getFiltersToRun();

        // First, filters that delete rows
        foreach ($priorityFilters as $filter) {
            $this->dataTable->filter($filter[0], $filter[1]);
        }

        $this->beforeGenericFiltersAreAppliedToLoadedDataTable();

        if (!$this->requestConfig->areGenericFiltersDisabled()) {
            $this->applyGenericFilters();
        }

        $this->afterGenericFiltersAreAppliedToLoadedDataTable();

        // queue other filters so they can be applied later if queued filters are disabled
        foreach ($otherFilters as $filter) {
            $this->dataTable->queueFilter($filter[0], $filter[1]);
        }

        // Finally, apply datatable filters that were queued (should be 'presentation' filters that
        // do not affect the number of rows)
        if (!$this->requestConfig->areQueuedFiltersDisabled()) {
            $this->dataTable->applyQueuedFilters();
        }
    }

    private function removeEmptyColumnsFromDisplay()
    {
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
     * Returns prettified and translated text that describes when a report was last updated.
     *
     * @return string
     */
    private function makePrettyArchivedOnText()
    {
        $dateText = $this->config->metadata[DataTable::ARCHIVED_DATE_METADATA_NAME];
        $date     = Date::factory($dateText);
        $today    = mktime(0, 0, 0);

        if ($date->getTimestamp() > $today) {

            $elapsedSeconds = time() - $date->getTimestamp();
            $timeAgo        = MetricsFormatter::getPrettyTimeFromSeconds($elapsedSeconds);

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
    private function hasReportBeenPurged()
    {
        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated('PrivacyManager')) {
            return false;
        }

        return PrivacyManager::hasReportBeenPurged($this->dataTable);
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

        foreach ($this->config->clientSideProperties as $name) {
            if (property_exists($this->requestConfig, $name)) {
                $result[$name] = $this->getIntIfValueIsBool($this->requestConfig->$name);
            } else if (property_exists($this->config, $name)) {
                $result[$name] = $this->getIntIfValueIsBool($this->config->$name);
            }
        }

        return $result;
    }

    private function getIntIfValueIsBool($value)
    {
        return is_bool($value) ? (int)$value : $value;
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

        foreach ($this->requestConfig->clientSideParameters as $name) {
            if (isset($javascriptVariablesToSet[$name])) {
                continue;
            }

            $valueToConvert = false;

            if (property_exists($this->requestConfig, $name)) {
                $valueToConvert = $this->requestConfig->$name;
            } else if (property_exists($this->config, $name)) {
                $valueToConvert = $this->config->$name;
            }

            if (false !== $valueToConvert) {
                $javascriptVariablesToSet[$name] = $this->getIntIfValueIsBool($valueToConvert);
            }
        }

        $javascriptVariablesToSet['module'] = $this->config->controllerName;
        $javascriptVariablesToSet['action'] = $this->config->controllerAction;
        if (!isset($javascriptVariablesToSet['viewDataTable'])) {
            $javascriptVariablesToSet['viewDataTable'] = static::getViewDataTableId();
        }

        if ($this->dataTable &&
            // Set doesn't have the method
            !($this->dataTable instanceof DataTable\Map)
            && empty($javascriptVariablesToSet['totalRows'])
        ) {
            $javascriptVariablesToSet['totalRows'] =
                $this->dataTable->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME) ?: $this->dataTable->getRowsCount();
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

    /**
     * Hook that is intended to change the request config that is sent to the API.
     */
    public function beforeLoadDataTable()
    {
    }

    /**
     * Hook that is executed before generic filters like "filter_limit" and "filter_offset" are applied
     */
    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {

    }

    /**
     * This hook is executed after generic filters like "filter_limit" and "filter_offset" are applied
     */
    public function afterGenericFiltersAreAppliedToLoadedDataTable()
    {

    }

    /**
     * This hook is executed after the data table is loaded and after all filteres are applied.
     * Format the data that you want to pass to the view here.
     */
    public function afterAllFilteresAreApplied()
    {
    }

    /**
     * Hook to make sure config properties have a specific value because the default config can be changed by a
     * report or by request ($_GET and $_POST) params.
     */
    public function beforeRender()
    {
        // eg $this->config->showFooterColumns = true;
    }

    /**
     * Second, generic filters (Sort, Limit, Replace Column Names, etc.)
     */
    private function applyGenericFilters()
    {
        $requestArray = $this->request->getRequestArray();
        $request      = \Piwik\API\Request::getRequestArrayFromString($requestArray);

        if (false === $this->config->enable_sort) {
            $request['filter_sort_column'] = '';
            $request['filter_sort_order']  = '';
        }

        $genericFilter = new \Piwik\API\DataTableGenericFilter($request);
        $genericFilter->filter($this->dataTable);
    }

    private function logMessageIfRequestPropertiesHaveChanged(array $requestPropertiesBefore)
    {
        $requestProperties = $this->requestConfig->getProperties();

        $diff = array_diff_assoc($this->makeSureArrayContainsOnlyStrings($requestProperties),
                                 $this->makeSureArrayContainsOnlyStrings($requestPropertiesBefore));

        if (empty($diff)) {
            return;
        }

        $details = array(
            'changedProperties' => $diff,
            'apiMethod'         => $this->requestConfig->apiMethodToRequestDataTable,
            'controller'        => $this->config->controllerName . '.' . $this->config->controllerAction,
            'viewDataTable'     => static::getViewDataTableId()
        );

        $message = 'Some ViewDataTable::requestConfig properties have changed after requesting the data table. '
                 . 'That means the changed values had probably no effect. For instance in beforeRender() hook. '
                 . 'Probably a bug? Details:'
                 . print_r($details, 1);

        Log::warning($message);
    }

    private function makeSureArrayContainsOnlyStrings($array)
    {
        $result = array();

        foreach ($array as $key => $value) {
            $result[$key] = json_encode($value);
        }

        return $result;
    }
}