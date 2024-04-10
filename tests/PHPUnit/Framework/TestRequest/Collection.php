<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestRequest;

use Piwik\API\DocumentationGenerator;
use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Url;
use Piwik\UrlHelper;
use Exception;

/**
 * Utility class used to generate a set of API requests given API methods to call, API
 * methods to exclude, and an ApiTestConfig instance.
 * @since 2.8.0
 */
class Collection
{
    public $defaultApiNotToCall = array(
        'LanguagesManager',
        'DBStats',
        'Dashboard',
        'UsersManager',
        'SitesManager',
        'TagManager',
        'ExampleUI',
        'Overlay',
        'Live',
        'SEO',
        'ExampleAPI',
        'ScheduledReports',
        'MobileMessaging',
        'Transitions',
        'API',
        'ImageGraph',
        'Annotations',
        'SegmentEditor',
        'UserCountry.getLocationFromIP',
        'UserCountry.getCountryCodeMapping',
        'Dashboard',
        'ExamplePluginTemplate',
        'CustomAlerts',
        'Insights',
        'LogViewer',
        'Referrers.getKeywordNotDefinedString',
        'CorePluginsAdmin.getSystemSettings',
        'API.getPagesComparisonsDisabledFor',
    );

    /**
     * The list of generated API requests.
     *
     * @var array[]
     */
    private $requestUrls;

    /**
     * The config for this set of API requests.
     *
     * @var ApiTestConfig
     */
    private $testConfig;

    /**
     * The set of API methods to test. Each API method will have at least one request URL in
     * $requestUrls.
     *
     * @var string[]|string Can be set to 'all' to test all available API methods.
     */
    private $apiToCall;

    /**
     * The set of API methods/modules that should not be called. These methods will be excluded
     * from the generated request URLs.
     *
     * @var string[]|string
     */
    private $apiNotToCall;

    /**
     * Constructor.
     */
    public function __construct($api, ApiTestConfig $testConfig, $apiToCall)
    {
        $this->testConfig = $testConfig;
        $this->setExplicitApiToCallAndNotCall($apiToCall);

        $this->requestUrls = $this->_generateApiUrls();
    }

    public function getRequestUrls()
    {
        return $this->requestUrls;
    }

    /**
     * Will return all api urls for the given data
     *
     * @return array
     */
    protected function _generateApiUrls()
    {
        $parametersToSet = array(
            'idSite'         => $this->testConfig->idSite,
            'date'           => ($this->testConfig->periods == array('range') || strpos($this->testConfig->date, ',') !== false || preg_match('/last[ -]?(week|month|year)/i', $this->testConfig->date) || preg_match('/(today|yesterday)/i', $this->testConfig->date)) ?
                                    $this->testConfig->date : date('Y-m-d', strtotime($this->testConfig->date)),
            'expanded'       => '1',
            'piwikUrl'       => 'http://example.org/piwik/',
            // Used in getKeywordsForPageUrl
            'url'            => 'http://example.org/store/purchase.htm',

            // Used in Actions.getPageUrl, .getDownload, etc.
            // tied to Main.test.php doTest_oneVisitorTwoVisits
            // will need refactoring when these same API functions are tested in a new function
            'downloadUrl'    => 'http://piwik.org/path/again/latest.zip?phpsessid=this is ignored when searching',
            'outlinkUrl'     => 'http://dev.piwik.org/svn',
            'pageUrl'        => 'http://example.org/index.htm?sessionid=this is also ignored by default',
            'pageName'       => ' Checkout / Purchasing... ',

            // do not show the millisec timer in response or tests would always fail as value is changing
            'showTimer'      => 0,

            'language'       => $this->testConfig->language ?: 'en',
            'idSites'        => $this->testConfig->idSite,
        );
        $parametersToSet = array_merge($parametersToSet, $this->testConfig->otherRequestParameters);
        if (!empty($this->testConfig->apiModule)) {
            $parametersToSet['apiModule'] = $this->testConfig->apiModule;
        }
        if (!empty($this->testConfig->apiAction)) {
            $parametersToSet['apiAction'] = $this->testConfig->apiAction;
        }
        if (!empty($this->testConfig->segment)) {
            $parametersToSet['segment'] = urlencode($this->testConfig->segment);
        }
        if ($this->testConfig->idGoal !== false) {
            $parametersToSet['idGoal'] = $this->testConfig->idGoal;
        }

        $requestUrls = $this->generateApiUrlPermutations($parametersToSet);

        $this->checkEnoughUrlsAreTested($requestUrls);

        return $requestUrls;
    }

