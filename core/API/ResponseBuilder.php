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
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Renderer;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Filter\ColumnDelete;
use Piwik\DataTable\Filter\Pattern;

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
     * @param Exception $e
     * @throws Exception
     * @return string
     */
    public function getResponseException(Exception $e)
    {
        $e       = $this->decorateExceptionWithDebugTrace($e);
        $message = $this->formatExceptionMessage($e);

        $this->sendHeaderIfEnabled();

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

            if ($this->shouldApplyLimitOnArray($limit, $offset)) {
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

    private function shouldApplyLimitOnArray($limit, $offset)
    {
        if ($limit === -1) {
            // all fields are requested
            return false;
        }

        if ($offset > 0) {
            // an offset is specified, we have to apply the limit
            return true;
        }

        // "api_datatable_default_limit" is set by API\Controller if no filter_limit is specified by the user.
        // it holds the number of the configured default limit.
        $limitSetBySystem = Common::getRequestVar('api_datatable_default_limit', -2, 'integer', $this->request);

        // we ignore the limit if the datatable_default_limit was set by the system as this default filter_limit is
        // only meant for dataTables but not for arrays. This way we stay BC as filter_limit was not applied pre
        // Piwik 2.6 and some fixes were made in Piwik 2.13.
        $wasFilterLimitSetBySystem = $limitSetBySystem !== -2;

        // we check for "$limitSetBySystem === $limit" as an API method could request another API method with
        // another limit. In this case we need to apply it again.
        $isLimitStillDefaultLimit = $limitSetBySystem === $limit;

        if ($wasFilterLimitSetBySystem && $isLimitStillDefaultLimit) {
            return false;
        }

        return true;
    }

    private function sendHeaderIfEnabled()
    {
        if ($this->sendHeader) {
            $this->apiRenderer->sendHeader();
        }
    }
}
