<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\API;

use Exception;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Renderer;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Filter\ColumnDelete;
use Piwik\DataTable\Filter\Pattern;
use Piwik\Http\HttpCodeException;
use Piwik\Plugins\Monolog\Processor\ExceptionToTextProcessor;

/**
 */
class ResponseBuilder
{
    private $outputFormat = null;
    private $apiRenderer  = null;
    private $request      = null;
    private $sendHeader   = true;
    private $postProcessDataTable = true;

    private $apiModule = false;
    private $apiMethod = false;
    private $shouldPrintBacktrace = false;

    /**
     * @param string $outputFormat
     * @param array $request
     */
    public function __construct($outputFormat, $request = array(), $shouldPrintBacktrace = null)
    {
        $this->outputFormat = $outputFormat;
        $this->request      = $request;
        $this->apiRenderer  = ApiRenderer::factory($outputFormat, $request);
        $this->shouldPrintBacktrace = $shouldPrintBacktrace === null ? \Piwik_ShouldPrintBackTraceWithMessage() : $shouldPrintBacktrace;
    }

    public function disableSendHeader()
    {
        $this->sendHeader = false;
    }

    public function disableDataTablePostProcessor()
    {
        $this->postProcessDataTable = false;
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

        $this->sendHeaderIfEnabled();

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
     * @param Exception|\Throwable $e
     * @throws Exception
     * @return string
     */
    public function getResponseException($e)
    {
        $e       = $this->decorateExceptionWithDebugTrace($e);
        $message = $this->formatExceptionMessage($e);

        if ($this->sendHeader
            && $e instanceof HttpCodeException
            && $e->getCode() > 0
        ) {
            http_response_code($e->getCode());
        }

        $this->sendHeaderIfEnabled();

        return $this->apiRenderer->renderException($message, $e);
    }

    /**
     * @param Exception|\Throwable $e
     * @return Exception
     */
    private function decorateExceptionWithDebugTrace($e)
    {
        // If we are in tests, show full backtrace
        if (defined('PIWIK_PATH_TEST_TO_ROOT')) {
            if ($this->shouldPrintBacktrace) {
                $message = $e->getMessage() . " in \n " . $e->getFile() . ":" . $e->getLine() . " \n " . $e->getTraceAsString();
            } else {
                $message = $e->getMessage() . "\n \n --> To temporarily debug this error further, set const PIWIK_PRINT_ERROR_BACKTRACE=true; in index.php";
            }

            return new Exception($message);
        }

        return $e;
    }

    /**
     * @param Exception|\Throwable $exception
     * @return string
     */
    private function formatExceptionMessage($exception)
    {
        $message = ExceptionToTextProcessor::getMessageAndWholeBacktrace($exception, $this->shouldPrintBacktrace);

        if ($exception instanceof \Piwik\Exception\Exception && $exception->isHtmlMessage() && Request::isRootRequestApiRequest()) {
            $message = strip_tags(str_replace('<br />', PHP_EOL, $message));
        }

        return Renderer::formatValueXml($message);
    }

    private function handleDataTable(DataTableInterface $datatable)
    {
        if ($this->postProcessDataTable) {
            $postProcessor = new DataTablePostProcessor($this->apiModule, $this->apiMethod, $this->request);
            $datatable = $postProcessor->process($datatable);
        }

        return $this->apiRenderer->renderDataTable($datatable);
    }

    private function handleArray($array)
    {
        $firstArray = null;
        $firstKey   = null;
        if (!empty($array)) {
            $firstArray = reset($array);
            $firstKey   = key($array);
        }

        $isAssoc = !empty($firstArray) && is_numeric($firstKey) && is_array($firstArray) && count(array_filter(array_keys($firstArray), 'is_string'));

        if (is_numeric($firstKey)) {
            $columns = Common::getRequestVar('filter_column', false, 'array', $this->request);
            $pattern = Common::getRequestVar('filter_pattern', '', 'string', $this->request);

            if ($columns != array(false) && $pattern !== '') {
                $pattern = new Pattern(new DataTable(), $columns, $pattern);
                $array   = $pattern->filterArray($array);
            }

            $limit  = Common::getRequestVar('filter_limit', -1, 'integer', $this->request);
            $offset = Common::getRequestVar('filter_offset', '0', 'integer', $this->request);

            if ($limit >= 0 || $offset > 0) {
                if ($limit < 0) {
                    $limit = null; // make sure to return all results from offset
                }
                $array = array_slice($array, $offset, $limit, $preserveKeys = false);
            }
        }

        if ($isAssoc) {
            $hideColumns = Common::getRequestVar('hideColumns', '', 'string', $this->request);
            $showColumns = Common::getRequestVar('showColumns', '', 'string', $this->request);
            if ($hideColumns !== '' || $showColumns !== '') {
                $columnDelete = new ColumnDelete(new DataTable(), $hideColumns, $showColumns);
                $array = $columnDelete->filter($array);
            }
        }

        return $this->apiRenderer->renderArray($array);
    }

    private function sendHeaderIfEnabled()
    {
        if ($this->sendHeader) {
            $this->apiRenderer->sendHeader();
        }
    }
}
