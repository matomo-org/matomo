<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Test CSV export with Expanded rows, Translated labels, Different languages
 */
class Test_Piwik_Integration_CsvExport extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        $apiToCall = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');

        $enExtraParam = array('expanded' => 0, 'flat' => 1, 'include_aggregate_rows' => 0, 'translateColumnNames' => 1);

        $deExtraParam = array('expanded' => 0, 'flat' => 1, 'include_aggregate_rows' => 1, 'translateColumnNames' => 1);

        return array(
            array($apiToCall, array('idSite'                 => $idSite,
                                    'date'                   => $dateTime,
                                    'format'                 => 'csv',
                                    'otherRequestParameters' => array('expanded' => 0, 'flat' => 0),
                                    'testSuffix'             => '_xp0')),

            array($apiToCall, array('idSite'                 => $idSite,
                                    'date'                   => $dateTime,
                                    'format'                 => 'csv',
                                    'otherRequestParameters' => $enExtraParam,
                                    'language'               => 'en',
                                    'testSuffix'             => '_xp1_inner0_trans-en')),

            array($apiToCall, array('idSite'                 => $idSite,
                                    'date'                   => $dateTime,
                                    'format'                 => 'csv',
                                    'otherRequestParameters' => $deExtraParam,
                                    'language'               => 'de',
                                    'testSuffix'             => '_xp1_inner1_trans-de')),

            // TODO: we cannot currently test the csv output of Live.getLastVisitsDetails. The API method includes
            //       random output. Normally we remove this when it's in XML output, but it's harder to do that w/
            //       CSV output. For now, we use compareOutput => false to just check that no errors result.
            array('Live.getLastVisitsDetails', array('idSite'        => $idSite,
                                                     'date'          => $dateTime,
                                                     'format'        => 'csv',
                                                     'compareOutput' => false))
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

    public static function getOutputPrefix()
    {
        return 'csvExport';
    }
}

Test_Piwik_Integration_CsvExport::$fixture = new Test_Piwik_Fixture_TwoVisitsWithCustomVariables();
Test_Piwik_Integration_CsvExport::$fixture->visitorId = null;
Test_Piwik_Integration_CsvExport::$fixture->useEscapedQuotes = false;
Test_Piwik_Integration_CsvExport::$fixture->doExtraQuoteTests = false;

