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
namespace Piwik\API;

use Exception;
use Piwik\Access;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\PluginDeactivatedException;
use Piwik\Plugin\Manager;
use Piwik\SettingsServer;
use Piwik\Url;
use Piwik\UrlHelper;

/**
 * An API request is the object used to make a call to the API and get the result.
 * The request has the format of a normal GET request, ie. parameter_1=X&parameter_2=Y
 *
 * You can use this object from anywhere in piwik (inside plugins for example).
 * You can even call it outside of piwik  using the REST API over http
 * or in a php script on the same server as piwik, by including piwik/index.php
 * (see examples in the documentation http://piwik.org/docs/analytics-api)
 *
 * Example:
 * $request = new Request('
 *                method=UserSettings.getWideScreen
 *                &idSite=1
 *            &date=yesterday
 *                &period=week
 *                &format=xml
 *                &filter_limit=5
 *                &filter_offset=0
 *    ');
 *    $result = $request->process();
 *  echo $result;
 *
 * @see http://piwik.org/docs/analytics-api
 * @package Piwik
 * @subpackage Piwik_API
 *
 * @api
 */
class Request
{
    protected $request = null;

    /**
     * Returns the request array as string
     *
     * @param string|array $request
     * @return array|null
     */
    static public function getRequestArrayFromString($request)
    {
        $defaultRequest = $_GET + $_POST;

        $requestRaw = self::getRequestParametersGET();
        if (!empty($requestRaw['segment'])) {
            $defaultRequest['segment'] = $requestRaw['segment'];
        }

        $requestArray = $defaultRequest;

        if (!is_null($request)) {
            if (is_array($request)) {
                $url = array();
                foreach ($request as $key => $value) {
                    $url[] = $key . "=" . $value;
                }
                $request = implode("&", $url);
            }

            $request = trim($request);
            $request = str_replace(array("\n", "\t"), '', $request);

            $requestParsed = UrlHelper::getArrayFromQueryString($request);
            $requestArray = $requestParsed + $defaultRequest;
        }

        foreach ($requestArray as &$element) {
            if (!is_array($element)) {
                $element = trim($element);
            }
        }
        return $requestArray;
    }

    /**
     * Constructs the request to the API, given the request url
     *
     * @param string $request GET request that defines the API call (must at least contain a "method" parameter)
     *                          Example: method=UserSettings.getWideScreen&idSite=1&date=yesterday&period=week&format=xml
     *                          If a request is not provided, then we use the $_GET and $_POST superglobal and fetch
     *                          the values directly from the HTTP GET query.
     */
    function __construct($request = null)
    {
        $this->request = self::getRequestArrayFromString($request);
        $this->sanitizeRequest();
    }

    /**
     * Make sure that the request contains no logical errors
     */
    private function sanitizeRequest()
    {
        // The label filter does not work with expanded=1 because the data table IDs have a different meaning
        // depending on whether the table has been loaded yet. expanded=1 causes all tables to be loaded, which
        // is why the label filter can't descend when a recursive label has been requested.
        // To fix this, we remove the expanded parameter if a label parameter is set.
        if (isset($this->request['label']) && !empty($this->request['label'])
            && isset($this->request['expanded']) && $this->request['expanded']
        ) {
            unset($this->request['expanded']);
        }
    }

