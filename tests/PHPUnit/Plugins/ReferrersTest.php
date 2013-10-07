<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
require_once 'Referrers/Referrers.php';

use Piwik\Date;
use Piwik\Period;
use Piwik\Segment;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\ArchiveProcessor\Rules;

class ReferrersTest extends IntegrationTestCase
{
    public static $oldReferrerRecordNames = array(
        'Referrers_keywordBySearchEngine',
        'Referrers_searchEngineByKeyword',
        'Referrers_keywordByCampaign',
        'Referrers_urlByWebsite',
        'Referrers_type',
    );

    public static $oldReferrerMetricNames = array(
        'Referrers_distinctSearchEngines',
        'Referrers_distinctKeywords',
        'Referrers_distinctCampaigns',
        'Referrers_distinctWebsites',
        'Referrers_distinctWebsitesUrls',
    );

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        Test_Piwik_BaseFixture::createWebsite('2010-01-01', $ecommerce = 0, $name = 'Site #1');
        Test_Piwik_BaseFixture::createWebsite('2010-01-01', $ecommerce = 0, $name = 'Site #2');

        $testDataTable = new DataTable();
        $testDataTable->addRow(new Row(array(
            Row::COLUMNS => array(
                'testcol1' => 'testdata1',
                'testcol2' => 'testdata2'
            )
        )));
        $testDataTable->addRow(new Row(array(
            Row::COLUMNS => array(
                'testcol3' => 'testdata3',
            )
        )));
        $testDataTableBlob = $testDataTable->getSerialized();
        $testDataTableBlob = @gzcompress($testDataTableBlob[0]);

