<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Config;
use Piwik\Plugins\Actions\ArchivingHelper;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/MockLocationProvider.php';

/**
 * Test Piwik's report limiting code. Make sure the datatable_archiving_maximum_rows_...
 * config options limit the size of certain reports when archiving.
 */
class Test_Piwik_Integration_BlobReportLimitingTest extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    public static function setUpBeforeClass()
    {
        self::setUpConfigOptions();
        parent::_setUpBeforeClass($dbName = false, $createEmptyDatabase = true, $createConfig = false);
        parent::setUpFixture(self::$fixture);
    }

    public function getApiForTesting()
    {
        // TODO: test Provider plugin? Not sure if it's possible.
        $apiToCall = array(
            'Actions.getPageUrls', 'Actions.getPageTitles', 'Actions.getDownloads', 'Actions.getOutlinks',
            'CustomVariables.getCustomVariables',
            'Referrers.getReferrerType', 'Referrers.getKeywords', 'Referrers.getSearchEngines',
            'Referrers.getWebsites', 'Referrers.getAll', /* TODO 'Referrers.getCampaigns', */
            'UserSettings.getResolution', 'UserSettings.getConfiguration', 'UserSettings.getOS',
            'UserSettings.getBrowserVersion',
            'UserCountry.getRegion', 'UserCountry.getCity',
        );
        
        $ecommerceApi = array('Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory');

        return array(
            array($apiToCall, array('idSite'  => self::$fixture->idSite,
                                    'date'    => self::$fixture->dateTime,
                                    'periods' => array('day'))),
            
            array($ecommerceApi, array('idSite'  => self::$fixture->idSite,
                                       'date'    => self::$fixture->nextDay,
                                       'periods' => 'day')),
        );
    }
    
    public function getRankingQueryDisabledApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;
        
        return array(
            array('Actions.getPageUrls', array('idSite'  => $idSite,
                                               'date'    => $dateTime,
                                               'periods' => array('day'))),
            
            array('Provider.getProvider', array('idSite'  => $idSite,
                                                'date'    => $dateTime,
                                                'periods' => array('month'))),
            
            array('Provider.getProvider', array('idSite'     => $idSite,
                                                'date'       => $dateTime,
                                                'periods'    => array('month'),
                                                'segment'    => 'provider==comcast.net',
                                                'testSuffix' => '_segment_provider')),
            
            // test getDownloads w/ period=range & flat=1
            array('Actions.getDownloads', array('idSite'                 => $idSite,
                                                'date'                   => '2010-01-02,2010-01-05',
                                                'periods'                => 'range',
                                                'testSuffix'             => '_rangeFlat',
                                                'otherRequestParameters' => array(
                                                    'flat'               => 1,
                                                    'expanded'           => 0
                                                ))),
        );
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    /**
     * @group        Integration
     */
    public function testApiWithRankingQuery()
    {
        // custom setup
        self::deleteArchiveTables();
        Config::getInstance()->General['archiving_ranking_query_row_limit'] = 3;
        ArchivingHelper::reloadConfig();

        foreach ($this->getApiForTesting() as $pair) {
            list($apiToCall, $params) = $pair;
            
            if (empty($params['testSuffix'])) {
                $params['testSuffix'] = '';
            }
            $params['testSuffix'] .= '_rankingQuery';

            $this->runApiTests($apiToCall, $params);
        }
    }
    
    /**
     * @group        Integration
     */
    public function testApiWithRankingQueryDisabled()
    {
        self::deleteArchiveTables();
        $generalConfig =& Config::getInstance()->General;
        $generalConfig['datatable_archiving_maximum_rows_referrers'] = 500;
        $generalConfig['datatable_archiving_maximum_rows_subtable_referrers'] = 500;
        $generalConfig['datatable_archiving_maximum_rows_actions'] = 500;
        $generalConfig['datatable_archiving_maximum_rows_subtable_actions'] = 500;
        $generalConfig['datatable_archiving_maximum_rows_standard'] = 500;
        $generalConfig['datatable_archiving_maximum_rows_custom_variables'] = 500;
        $generalConfig['datatable_archiving_maximum_rows_subtable_custom_variables'] = 500;
        $generalConfig['archiving_ranking_query_row_limit'] = 0;
        
        foreach ($this->getRankingQueryDisabledApiForTesting() as $pair) {
            list($apiToCall, $params) = $pair;
            
            if (empty($params['testSuffix'])) {
                $params['testSuffix'] = '';
            }
            $params['testSuffix'] .= '_rankingQueryDisabled';

            $this->runApiTests($apiToCall, $params);
        }
    }

    public static function getOutputPrefix()
    {
        return 'reportLimiting';
    }

    protected static function setUpConfigOptions()
    {
        self::createTestConfig();
        $generalConfig =& Config::getInstance()->General;
        $generalConfig['datatable_archiving_maximum_rows_referers'] = 3;
        $generalConfig['datatable_archiving_maximum_rows_subtable_referers'] = 2;
        $generalConfig['datatable_archiving_maximum_rows_actions'] = 3;
        $generalConfig['datatable_archiving_maximum_rows_custom_variables'] = 3;
        $generalConfig['datatable_archiving_maximum_rows_subtable_custom_variables'] = 2;
        $generalConfig['datatable_archiving_maximum_rows_subtable_actions'] = 2;
        $generalConfig['datatable_archiving_maximum_rows_standard'] = 3;
        $generalConfig['archiving_ranking_query_row_limit'] = 50000;
    }
}

Test_Piwik_Integration_BlobReportLimitingTest::$fixture = new Test_Piwik_Fixture_ManyVisitsWithMockLocationProvider();

