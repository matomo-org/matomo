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
use Piwik\DataTable\Renderer\Json;
use Piwik\DataTable\Renderer;
use Piwik\DataTable\Simple;
use Piwik\DataTable;

/**
 */
class ResponseBuilder
{
    private $request = null;
    private $outputFormat = null;

    private $apiModule = false;
    private $apiMethod = false;

    /**
     * @param string $outputFormat
     * @param array $request
     */
    public function __construct($outputFormat, $request = array())
    {
        $this->request = $request;
        $this->outputFormat = $outputFormat;
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

        if($this->outputFormat == 'original') {
            @header('Content-Type: text/plain; charset=utf-8');
        }
        return $this->renderValue($value);
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
        $format = strtolower($this->outputFormat);

        if ($format == 'original') {
            throw $e;
        }

        try {
            $renderer = Renderer::factory($format);
        } catch (Exception $exceptionRenderer) {
            return "Error: " . $e->getMessage() . " and: " . $exceptionRenderer->getMessage();
        }

        $e = $this->decorateExceptionWithDebugTrace($e);

        $renderer->setException($e);

        if ($format == 'php') {
            $renderer->setSerialize($this->caseRendererPHPSerialize());
        }

        return $renderer->renderException();
    }

    /**
     * @param $value
     * @return string
     */
    protected function renderValue($value)
    {
        // when null or void is returned from the api call, we handle it as a successful operation
        if (!isset($value)) {
            return $this->handleSuccess();
        }

        // If the returned value is an object DataTable we
        // apply the set of generic filters if asked in the URL
        // and we render the DataTable according to the format specified in the URL
        if ($value instanceof DataTable
            || $value instanceof DataTable\Map
        ) {
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

        // original data structure requested, we return without process
        if ($this->outputFormat == 'original') {
            return $value;
        }

        if (is_object($value)
            || is_resource($value)
        ) {
            return $this->getResponseException(new Exception('The API cannot handle this data structure.'));
        }

        // bool // integer // float // serialized object
        return $this->handleScalar($value);
    }

    /**
     * @param Exception $e
     * @return Exception
     */
    protected function decorateExceptionWithDebugTrace(Exception $e)
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

    /**
     * Returns true if the user requested to serialize the output data (&serialize=1 in the request)
     *
     * @param mixed $defaultSerializeValue Default value in case the user hasn't specified a value
     * @return bool
     */
    protected function caseRendererPHPSerialize($defaultSerializeValue = 1)
    {
        $serialize = Common::getRequestVar('serialize', $defaultSerializeValue, 'int', $this->request);
        if ($serialize) {
            return true;
        }
        return false;
    }

    /**
     * Apply the specified renderer to the DataTable
     *
     * @param DataTable|array $dataTable
     * @return string
     */
    protected function getRenderedDataTable($dataTable)
    {
        $format = strtolower($this->outputFormat);

        // if asked for original dataStructure
        if ($format == 'original') {
            // by default "original" data is not serialized
            if ($this->caseRendererPHPSerialize($defaultSerialize = 0)) {
                $dataTable = serialize($dataTable);
            }
            return $dataTable;
        }

        $method = Common::getRequestVar('method', '', 'string', $this->request);

        $renderer = Renderer::factory($format);
        $renderer->setTable($dataTable);
        $renderer->setRenderSubTables(Common::getRequestVar('expanded', false, 'int', $this->request));
        $renderer->setHideIdSubDatableFromResponse(Common::getRequestVar('hideIdSubDatable', false, 'int', $this->request));

        if ($format == 'php') {
            $renderer->setSerialize($this->caseRendererPHPSerialize());
            $renderer->setPrettyDisplay(Common::getRequestVar('prettyDisplay', false, 'int', $this->request));
        } else if ($format == 'html') {
            $renderer->setTableId($this->request['method']);
        } else if ($format == 'csv' || $format == 'tsv') {
            $renderer->setConvertToUnicode(Common::getRequestVar('convertToUnicode', true, 'int', $this->request));
        }

        // prepare translation of column names
        if ($format == 'html' || $format == 'csv' || $format == 'tsv' || $format = 'rss') {
            $renderer->setApiMethod($method);
            $renderer->setIdSite(Common::getRequestVar('idSite', false, 'int', $this->request));
            $renderer->setTranslateColumnNames(Common::getRequestVar('translateColumnNames', false, 'int', $this->request));
        }

        return $renderer->render();
    }

    /**
     * Returns a success $message in the requested $format
     *
     * @param string $message
     * @return string
     */
    protected function handleSuccess($message = 'ok')
    {
        // return a success message only if no content has already been buffered, useful when APIs return raw text or html content to the browser
        if (!ob_get_contents()) {
            switch ($this->outputFormat) {
                case 'xml':
                    @header("Content-Type: text/xml;charset=utf-8");
                    $return =
                        "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n" .
                        "<result>\n" .
                        "\t<success message=\"" . $message . "\" />\n" .
                        "</result>";
                    break;
                case 'json':
                    @header("Content-Type: application/json");
                    $return = '{"result":"success", "message":"' . $message . '"}';
                    break;
                case 'php':
                    $return = array('result' => 'success', 'message' => $message);
                    if ($this->caseRendererPHPSerialize()) {
                        $return = serialize($return);
                    }
                    break;

                case 'csv':
                    @header("Content-Type: application/vnd.ms-excel");
                    @header("Content-Disposition: attachment; filename=piwik-report-export.csv");
                    $return = "message\n" . $message;
                    break;

                default:
                    $return = 'Success:' . $message;
                    break;
            }
            return $return;
        }
    }

    /**
     * Converts the given scalar to an data table
     *
     * @param mixed $scalar
     * @return string
     */
    protected function handleScalar($scalar)
    {
        $dataTable = new Simple();
        $dataTable->addRowsFromArray(array($scalar));
        return $this->getRenderedDataTable($dataTable);
    }

    /**
     * Handles the given data table
     *
     * @param DataTable $datatable
     * @return string
     */
    protected function handleDataTable($datatable)
    {
        // process request
        $label = $this->getLabelFromRequest($this->request);

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
        return $this->getRenderedDataTable($datatable);
    }

    /**
     * Converts the given simple array to a data table
     *
     * @param array $array
     * @return string
     */
    protected function handleArray($array)
    {
        if ($this->outputFormat == 'original') {
            // we handle the serialization. Because some php array have a very special structure that
            // couldn't be converted with the automatic DataTable->addRowsFromSimpleArray
            // the user may want to request the original PHP data structure serialized by the API
            // in case he has to setup serialize=1 in the URL
            if ($this->caseRendererPHPSerialize($defaultSerialize = 0)) {
                return serialize($array);
            }
            return $array;
        }

        $multiDimensional = $this->handleMultiDimensionalArray($array);
        if ($multiDimensional !== false) {
            return $multiDimensional;
        }

        return $this->getRenderedDataTable($array);
    }

    /**
     * Is this a multi dimensional array?
     * Multi dim arrays are not supported by the Datatable renderer.
     * We manually render these.
     *
     * array(
     *         array(
     *             1,
     *             2 => array( 1,
     *                         2
     *             )
     *        ),
     *        array( 2,
     *               3
     *        )
     *    );
     *
     * @param array $array
     * @return string|bool  false if it isn't a multidim array
     */
    protected function handleMultiDimensionalArray($array)
    {
        $first = reset($array);
        foreach ($array as $first) {
            if (is_array($first)) {
                foreach ($first as $key => $value) {
                    // Yes, this is a multi dim array
                    if (is_array($value)) {
                        switch ($this->outputFormat) {
                            case 'json':
                                @header("Content-Type: application/json");
                                return self::convertMultiDimensionalArrayToJson($array);
                                break;

                            case 'php':
                                if ($this->caseRendererPHPSerialize($defaultSerialize = 0)) {
                                    return serialize($array);
                                }
                                return $array;

                            case 'xml':
                                @header("Content-Type: text/xml;charset=utf-8");
                                return $this->getRenderedDataTable($array);
                            default:
                                break;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Render a multidimensional array to Json
     * Handle DataTable|Set elements in the first dimension only, following case does not work:
     * array(
     *        array(
     *            DataTable,
     *            2 => array(
     *                1,
     *                2
     *            ),
     *        ),
     *    );
     *
     * @param array $array can contain scalar, arrays, DataTable and Set
     * @return string
     */
    public static function convertMultiDimensionalArrayToJson($array)
    {
        $jsonRenderer = new Json();
        $jsonRenderer->setTable($array);
        $renderedReport = $jsonRenderer->render();
        return $renderedReport;
    }

    /**
     * Returns the value for the label query parameter which can be either a string
     * (ie, label=...) or array (ie, label[]=...).
     *
     * @param array $request
     * @return array
     */
    static public function getLabelFromRequest($request)
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

    static public function unsanitizeLabelParameter($label)
    {
        // this is needed because Proxy uses Common::getRequestVar which in turn
        // uses Common::sanitizeInputValue. This causes the > that separates recursive labels
        // to become &gt; and we need to undo that here.
        $label = Common::unsanitizeInputValues($label);
        return $label;
    }
}