    /**
     * Handles the request to the API.
     * It first checks that the method called (parameter 'method') is available in the module (it means that the method exists and is public)
     * It then reads the parameters from the request string and throws an exception if there are missing parameters.
     * It then calls the API Proxy which will call the requested method.
     *
     * @throws PluginDeactivatedException
     * @return DataTable|mixed  The data resulting from the API call
     */
    public function process()
    {
        // read the format requested for the output data
        $outputFormat = strtolower(Common::getRequestVar('format', 'xml', 'string', $this->request));

        // create the response
        $response = new ResponseBuilder($outputFormat, $this->request);

        try {
            // read parameters
            $moduleMethod = Common::getRequestVar('method', null, 'string', $this->request);

            list($module, $method) = $this->extractModuleAndMethod($moduleMethod);

            if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated($module)) {
                throw new PluginDeactivatedException($module);
            }
            $apiClassName = $this->getClassNameAPI($module);

            self::reloadAuthUsingTokenAuth($this->request);

            // call the method
            $returnedValue = Proxy::getInstance()->call($apiClassName, $method, $this->request);

            $toReturn = $response->getResponse($returnedValue, $module, $method);
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }
        return $toReturn;
    }

    static public function getClassNameAPI($module)
    {
        return sprintf('\Piwik\Plugins\%s\API', $module);
    }

    /**
     * If the token_auth is found in the $request parameter,
     * the current session will be authenticated using this token_auth.
     * It will overwrite the previous Auth object.
     *
     * @param array $request If null, uses the default request ($_GET)
     * @return void
     */
    static public function reloadAuthUsingTokenAuth($request = null)
    {
        // if a token_auth is specified in the API request, we load the right permissions
        $token_auth = Common::getRequestVar('token_auth', '', 'string', $request);
        if ($token_auth) {

            /**
             * This event is triggered when authenticating the API call, only if the token_auth is found in the request.
             * In this case the current session should authenticate using this token_auth.
             */
            Piwik::postEvent('API.Request.authenticate', array($token_auth));
            Access::getInstance()->reloadAccess();
            SettingsServer::raiseMemoryLimitIfNecessary();
        }
    }

    /**
     * Returns array( $class, $method) from the given string $class.$method
     *
     * @param string $parameter
     * @throws Exception
     * @return array
     */
    private function extractModuleAndMethod($parameter)
    {
        $a = explode('.', $parameter);
        if (count($a) != 2) {
            throw new Exception("The method name is invalid. Expected 'module.methodName'");
        }
        return $a;
    }

    /**
     * Helper method to process an API request using the variables in $_GET and $_POST.
     *
     * @param string $method The API method to call, ie, Actions.getPageTitles
     * @param array $paramOverride The parameter name-value pairs to use instead of what's
     *                             in $_GET & $_POST.
     * @return mixed The result of the API request.
     */
    public static function processRequest($method, $paramOverride = array())
    {
        $params = array();
        $params['format'] = 'original';
        $params['module'] = 'API';
        $params['method'] = $method;
        $params = $paramOverride + $params;

        // process request
        $request = new Request($params);
        return $request->process();
    }

    /**
     * @return array
     */
    public static function getRequestParametersGET()
    {
        if (empty($_SERVER['QUERY_STRING'])) {
            return array();
        }
        $GET = UrlHelper::getArrayFromQueryString($_SERVER['QUERY_STRING']);
        return $GET;
    }

    /**
     * Returns the current URL without generic filter query parameters.
     *
     * @param array $params Query parameter values to override in the new URL.
     * @return string
     */
    public static function getCurrentUrlWithoutGenericFilters($params)
    {
        // unset all filter query params so the related report will show up in its default state,
        // unless the filter param was in $queryParams
        $genericFiltersInfo = DataTableGenericFilter::getGenericFiltersInformation();
        foreach ($genericFiltersInfo as $filter) {
            foreach ($filter as $queryParamName => $queryParamInfo) {
                if (!isset($params[$queryParamName])) {
                    $params[$queryParamName] = null;
                }
            }
        }

        return Url::getCurrentQueryStringWithParametersModified($params);
    }

    /**
     * @return array|bool
     */
    static public function getRawSegmentFromRequest()
    {
        // we need the URL encoded segment parameter, we fetch it from _SERVER['QUERY_STRING'] instead of default URL decoded _GET
        $segmentRaw = false;
        $segment = Common::getRequestVar('segment', '', 'string');
        if (!empty($segment)) {
            $request = Request::getRequestParametersGET();
            if (!empty($request['segment'])) {
                $segmentRaw = $request['segment'];
            }
        }
        return $segmentRaw;
    }
}