    protected function checkEnoughUrlsAreTested($requestUrls)
    {
        $countUrls = count($requestUrls);
        $approximateCountApiToCall = count($this->apiToCall);
        if (
            empty($requestUrls)
            || $approximateCountApiToCall > $countUrls
        ) {
            $requestUrls = array_map(function ($params) {
                return is_string($params) ? $params : Url::getQueryStringFromParameters($params);
            }, $requestUrls);
            throw new Exception("Only generated $countUrls API calls to test but was expecting more for this test.\n" .
                    "Want to test APIs: " . implode(", ", $this->apiToCall) . ")\n" .
                    "But only generated these URLs: \n" . implode("\n", $requestUrls) . ")\n" .
                    "Note: SystemTestCase is meant to test API methods where the method name starts with get* \n" .
                    "If you want to test other API methods such as add* or update* or any other, please create an IntegrationTestCase instead (via `./console generate:test`)\n");
        }
    }

    /**
     * Given a list of default parameters to set, returns the URLs of APIs to call
     * If any API was specified in $this->apiNotToCall we ensure only these are tested.
     * If any API is set as excluded (see list below) then it will be ignored.
     *
     * @param array $parametersToSet Parameters to set in api call
     * @param array $formats         Array of 'format' to fetch from API
     * @param array $periods         Array of 'period' to query API
     * @param bool  $supertableApi
     * @param bool  $setDateLastN    If set to true, the 'date' parameter will be rewritten to query instead a range of dates, rather than one period only.
     * @param bool|string $language        2 letter language code, defaults to default piwik language
     * @param bool|string $fileExtension
     *
     * @throws Exception
     *
     * @return array of API URLs query strings
     */
    protected function generateApiUrlPermutations($parametersToSet)
    {
        $formats = array($this->testConfig->format);
        $originalDate = $parametersToSet['date'];

        $requestUrls = array();
        $apiMetadata = new DocumentationGenerator();

        // Get the URLs to query against the API for all functions starting with get*
        foreach ($this->getAllApiMethods() as $apiMethodInfo) {
            list($class, $moduleName, $methodName) = $apiMethodInfo;

            $apiId = $moduleName . '.' . $methodName;

            foreach ($this->testConfig->periods as $period) {
                $parametersToSet['period'] = $period;

                // If date must be a date range, we process this date range by adding 6 periods to it
                if ($this->testConfig->setDateLastN) {
                    if (!isset($parametersToSet['dateRewriteBackup'])) {
                        $parametersToSet['dateRewriteBackup'] = $parametersToSet['date'];
                    }

                    $lastCount = $this->testConfig->setDateLastN;

                    $secondDate = date('Y-m-d', strtotime("+$lastCount " . $period . "s", strtotime($originalDate)));
                    $parametersToSet['date'] = $originalDate . ',' . $secondDate;
                }

                // Set response language
                if ($this->testConfig->language !== false) {
                    $parametersToSet['language'] = $this->testConfig->language;
                }

                // set idSubtable if subtable API is set
                if ($this->testConfig->supertableApi !== false) {
                    $request = new Request(array(
                                                          'module'    => 'API',
                                                          'method'    => $this->testConfig->supertableApi,
                                                          'idSite'    => $parametersToSet['idSite'],
                                                          'period'    => $parametersToSet['period'],
                                                          'date'      => $parametersToSet['date'],
                                                          'format'    => 'json',
                                                     ));

                    $content = json_decode($request->process(), true);
                    SystemTestCase::assertApiResponseHasNoError($content);

                    // find first row w/ subtable
                    foreach ($content as $row) {
                        if (isset($row['idsubdatatable'])) {
                            $parametersToSet['idSubtable'] = $row['idsubdatatable'];
                            break;
                        }
                    }

                    // if no subtable found, throw
                    if (!isset($parametersToSet['idSubtable'])) {
                        throw new Exception(
                            "Cannot find subtable to load for $apiId in {$this->testConfig->supertableApi}."
                        );
                    }
                }

                // Generate for each specified format
                foreach ($formats as $format) {
                    $parametersToSet['format'] = $format;
                    $parametersToSet['hideIdSubDatable'] = 1;
                    if (!isset($parametersToSet['serialize'])) {
                        $parametersToSet['serialize'] = 1;
                    }

                    $exampleUrl = $apiMetadata->getExampleUrl($class, $methodName, $parametersToSet);
                    if ($exampleUrl === false) {
                        continue;
                    }

                    // Remove the first ? in the query string
                    $exampleUrl = substr($exampleUrl, 1);
                    $apiRequestId = $apiId;
                    if (strpos($exampleUrl, 'period=') !== false) {
                        $apiRequestId .= '_' . $period;
                    }

                    $apiRequestId .= '.' . $format;

                    if ($this->testConfig->fileExtension) {
                        $apiRequestId .= '.' . $this->testConfig->fileExtension;
                    }

                    $requestUrls[$apiRequestId] = UrlHelper::getArrayFromQueryString($exampleUrl);
                }
            }
        }
        return $requestUrls;
    }

