<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * Tests that filter_truncate works recursively in Page URLs report AND in the case there are 2 different data Keywords -> search engine
 */
class Test_Piwik_Integration_OneVisitor_LongUrlsTruncated extends IntegrationTestCase
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
            'Referrers.getKeywords',
            'Actions.getPageUrls',

            // Specifically testing getPlugin filter_truncate works
            'UserSettings.getPlugin');

        return array(
            array($apiToCall, array('idSite'                 => self::$fixture->idSite,
                                    'date'                   => self::$fixture->dateTime,
                                    'language'               => 'fr',
                                    'otherRequestParameters' => array('expanded' => 1, 'filter_truncate' => 2)))
        );
    }

    public static function getOutputPrefix()
    {
        return 'OneVisitor_LongUrlsTruncated';
    }
}

Test_Piwik_Integration_OneVisitor_LongUrlsTruncated::$fixture = new Test_Piwik_Fixture_SomeVisitsWithLongUrls();

