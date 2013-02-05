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
    protected static $idSite   = 1;
    protected static $dateTime = '2009-01-04 00:11:42';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        try {
            self::setUpWebsitesAndGoals();
            self::trackVisits();
        } catch(Exception $e) {
            // Skip whole test suite if an error occurs while setup
            throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
        }
    }

    protected static function setUpWebsitesAndGoals()
    {
        self::createWebsite(self::$dateTime);
    }

    protected static function trackVisits()
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
            // @todo  reenable me
            //array('API.getReportMetadata', $params),
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
