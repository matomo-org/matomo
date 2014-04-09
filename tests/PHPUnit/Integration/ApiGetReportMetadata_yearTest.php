<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * test the Yearly metadata API response,
 * with no visits, with custom response language
 */
class Test_Piwik_Integration_ApiGetReportMetadata_Year extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    public function getApiForTesting()
    {
        $params = array('idSite'   => self::$fixture->idSite,
                        'date'     => self::$fixture->dateTime,
                        'periods'  => 'year',
                        'language' => 'fr');
        return array(
            array('API.getProcessedReport', $params),
            array('LanguagesManager.getAvailableLanguageNames', $params),
            array('SitesManager.getJavascriptTag', $params)
        );
    }

    public static function getOutputPrefix()
    {
        return 'apiGetReportMetadata_year';
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }
}

Test_Piwik_Integration_ApiGetReportMetadata_Year::$fixture = new Test_Piwik_Fixture_InvalidVisits();
Test_Piwik_Integration_ApiGetReportMetadata_Year::$fixture->trackInvalidRequests = false;

