<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Testing that, when using window_look_back_for_visitor with a high value,
 * works well with the use case of a returning visitor being assigned to today's visit
 *
 */
class Test_Piwik_Integration_TrackerWindowLookBack extends IntegrationTestCase
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
        $idSite = self::$fixture->idSite;

        return array(
            array('VisitsSummary.getVisits', array( 'date'    => '2010-12-01,2011-01-31',
                                                    'periods' => array('range'),
                                                    'idSite' => $idSite,
            ))
        );
    }

    public static function getOutputPrefix()
    {
        return 'TrackerWindowLookBack';
    }
}

Test_Piwik_Integration_TrackerWindowLookBack::$fixture = new Test_Piwik_Fixture_VisitsOverSeveralDays();
Test_Piwik_Integration_TrackerWindowLookBack::$fixture->forceLargeWindowLookBackForVisitor = true;
