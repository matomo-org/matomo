<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestRequest;

use Exception;

/**
 * Holds the specification for a set of API tests.
 *
 * An API test consists of calling Piwik's API and comparing the result with an expected
 * result. The expected result is stored in a file, usually in an **expected** folder.
 *
 * The test specification describes how the API is called and how the API response is
 * processed before comparison.
 *
 * Instances of this class are not created directly. Instead, an array mapping config
 * property names w/ values is passed to SystemTestCase::runApiTests. For example,
 *
 *     $this->runApiTests("UserCountry", array(
 *         'idSite' => 1,
 *         'date' => '2012-01-01',
 *         // ...
 *     ));
 * @since 2.8.0
 */
class ApiTestConfig
{
    /**
     * The value of the idSite query parameter to send to Piwik's API. Can be a comma separated
     * list of integers or `'all'`.
     *
     * This option is required.
     *
     * @var int|string
     */
    public $idSite;

    /**
     * The value of the date query parameter to send to Piwik's API.
     *
     * @var string
     */
    public $date = '';

    /**
     * One or more periods to test for. Multiple periods will result in multiple API calls and
     * multiple comparisons.
     *
     * @var string[]
     */
    public $periods = array('day');

    /**
     * The desired output format of the API response. Used to test DataTable renderers.
     *
     * @var string
     */
    public $format = 'xml';

    /**
     * Controls whether to query for multiple periods or not. If set to true, the last 6 dates will be
     * queried for. If set to an integer, then that number of periods will be queried for.
     *
     * @var bool|int
     */
    public $setDateLastN = false;

    /**
     * The language to retrieve results in. Defaults to 'en'.
     *
     * @var string|false
     */
    public $language = false;

    /**
     * An optional value to use for the segment query parameter.
     *
     * @var string|false
     */
    public $segment = false;

    /**
     * The value to use for the idGoal query parameter.
     *
     * @var int|bool
     */
    public $idGoal = false;

    /**
     * The value to use for the apiModule query parameter.
     *
     * @var string|false
     */
    public $apiModule = false;

    /**
     * The value to use for the apiAction query parameter.
     *
     * @var string|false
     */
    public $apiAction = false;

    /**
     * Associative array of query parameters to set in API requests. For example,
     *
     *     array('param1' => 'value1', 'param2' => 'value2')
     *
     * @var string[]
     */
    public $otherRequestParameters = array();

    /**
     * This property is used to test API methods that return subtables and should be set to the API method that
     * returns the super table of the API method being tested. If set, TestRequest\Collection will look for the
     * first valid idSubtable value to use in the test request. Since these values are assigned dynamically,
     * there's no other way to set idSubtable.
     * 
     * @var string|bool eg, `"Referrers.getWebsites"`
     */
    public $supertableApi = false;

    /**
     * If supplied, this value will be used as the expected and processed file's extension **without**
     * setting the 'format' query parameter.
     *
     * Used when testing scheduled reports.
     *
     * @var string|bool eg, `"html"`
     */
    public $fileExtension = false;

    /**
     * An array of API methods that shouldn't be called. If `'all'` is specified in SystemTestCase::runApiTests,
     * the methods in this property will be ignored when calling all API methods.
     *
     * @var string[]|false eg, `array("Actions", "Referrers.getWebsites", ...)`
     */
    public $apiNotToCall = false;

    /**
     * If true, archiving will be disabled when the API is called.
     *
     * @var bool
     */
    public $disableArchiving = false;

    /**
     * An extra suffix to apply to the expected and processed output file names.
     *
     * @param string
     */
    public $testSuffix = '';

    /**
     * If supplied, tests will compare API responses with files using a different file prefix.
     * Normally, the test name is used as the test prefix, so this will usually be set to the
     * name of the system test. Either that or the value in the test's getOutputPrefix
     * method.
     *
     * @param string|bool eg, `'OneVisitorTwoVisitsTest'`
     */
    public $compareAgainst = false;

    /**
     * An array of XML fields that should be removed from processed API response before
     * comparing. These fields should be fields that change on every test execution and have
     * to be removed in order to make tests pass.
     *
     * @param string[]|false
     */
    public $xmlFieldsToRemove = false;

    /**
     * If true, Date times XML fields that change on each request for Live API methods are retained.
     * Normally, they are removed before comparing the API response w/ expected.
     *
     * @param bool
     */
    public $keepLiveDates = false;

    /**
     * If true, ID visitors/User ID/other IDs that change on each request for Live API methods are retained.
     * Normally, they are removed before comparing the API response w/ expected.
     *
     * @param bool
     */
    public $keepLiveIds = false;

    /**
     * For format=original tests. Will forego comparison w/ expected files and just make sure unserialize works.
     *
     * @var bool
     */
    public $onlyCheckUnserialize = false;

    /**
     * Constructor. Sets class properties using an associative array mapping property names w/ values.
     *
     * @param array $params eg, `array('idSite' => 1, 'date' => '2012-01-01', ...)`
     * @throws Exception if a property name in `$params` is invalid
     */
    public function __construct($params)
    {
        foreach ($params as $key => $value) {
            if ($key == 'period') {
                $key = 'periods';
            }

            if (!property_exists($this, $key)) {
                throw new Exception("Invalid API test property '$key'! Check your System tests.");
            }

            $this->$key = $value;
        }

        if (!is_array($this->periods)) {
            $this->periods = array($this->periods);
        }

        if ($this->setDateLastN === true) {
            $this->setDateLastN = 6;
        }
    }
}
