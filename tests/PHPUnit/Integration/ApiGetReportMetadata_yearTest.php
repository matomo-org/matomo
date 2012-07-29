<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

/**
 * test the Yearly metadata API response, 
 * with no visits, with custom response language 
 */
class Test_Piwik_Integration_ApiGetReportMetadata_Year extends IntegrationTestCase
{
    protected static $idSite   = 1;
    protected static $dateTime = '2009-01-04 00:11:42';

    protected function setUpWebsitesAndGoals()
    {
        $this->createWebsite(self::$dateTime);
    }

    protected function trackVisits()
    {
    }

    public function getApiForTesting()
    {
        $params = array('idSite'   => self::$idSite,
                        'date'     => self::$dateTime,
                        'periods'  => 'year',
                        'language' => 'fr');
        return array(
            array('API.getProcessedReport', $params),
            array('API.getReportMetadata', $params),
            array('LanguagesManager.getTranslationsForLanguage', $params),
            array('LanguagesManager.getAvailableLanguageNames', $params),
            array('SitesManager.getJavascriptTag', $params)
        );
    }

    public function getOutputPrefix()
    {
        return 'apiGetReportMetadata_year';
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        ApiGetReportMetadata
     * @group        ApiGetReportMetadata_Year
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }
}
