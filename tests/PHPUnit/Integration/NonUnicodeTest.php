<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Tests that visits track & reports display correctly when non-unicode text is
 * used in URL query params of visits.
 */
class Test_Piwik_Integration_NonUnicodeTest extends IntegrationTestCase
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
        $apiToCall = array(
            'Actions.getSiteSearchKeywords',
            'Actions.getPageTitles',
            'Actions.getPageUrls',
            'Referrers.getWebsites',
        );

        return array(
            array($apiToCall, array('idSite'  => self::$fixture->idSite1,
                                    'date'    => self::$fixture->dateTime,
                                    'periods' => 'day'))
        );
    }

    public static function getOutputPrefix()
    {
        return 'NonUnicode';
    }

}

Test_Piwik_Integration_NonUnicodeTest::$fixture =
    new Test_Piwik_Fixture_SomeVisitsWithNonUnicodePageTitles();

