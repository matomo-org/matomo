<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\API;

use Exception;
use Piwik\API\DataTableManipulator\Flattener;
use Piwik\API\DataTableManipulator\LabelFilter;
use Piwik\API\DataTableManipulator\ReportTotalsCalculator;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Filter\PivotByDimension;
use Piwik\DataTable\Renderer;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Filter\ColumnDelete;
use Piwik\Piwik;

/**
 */
class ResponseBuilder
{
    private $outputFormat = null;
    private $apiRenderer  = null;
    private $request      = null;

    private $apiModule = false;
    private $apiMethod = false;

    /**
     * @param string $outputFormat
     * @param array $request
     */
    public function __construct($outputFormat, $request = array())
    {
        $this->outputFormat = $outputFormat;
        $this->request      = $request;
        $this->apiRenderer  = ApiRenderer::factory($outputFormat, $request);
    }

    /**
     * This method processes the data resulting from the API call.
     *
     * - If the data resulted from the API call is a DataTable then
     *         - we apply the standard filters if the parameters have been found
     *           in the URL. For example to offset,limit the Table you can add the following parameters to any API
     *        call that returns a DataTable: filter_limit=10&filter_offset=20
     *         - we apply the filters that have been previously queued on the DataTable
     * @see DataTable::queueFilter()
     *         - we apply the renderer that generate the DataTable in a given format (XML, PHP, HTML, JSON, etc.)
     *           the format can be changed using the 'format' parameter in the request.
     *        Example: format=xml
     *
     * - If there is nothing returned (void) we display a standard success message
     *
     * - If there is a PHP array returned, we try to convert it to a dataTable
     *   It is then possible to convert this datatable to any requested format (xml/etc)
     *
     * - If a bool is returned we convert to a string (true is displayed as 'true' false as 'false')
     *
     * - If an integer / float is returned, we simply return it
     *
     * @param mixed $value The initial returned value, before post process. If set to null, success response is returned.
     * @param bool|string $apiModule The API module that was called
     * @param bool|string $apiMethod The API method that was called
     * @return mixed  Usually a string, but can still be a PHP data structure if the format requested is 'original'
     */
    public function getResponse($value = null, $apiModule = false, $apiMethod = false)
    {
        $this->apiModule = $apiModule;
        $this->apiMethod = $apiMethod;

        $this->apiRenderer->sendHeader();

        // when null or void is returned from the api call, we handle it as a successful operation
        if (!isset($value)) {
            if (ob_get_contents()) {
                return null;
            }

            return $this->apiRenderer->renderSuccess('ok');
        }

        // If the returned value is an object DataTable we
        // apply the set of generic filters if asked in the URL
        // and we render the DataTable according to the format specified in the URL
        if ($value instanceof DataTableInterface) {
            return $this->handleDataTable($value);
        }

        // Case an array is returned from the API call, we convert it to the requested format
        // - if calling from inside the application (format = original)
        //    => the data stays unchanged (ie. a standard php array or whatever data structure)
        // - if any other format is requested, we have to convert this data structure (which we assume
        //   to be an array) to a DataTable in order to apply the requested DataTable_Renderer (for example XML)
        if (is_array($value)) {
            return $this->handleArray($value);
        }

        if (is_object($value)) {
            return $this->apiRenderer->renderObject($value);
        }

        if (is_resource($value)) {
            return $this->apiRenderer->renderResource($value);
        }

        return $this->apiRenderer->renderScalar($value);
    }

    /**
     * Returns an error $message in the requested $format
     *
     * @param Exception $e
     * @throws Exception
     * @return string
     */
    public function getResponseException(Exception $e)
    {
        $e       = $this->decorateExceptionWithDebugTrace($e);
        $message = $this->formatExceptionMessage($e);

        $this->apiRenderer->sendHeader();
        return $this->apiRenderer->renderException($message, $e);
    }

    /**
     * @param Exception $e
     * @return Exception
     */
    private function decorateExceptionWithDebugTrace(Exception $e)
    {
        // If we are in tests, show full backtrace
        if (defined('PIWIK_PATH_TEST_TO_ROOT')) {
            if (\Piwik_ShouldPrintBackTraceWithMessage()) {
                $message = $e->getMessage() . " in \n " . $e->getFile() . ":" . $e->getLine() . " \n " . $e->getTraceAsString();
            } else {
                $message = $e->getMessage() . "\n \n --> To temporarily debug this error further, set const PIWIK_PRINT_ERROR_BACKTRACE=true; in index.php";
            }

            return new Exception($message);
        }

        return $e;
    }

    private function formatExceptionMessage(Exception $exception)
    {
        $message = $exception->getMessage();
        if (\Piwik_ShouldPrintBackTraceWithMessage()) {
            $message .= "\n" . $exception->getTraceAsString();
        }

        return Renderer::formatValueXml($message);
    }

