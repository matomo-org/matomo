<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

require_once dirname(__FILE__).'/TwoVisitsWithCustomVariablesTest.php';

/**
 * Test CSV export with Expanded rows, Translated labels, Different languages
 */
class Test_Piwik_Integration_CsvExport extends Test_Piwik_Integration_TwoVisitsWithCustomVariables
{

    public static function setUpBeforeClass()
    {
        IntegrationTestCase::setUpBeforeClass();
        self::$visitorId = substr(md5(uniqid()), 0, 16);
        self::setUpWebsitesAndGoals();
        self::trackVisits();
    }

    protected static function trackVisits() {
        self::$useEscapedQuotes  = false;
        self::$doExtraQuoteTests = false;
        parent::trackVisits();
    }

    public function getApiForTesting()
    {
        $apiToCall    = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');

        $enExtraParam = array('expanded' => 0, 'flat' => 1, 'include_aggregate_rows' => 0, 'translateColumnNames' => 1);

        $deExtraParam = array('expanded' => 0, 'flat' => 1, 'include_aggregate_rows' => 1, 'translateColumnNames' => 1);

        return array(
            array($apiToCall, array('idSite'                 => self::$idSite,
                                    'date'                   => self::$dateTime,
                                    'format'                 => 'csv',
                                    'otherRequestParameters' => array('expanded' => 0, 'flat' => 0),
                                    'testSuffix'             => '_xp0')),

            array($apiToCall, array('idSite'                 => self::$idSite,
                                    'date'                   => self::$dateTime,
                                    'format'                 => 'csv',
                                    'otherRequestParameters' => $enExtraParam,
                                    'language'               => 'en',
                                    'testSuffix'             => '_xp1_inner0_trans-en')),

            array($apiToCall, array('idSite'                 => self::$idSite,
                                    'date'                   => self::$dateTime,
                                    'format'                 => 'csv',
                                    'otherRequestParameters' => $deExtraParam,
                                    'language'               => 'de',
                                    'testSuffix'             => '_xp1_inner1_trans-de')),
        );
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        CsvExport
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getOutputPrefix()
    {
        return 'csvExport';
    }
}
