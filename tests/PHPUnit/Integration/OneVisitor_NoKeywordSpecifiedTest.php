<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * 1) Tests empty google kwd works nicely in Live! output and Top keywords
 * 2) Tests IP anonymization
 * Also test that Live! will link to the search result page URL rather than the exact referrer URL
 * when the referrer URL is google.XX/url.... which is a redirect to landing page rather than the search result URL
 */
class Test_Piwik_Integration_OneVisitor_NoKeywordSpecified extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array('Referrers.getKeywords', 'Live.getLastVisitsDetails');

        return array(
            array($apiToCall, array('idSite'   => self::$fixture->idSite,
                                    'date'     => self::$fixture->dateTime,
                                    'language' => 'fr'))
        );
    }

    public static function getOutputPrefix()
    {
        return 'OneVisitor_NoKeywordSpecified';
    }
}

Test_Piwik_Integration_OneVisitor_NoKeywordSpecified::$fixture = new Test_Piwik_Fixture_TwoVisitsNoKeywordWithBot();