    protected function handleDataTable(DataTableInterface $datatable)
    {
        $label = $this->getLabelFromRequest($this->request);

        // handle pivot by dimension filter
        $pivotBy = Common::getRequestVar('pivotBy', false, 'string', $this->request);
        if (!empty($pivotBy)) {
            $reportId = $this->apiModule . '.' . $this->apiMethod;
            $pivotByColumn = Common::getRequestVar('pivotByColumn', false, 'string', $this->request);
            $pivotByColumnLimit = Common::getRequestVar('pivotByColumnLimit', false, 'int', $this->request);

            $datatable->filter('PivotByDimension', array($reportId, $pivotBy, $pivotByColumn, $pivotByColumnLimit,
                PivotByDimension::isSegmentFetchingEnabledInConfig()));
        }

        // if requested, flatten nested tables
        if (Common::getRequestVar('flat', '0', 'string', $this->request) == '1') {
            $flattener = new Flattener($this->apiModule, $this->apiMethod, $this->request);
            if (Common::getRequestVar('include_aggregate_rows', '0', 'string', $this->request) == '1') {
                $flattener->includeAggregateRows();
            }
            $datatable = $flattener->flatten($datatable);
        }

        if (1 == Common::getRequestVar('totals', '1', 'integer', $this->request)) {
            $genericFilter = new ReportTotalsCalculator($this->apiModule, $this->apiMethod, $this->request);
            $datatable     = $genericFilter->calculate($datatable);
        }

        // if the flag disable_generic_filters is defined we skip the generic filters
        if (0 == Common::getRequestVar('disable_generic_filters', '0', 'string', $this->request)) {
            $genericFilter = new DataTableGenericFilter($this->request);
            if (!empty($label)) {
                $genericFilter->disableFilters(array('Limit', 'Truncate'));
            }

            $genericFilter->filter($datatable);
        }

        // we automatically safe decode all datatable labels (against xss)
        $datatable->queueFilter('SafeDecodeLabel');

        // if the flag disable_queued_filters is defined we skip the filters that were queued
        if (Common::getRequestVar('disable_queued_filters', 0, 'int', $this->request) == 0) {
            $datatable->applyQueuedFilters();
        }

        // use the ColumnDelete filter if hideColumns/showColumns is provided (must be done
        // after queued filters are run so processed metrics can be removed, too)
        $hideColumns = Common::getRequestVar('hideColumns', '', 'string', $this->request);
        $showColumns = Common::getRequestVar('showColumns', '', 'string', $this->request);
        if ($hideColumns !== '' || $showColumns !== '') {
            $datatable->filter('ColumnDelete', array($hideColumns, $showColumns));
        }

        // apply label filter: only return rows matching the label parameter (more than one if more than one label)
        if (!empty($label)) {
            $addLabelIndex = Common::getRequestVar('labelFilterAddLabelIndex', 0, 'int', $this->request) == 1;

            $filter = new LabelFilter($this->apiModule, $this->apiMethod, $this->request);
            $datatable = $filter->filter($label, $datatable, $addLabelIndex);
        }

        return $this->apiRenderer->renderDataTable($datatable);
    }

    protected function handleArray($array)
    {
        $firstArray = null;
        $firstKey   = null;
        if (!empty($array)) {
            $firstArray = reset($array);
            $firstKey   = key($array);
        }

        $isAssoc = !empty($firstArray) && is_numeric($firstKey) && is_array($firstArray) && count(array_filter(array_keys($firstArray), 'is_string'));

        if ($isAssoc) {
            $hideColumns = Common::getRequestVar('hideColumns', '', 'string', $this->request);
            $showColumns = Common::getRequestVar('showColumns', '', 'string', $this->request);
            if ($hideColumns !== '' || $showColumns !== '') {
                $columnDelete = new ColumnDelete(new DataTable(), $hideColumns, $showColumns);
                $array = $columnDelete->filter($array);
            }
        } else if (is_numeric($firstKey)) {
            $limit  = Common::getRequestVar('filter_limit', -1, 'integer', $this->request);
            $offset = Common::getRequestVar('filter_offset', '0', 'integer', $this->request);

            if (-1 !== $limit) {
                $array = array_slice($array, $offset, $limit);
            }
        }

        return $this->apiRenderer->renderArray($array);
    }

    /**
     * Returns the value for the label query parameter which can be either a string
     * (ie, label=...) or array (ie, label[]=...).
     *
     * @param array $request
     * @return array
     */
    public static function getLabelFromRequest($request)
    {
        $label = Common::getRequestVar('label', array(), 'array', $request);
        if (empty($label)) {
            $label = Common::getRequestVar('label', '', 'string', $request);
            if (!empty($label)) {
                $label = array($label);
            }
        }

        $label = self::unsanitizeLabelParameter($label);
        return $label;
    }

    public static function unsanitizeLabelParameter($label)
    {
        // this is needed because Proxy uses Common::getRequestVar which in turn
        // uses Common::sanitizeInputValue. This causes the > that separates recursive labels
        // to become &gt; and we need to undo that here.
        $label = Common::unsanitizeInputValues($label);
        return $label;
    }
}