        foreach (array(1, 2) as $idSite) {
            foreach (array('2010-05-06', '2010-05-07') as $date) {
                $period = Period::factory('day', Date::factory($date));
                $archiveWriter = new ArchiveWriter($idSite, new Segment('', array($idSite)), $period, 'Referrers', $temp = false);

                $archiveWriter->initNewArchive();
                $archiveWriter->insertRecord('nb_visits', 1); // records are ignored if visits is absent or == 0
                foreach (self::$oldReferrerRecordNames as $recordName) {
                    $archiveWriter->insertRecord($recordName, $testDataTableBlob);
                }
                foreach (self::$oldReferrerMetricNames as $recordName) {
                    $archiveWriter->insertRecord($recordName, 5);
                }
                $archiveWriter->finalizeArchive();
            }
        }
    }

    public function setUp()
    {
        Rules::$archivingDisabledByTests = false;
    }

    public function tearDown()
    {
        Rules::$archivingDisabledByTests = false;
    }

    /**
     * Dataprovider serving all search engine data
     */
    public function getSearchEngines()
    {
        include PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/SearchEngines.php';

        $searchEngines = array();
        foreach ($GLOBALS['Piwik_SearchEngines'] AS $url => $searchEngine) {
            $searchEngines[] = array($url, $searchEngine);
        }
        return $searchEngines;
    }

    /**
     * search engine has at least one keyword
     *
     * @group Plugins
     * @group Referrers
     * @dataProvider getSearchEngines
     */
    public function testMissingSearchEngineKeyword($url, $searchEngine)
    {
        // Get list of search engines and first appearing URL
        static $searchEngines = array();

        $name = parse_url('http://' . $url);
        if (!array_key_exists($searchEngine[0], $searchEngines)) {
            $searchEngines[$searchEngine[0]] = $url;

            $this->assertTrue(!empty($searchEngine[1]), $name['host']);
        }
    }

    /**
     * search engine is defined in DataFiles/SearchEngines.php but there's no favicon
     *
     * @group Plugins
     * @group Referrers
     * @dataProvider getSearchEngines
     */
    public function testMissingSearchEngineIcons($url, $searchEngine)
    {
        // Get list of existing favicons
        $favicons = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/Referrers/images/searchEngines/');

        // Get list of search engines and first appearing URL
        static $searchEngines = array();

        $name = parse_url('http://' . $url);
        if (!array_key_exists($searchEngine[0], $searchEngines)) {
            $searchEngines[$searchEngine[0]] = $url;

            $this->assertTrue(in_array($name['host'] . '.png', $favicons), $name['host']);
        }
    }

    /**
     * favicon exists but there's no corresponding search engine defined in DataFiles/SearchEngines.php
     *
     * @group Plugins
     * @group Referrers
     */
    public function testObsoleteSearchEngineIcons()
    {
        include PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/SearchEngines.php';

        // Get list of search engines and first appearing URL
        $searchEngines = array();
        foreach ($GLOBALS['Piwik_SearchEngines'] as $url => $searchEngine) {
            $name = parse_url('http://' . $url);
            if (!array_key_exists($name['host'], $searchEngines)) {
                $searchEngines[$name['host']] = true;
            }
        }

        // Get list of existing favicons
        $favicons = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/Referrers/images/searchEngines/');
        foreach ($favicons as $name) {
            if ($name[0] == '.' || strpos($name, 'xx.') === 0) {
                continue;
            }

            $host = substr($name, 0, -4);
            $this->assertTrue(array_key_exists($host, $searchEngines), $host);
        }
    }

    /**
     * get search engine host from url
     *
     * @group Plugins
     * @group Referrers
     */
    public function testGetSearchEngineHostFromUrl()
    {
        $data = array(
            'http://www.google.com/cse' => array('www.google.com', 'www.google.com/cse'),
            'http://www.google.com'     => array('www.google.com', 'www.google.com'),
        );

        foreach ($data as $url => $expected) {
            $this->assertEquals($expected[0], \Piwik\Plugins\Referrers\getSearchEngineHostFromUrl($url));
            $this->assertEquals($expected[1], \Piwik\Plugins\Referrers\getSearchEngineHostPathFromUrl($url));
        }
    }

    /**
     * Dataprovider for testGetSearchEngineUrlFromUrlAndKeyword
     */
    public function getSearchEngineUrlFromUrlAndKeywordTestData()
    {
        return array(
            array('http://apollo.lv/portal/search/', 'piwik', 'http://apollo.lv/portal/search/?cof=FORID%3A11&q=piwik&search_where=www'),
            array('http://bing.com/images/search', 'piwik', 'http://bing.com/images/search/?q=piwik'),
            array('http://google.com', 'piwik', 'http://google.com/search?q=piwik'),
        );
    }

    /**
     * get search engine url from name and keyword
     *
     * @group Plugins
     * @group Referrers
     * @dataProvider getSearchEngineUrlFromUrlAndKeywordTestData
     */
    public function testGetSearchEngineUrlFromUrlAndKeyword($url, $keyword, $expected)
    {
        include PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/SearchEngines.php';
        $this->assertEquals($expected, \Piwik\Plugins\Referrers\getSearchEngineUrlFromUrlAndKeyword($url, $keyword));
    }

    public function getReferrerReportsToTest()
    {
        $idSite = 1;

        $referrersApi = array(
            'Referrers.getReferrerType',
            'Referrers.getKeywords',
            'Referrers.getSearchEngines',
            'Referrers.getCampaigns',
            'Referrers.getWebsites',
            'Referrers.getNumberOfDistinctSearchEngines',
            'Referrers.getNumberOfDistinctKeywords',
            'Referrers.getNumberOfDistinctCampaigns',
            'Referrers.getNumberOfDistinctWebsites',
            'Referrers.getNumberOfDistinctWebsitesUrls'
        );

        return array(
            // test all Referrers reports alone
            array($referrersApi, array('idSite' => $idSite,
                                       'date' => '2010-05-06',
                                       'period' => 'day',
                                       'testSuffix' => '_referrerOldName')),

            // test one Referrers report with multiple sites + dates
            array('Referrers.getKeywords', array('idSite' => 'all',
                                                'date' => '2010-05-06,2010-05-07',
                                                'period' => 'day',
                                                'testSuffix' => '_referrerOldName_multiple')),
        );
    }

    /**
     * Test that if old Referrer blob names (Referrer_...) are found in the DB, they will be
     * used instead of launching archiving.
     *
     * @group        Integration
     * @group        OneVisitorTwoVisits
     * @dataProvider getReferrerReportsToTest
     */
    public function testReferrersReportsWhenOldBlobNameInDB($api, $params)
    {
        $this->runApiTests($api, $params);
    }
}