    private function getAllApiMethods()
    {
        $result = array();

        foreach (Proxy::getInstance()->getMetadata() as $class => $info) {
            $moduleName = Proxy::getInstance()->getModuleNameFromClassName($class);
            foreach ($info as $methodName => $infoMethod) {
                if ($this->shouldSkipApiMethod($moduleName, $methodName)) {
                    continue;
                }

                $result[] = array($class, $moduleName, $methodName);
            }
        }

        return $result;
    }

    protected function shouldSkipApiMethod($moduleName, $methodName)
    {
        $apiId = $moduleName . '.' . $methodName;

        // If Api to test were set, we only test these
        if (
            !empty($this->apiToCall)
            && in_array($moduleName, $this->apiToCall) === false
            && in_array($apiId, $this->apiToCall) === false
        ) {
            return true;
        } elseif (
            ((strpos($methodName, 'get') !== 0 && $methodName != 'generateReport')
                || in_array($moduleName, $this->apiNotToCall) === true
                || in_array($apiId, $this->apiNotToCall) === true
            )
        ) { // Excluded modules from test
            return true;
        }

        return false;
    }

    private function setExplicitApiToCallAndNotCall($apiToCall)
    {
        if ($apiToCall == 'all') {
            $this->apiToCall = array();
            $this->apiNotToCall = $this->defaultApiNotToCall;
        } else {
            if (!is_array($apiToCall)) {
                $apiToCall = array($apiToCall);
            }

            $this->apiToCall = $apiToCall;

            if (
                !in_array('UserCountry.getLocationFromIP', $apiToCall) &&
                !in_array('UserCountry.getCountryCodeMapping', $apiToCall)
            ) {
                $this->apiNotToCall = array(
                                            'API.getMatomoVersion',
                                            'API.getPiwikVersion',
                                            'API.getPhpVersion',
                                            'API.getPagesComparisonsDisabledFor',
                                            'UserCountry.getLocationFromIP',
                                            'UserCountry.getCountryCodeMapping');
            } else {
                $this->apiNotToCall = array();
            }
        }

        if (!empty($this->testConfig->apiNotToCall)) {
            $this->apiNotToCall = array_merge($this->apiNotToCall, $this->testConfig->apiNotToCall);
        }
    }
}
