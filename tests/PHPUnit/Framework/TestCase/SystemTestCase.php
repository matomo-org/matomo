<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestCase;

use Exception;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Http;
use Piwik\ReportRenderer;
use Piwik\Tests\Framework\Constraint\ResponseCode;
use Piwik\Tests\Framework\Constraint\HttpResponseText;
use Piwik\Tests\Framework\TestRequest\ApiTestConfig;
use Piwik\Tests\Framework\TestRequest\Collection;
use Piwik\Tests\Framework\TestRequest\Response;
use Piwik\Translate;
use Piwik\Log;
use PHPUnit_Framework_TestCase;
use Piwik\Tests\Framework\Fixture;
use Piwik\Translation\Translator;
use Piwik\Url;

require_once PIWIK_INCLUDE_PATH . '/libs/PiwikTracker/PiwikTracker.php';

/**
 * Base class for System tests.
 *
 * Provides helpers to track data and then call API get* methods to check outputs automatically.
 *
 * @since 2.8.0
 */
abstract class SystemTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Identifies the last language used in an API/Controller call.
     *
     * @var string
     */
    protected $lastLanguage;

    protected $missingExpectedFiles = array();
    protected $comparisonFailures = array();

    /**
     * @var Fixture
     */
    public static $fixture;

    public static function setUpBeforeClass()
    {
        Log::debug("Setting up " . get_called_class());

        if (!isset(static::$fixture)) {
            $fixture = new Fixture();
        } else {
            $fixture = static::$fixture;
        }

        $fixture->testCaseClass = get_called_class();

        if (!array_key_exists('loadRealTranslations', $fixture->extraTestEnvVars)) {
            $fixture->extraTestEnvVars['loadRealTranslations'] = true; // load real translations by default for system tests
        }

        $fixture->extraDefinitions = static::provideContainerConfigBeforeClass();

        try {
            $fixture->performSetUp();
        } catch (Exception $e) {
            static::fail("Failed to setup fixture: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public static function tearDownAfterClass()
    {
        Log::debug("Tearing down " . get_called_class());

        if (!isset(static::$fixture)) {
            $fixture = new Fixture();
        } else {
            $fixture = static::$fixture;
        }

        $fixture->performTearDown();
    }

    /**
     * Returns true if continuous integration running this request
     * Useful to exclude tests which may fail only on this setup
     */
    public static function isTravisCI()
    {
        $travis = getenv('TRAVIS');
        return !empty($travis);
    }

    public static function isPhpVersion53()
    {
        return strpos(PHP_VERSION, '5.3') === 0;
    }

    public static function isPhp7orLater()
    {
        return version_compare('7.0.0-dev', PHP_VERSION) < 1;
    }

    public static function isMysqli()
    {
        return getenv('MYSQL_ADAPTER') == 'MYSQLI';
    }

    protected function alertWhenImagesExcludedFromTests()
    {
        if (!Fixture::canImagesBeIncludedInScheduledReports()) {
            $this->markTestSkipped(
                'Scheduled reports generated during integration tests will not contain the image graphs. ' .
                    'For tests to generate images, use a machine with the following specifications : ' .
                    'OS = '.Fixture::IMAGES_GENERATED_ONLY_FOR_OS.', PHP = '.Fixture::IMAGES_GENERATED_FOR_PHP .
                    ' and GD = ' . Fixture::IMAGES_GENERATED_FOR_GD
            );
        }
    }

    /**
     * Return 4 Api Urls for testing scheduled reports :
     * - one in HTML format with all available reports
     * - one in PDF format with all available reports
     * - two in SMS (one for each available report: MultiSites.getOne & MultiSites.getAll)
     *
     * @param string $dateTime eg '2010-01-01 12:34:56'
     * @param string $period eg 'day', 'week', 'month', 'year'
     * @return array
     */
    protected static function getApiForTestingScheduledReports($dateTime, $period)
    {
        $apiCalls = array();

        // HTML Scheduled Report
        array_push(
            $apiCalls,
            array(
                'ScheduledReports.generateReport',
                array(
                    'testSuffix'             => '_scheduled_report_in_html_tables_only',
                    'date'                   => $dateTime,
                    'periods'                => array($period),
                    'format'                 => 'original',
                    'fileExtension'          => 'html',
                    'otherRequestParameters' => array(
                        'idReport'     => 1,
                        'reportFormat' => ReportRenderer::HTML_FORMAT,
                        'outputType'   => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                    )
                )
            )
        );

        // CSV Scheduled Report
        array_push(
            $apiCalls,
            array(
                'ScheduledReports.generateReport',
                array(
                    'testSuffix'             => '_scheduled_report_in_csv',
                    'date'                   => $dateTime,
                    'periods'                => array($period),
                    'format'                 => 'original',
                    'fileExtension'          => 'csv',
                    'otherRequestParameters' => array(
                        'idReport'     => 1,
                        'reportFormat' => ReportRenderer::CSV_FORMAT,
                        'outputType'   => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                    )
                )
            )
        );

        if (Fixture::canImagesBeIncludedInScheduledReports()) {
            // PDF Scheduled Report
            // tests/PHPUnit/System/processed/test_ecommerceOrderWithItems_scheduled_report_in_pdf_tables_only__ScheduledReports.generateReport_week.original.pdf
            array_push(
                $apiCalls,
                array(
                     'ScheduledReports.generateReport',
                     array(
                         'testSuffix'             => '_scheduled_report_in_pdf_tables_only',
                         'date'                   => $dateTime,
                         'periods'                => array($period),
                         'format'                 => 'original',
                         'fileExtension'          => 'pdf',
                         'otherRequestParameters' => array(
                             'idReport'     => 1,
                             'reportFormat' => ReportRenderer::PDF_FORMAT,
                             'outputType'   => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                         )
                     )
                )
            );
        }

        // SMS Scheduled Report, one site
        array_push(
            $apiCalls,
            array(
                 'ScheduledReports.generateReport',
                 array(
                     'testSuffix'             => '_scheduled_report_via_sms_one_site',
                     'date'                   => $dateTime,
                     'periods'                => array($period),
                     'format'                 => 'original',
                     'fileExtension'          => 'sms.txt',
                     'otherRequestParameters' => array(
                         'idReport'   => 2,
                         'outputType' => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                     )
                 )
            )
        );

        // SMS Scheduled Report, all sites
        array_push(
            $apiCalls,
            array(
                 'ScheduledReports.generateReport',
                 array(
                     'testSuffix'             => '_scheduled_report_via_sms_all_sites',
                     'date'                   => $dateTime,
                     'periods'                => array($period),
                     'format'                 => 'original',
                     'fileExtension'          => 'sms.txt',
                     'otherRequestParameters' => array(
                         'idReport'   => 3,
                         'outputType' => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                     )
                 )
            )
        );

        if (Fixture::canImagesBeIncludedInScheduledReports()) {
            // HTML Scheduled Report with images
            array_push(
                $apiCalls,
                array(
                     'ScheduledReports.generateReport',
                     array(
                         'testSuffix'             => '_scheduled_report_in_html_tables_and_graph',
                         'date'                   => $dateTime,
                         'periods'                => array($period),
                         'format'                 => 'original',
                         'fileExtension'          => 'html',
                         'otherRequestParameters' => array(
                             'idReport'     => 4,
                             'reportFormat' => ReportRenderer::HTML_FORMAT,
                             'outputType'   => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                         )
                     )
                )
            );

            // mail report with one row evolution based png graph
            array_push(
                $apiCalls,
                array(
                     'ScheduledReports.generateReport',
                     array(
                         'testSuffix'             => '_scheduled_report_in_html_row_evolution_graph',
                         'date'                   => $dateTime,
                         'periods'                => array($period),
                         'format'                 => 'original',
                         'fileExtension'          => 'html',
                         'otherRequestParameters' => array(
                             'idReport'     => 5,
                             'outputType'   => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                         )
                     )
                )
            );
        }

        return $apiCalls;
    }

    /**
     * While {@link runApiTests()} lets you run test for many API methods at once this one tests only one specific
     * API method and it goes via HTTP. While the other method lets you test only some methods starting with 'get'
     * this one lets you actually test any API method.
     */
    protected function runAnyApiTest($apiMethod, $apiId, $requestParams, $options = array())
    {
        $requestParams['module'] = 'API';
        $requestParams['format'] = 'XML';
        $requestParams['method'] = $apiMethod;

        $apiId = $apiMethod . '_' . $apiId . '.xml';
        $testName = 'test_' . static::getOutputPrefix();

        list($processedFilePath, $expectedFilePath) =
            $this->getProcessedAndExpectedPaths($testName, $apiId, $format = null, $compareAgainst = false);

        if (!array_key_exists('token_auth', $requestParams)) {
            $requestParams['token_auth'] = Fixture::getTokenAuth();
        }

        $response = $this->getResponseFromHttpAPI($requestParams);
        $processedResponse = new Response($response, $options, $requestParams);

        if (empty($compareAgainst)) {
            $processedResponse->save($processedFilePath);
        }

        try {
            $expectedResponse = Response::loadFromFile($expectedFilePath, $options, $requestParams);
        } catch (Exception $ex) {
            $this->handleMissingExpectedFile($expectedFilePath, $processedResponse);
            return;
        }

        try {
            $errorMessage = get_class($this) . ": Differences with expected in '$processedFilePath'";
            Response::assertEquals($expectedResponse, $processedResponse, $errorMessage);
        } catch (Exception $ex) {
            $this->comparisonFailures[] = $ex;
        }

        $this->printApiTestFailures();
    }

    /**
     * @param $requestUrl
     * @return string
     * @throws Exception
     */
    protected function getResponseFromHttpAPI($requestUrl)
    {
        $queryString = Url::getQueryStringFromParameters($requestUrl);
        $hostAndPath = Fixture::getTestRootUrl();
        $url = $hostAndPath . '?' . $queryString;
        $response = Http::sendHttpRequest($url, $timeout = 300);
        return $response;
    }

    protected function _testApiUrl($testName, $apiId, $requestUrl, $compareAgainst, $params = array())
    {
        list($processedFilePath, $expectedFilePath) =
            $this->getProcessedAndExpectedPaths($testName, $apiId, $format = null, $compareAgainst);

        $originalGET = $_GET;
        $_GET = $requestUrl;
        unset($_GET['serialize']);

        $processedResponse = Response::loadFromApi($params, $requestUrl);
        if (empty($compareAgainst)) {
            $processedResponse->save($processedFilePath);
        }

        $_GET = $originalGET;

        try {
            $expectedResponse = Response::loadFromFile($expectedFilePath, $params, $requestUrl);
        } catch (Exception $ex) {
            $this->handleMissingExpectedFile($expectedFilePath, $processedResponse);
            return;
        }

        try {
            $errorMessage = get_class($this) . ": Differences with expected in '$processedFilePath'";
            Response::assertEquals($expectedResponse, $processedResponse, $errorMessage);
        } catch (Exception $ex) {
            $this->comparisonFailures[] = $ex;
        }
    }

    private function handleMissingExpectedFile($expectedFilePath, Response $processedResponse)
    {
        $this->missingExpectedFiles[] = $expectedFilePath;

        print("The expected file is not found at '$expectedFilePath'. The Processed response was:");
        print("\n----------------------------\n\n");
        var_dump($processedResponse->getResponseText());
        print("\n----------------------------\n");
    }

    public static function assertApiResponseHasNoError($response)
    {
        if(!is_string($response)) {
            $response = json_encode($response);
        }
        self::assertTrue(stripos($response, 'error') === false, "error in $response");
        self::assertTrue(stripos($response, 'exception') === false, "exception in $response");
    }

    protected static function getProcessedAndExpectedDirs()
    {
        $path = static::getPathToTestDirectory();
        $processedPath = $path . '/processed/';

        if (!is_dir($processedPath)) {
            mkdir($processedPath, $mode = 0777, $recursive = true);
        }

        if (!is_writable($processedPath)) {
            self::fail('To run the tests, you need to give write permissions to the following directory (create it if '
                      . 'it doesn\'t exist).<code><br/>mkdir ' . $processedPath . '<br/>chmod 777 ' . $processedPath
                      . '</code><br/>');
        }

        return array($processedPath, $path . '/expected/');
    }

    private function getProcessedAndExpectedPaths($testName, $testId, $format = null, $compareAgainst = false)
    {
        $filenameSuffix = '__' . $testId;
        if ($format) {
            $filenameSuffix .= ".$format";
        }

        $processedFilename = $testName . $filenameSuffix;

        $expectedFilename = $compareAgainst ? ('test_' . $compareAgainst) : $testName;
        $expectedFilename .= $filenameSuffix;

        list($processedDir, $expectedDir) = static::getProcessedAndExpectedDirs();

        return array($processedDir . $processedFilename, $expectedDir . $expectedFilename);
    }

    /**
     * Returns an array describing the API methods to call & compare with
     * expected output.
     *
     * The returned array must be of the following format:
     * <code>
     * array(
     *     array('SomeAPI.method', array('testOption1' => 'value1', 'testOption2' => 'value2'),
     *     array(array('SomeAPI.method', 'SomeOtherAPI.method'), array(...)),
     *     .
     *     .
     *     .
     * )
     * </code>
     *
     * Valid test options are described in the ApiTestConfig class docs.
     *
     * All test options are optional, except 'idSite' & 'date'.
     */
    public function getApiForTesting()
    {
        return array();
    }

    /**
     * Gets the string prefix used in the name of the expected/processed output files.
     */
    public static function getOutputPrefix()
    {
        $parts = explode("\\", get_called_class());
        $result = end($parts);
        $result = str_replace('Test_Piwik_Integration_', '', $result);
        return $result;
    }

    /**
     * Assert that the response of an API method call is the same as the contents in an
     * expected file.
     *
     * @param string $api ie, `"DevicesDetection.getBrowsers"`
     * @param array $queryParams Query parameters to send to the API.
     */
    public function assertApiResponseEqualsExpected($apiMethod, $queryParams)
    {
        $this->runApiTests($apiMethod, array(
            'idSite' => $queryParams['idSite'],
            'date' => $queryParams['date'],
            'periods' => $queryParams['period'],
            'format' => isset($queryParams['format']) ? $queryParams['format'] : 'xml',
            'testSuffix' => '_' . $this->getName(), // TODO: instead of using a test suffix, the whole file name should just be the test method
            'otherRequestParameters' => $queryParams
        ));
    }

    /**
     * Runs API tests.
     */
    protected function runApiTests($api, $params)
    {
        $testConfig = new ApiTestConfig($params);

        $testName = 'test_' . static::getOutputPrefix();
        $this->missingExpectedFiles = array();
        $this->comparisonFailures = array();

        if ($testConfig->disableArchiving) {
            Rules::$archivingDisabledByTests = true;
            Config::getInstance()->General['browser_archiving_disabled_enforce'] = 1;
        } else {
            Rules::$archivingDisabledByTests = false;
            Config::getInstance()->General['browser_archiving_disabled_enforce'] = 0;
        }

        if ($testConfig->language) {
            $this->changeLanguage($testConfig->language);
        }

        $testRequests = $this->getTestRequestsCollection($api, $testConfig, $api);

        foreach ($testRequests->getRequestUrls() as $apiId => $requestUrl) {
            $this->_testApiUrl($testName . $testConfig->testSuffix, $apiId, $requestUrl, $testConfig->compareAgainst, $params);
        }

        // change the language back to en
        if ($this->lastLanguage != 'en') {
            $this->changeLanguage('en');
        }

        $this->printApiTestFailures();

        return count($this->comparisonFailures) == 0;
    }

    private function printApiTestFailures()
    {
        if (!empty($this->missingExpectedFiles)) {
            $expectedDir = dirname(reset($this->missingExpectedFiles));
            $this->fail(" ERROR: Could not find expected API output '"
                . implode("', '", $this->missingExpectedFiles)
                . "'. For new tests, to pass the test, you can copy files from the processed/ directory into"
                . " $expectedDir  after checking that the output is valid. %s ");
        }

        // Display as one error all sub-failures
        if (!empty($this->comparisonFailures)) {
            $this->printComparisonFailures();
            throw reset($this->comparisonFailures);
        }
    }

    protected function getTestRequestsCollection($api, $testConfig, $apiToCall)
    {
       return new Collection($api, $testConfig, $apiToCall);
    }

    private function printComparisonFailures()
    {
        $messages = '';
        foreach ($this->comparisonFailures as $index => $failure) {
            $msg = $failure->getMessage();
            $msg = strtok($msg, "\n");
            $messages .= "\n#" . ($index + 1) . ": " . $msg;
        }
        $messages .= " \n ";

        print($messages);
    }

    /**
     * changing the language within one request is a bit fancy
     * in order to keep the core clean, we need a little hack here
     *
     * @param string $langId
     */
    protected function changeLanguage($langId)
    {
        if ($this->lastLanguage != $langId) {
            $_GET['language'] = $langId;
            /** @var Translator $translator */
            $translator = StaticContainer::get('Piwik\Translation\Translator');
            $translator->setCurrentLanguage($langId);
        }

        $this->lastLanguage = $langId;
    }

    /**
     * Path where expected/processed output files are stored.
     */
    public static function getPathToTestDirectory()
    {
        $up = DIRECTORY_SEPARATOR . '..';

        return dirname(__FILE__) . $up . $up . DIRECTORY_SEPARATOR . 'System';
    }

    /**
     * Returns an array associating table names w/ lists of row data.
     *
     * @return array
     */
    protected static function getDbTablesWithData()
    {
        $result = array();
        foreach (DbHelper::getTablesInstalled() as $tableName) {
            $result[$tableName] = Db::fetchAll("SELECT * FROM `$tableName`");
        }
        return $result;
    }

    /**
     * Truncates all tables then inserts the data in $tables into each
     * mapped table.
     *
     * @param array $tables Array mapping table names with arrays of row data.
     */
    protected static function restoreDbTables($tables)
    {
        $db = Db::fetchOne("SELECT DATABASE()");
        if (empty($db)) {
            Db::exec("USE " . Config::getInstance()->database_tests['dbname']);
        }

        DbHelper::truncateAllTables();

        // insert data
        $existingTables = DbHelper::getTablesInstalled();
        foreach ($tables as $table => $rows) {
            // create table if it's an archive table
            if (strpos($table, 'archive_') !== false && !in_array($table, $existingTables)) {
                $tableType = strpos($table, 'archive_numeric') !== false ? 'archive_numeric' : 'archive_blob';

                $createSql = DbHelper::getTableCreateSql($tableType);
                $createSql = str_replace(Common::prefixTable($tableType), $table, $createSql);
                Db::query($createSql);
            }

            if (empty($rows)) {
                continue;
            }

            $rowsSql = array();
            $bind = array();
            foreach ($rows as $row) {
                $values = array();
                foreach ($row as $value) {
                    if (is_null($value)) {
                        $values[] = 'NULL';
                    } else if (is_numeric($value)) {
                        $values[] = $value;
                    } else if (!ctype_print($value)) {
                        $values[] = "x'" . bin2hex($value) . "'";
                    } else {
                        $values[] = "?";
                        $bind[] = $value;
                    }
                }

                $rowsSql[] = "(" . implode(',', $values) . ")";
            }

            $sql = "INSERT INTO `$table` VALUES " . implode(',', $rowsSql);
            Db::query($sql, $bind);
        }
    }

    /**
     * Drops all archive tables.
     */
    public static function deleteArchiveTables()
    {
        DbHelper::deleteArchiveTables();
    }

    protected function skipWhenPhp53()
    {
        if(self::isPhpVersion53()) {
            $this->markTestSkipped('Sometimes fail on php 5.3');
        }
    }

    public function assertHttpResponseText($expectedResponseText, $url, $message = '')
    {
        self::assertThat($url, new HttpResponseText($expectedResponseText), $message);
    }

    public function assertResponseCode($expectedResponseCode, $url, $message = '')
    {
        self::assertThat($url, new ResponseCode($expectedResponseCode), $message);
    }

    public function assertNotDbConnectionCreated($message = 'A database connection was created but should not.')
    {
        self::assertFalse(Db::hasDatabaseObject(), $message);
    }

    public function assertDbConnectionCreated($message = 'A database connection was not created but should.')
    {
        self::assertTrue(Db::hasDatabaseObject(), $message);
    }

    /**
     * Use this method to return custom container configuration that you want to apply for the tests.
     * This configuration will override Fixture config.
     *
     * @return array
     */
    public static function provideContainerConfigBeforeClass()
    {
        return array();
    }
}

SystemTestCase::$fixture = new \Piwik\Tests\Framework\Fixture();
