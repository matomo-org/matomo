<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

class Test_Piwik_Integration_OneVisitor_SeveralDays_ImportedInRandomOrderTest extends IntegrationTestCase
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
        return array(
            // This should show 1 visit on 3 different days
            array('Live.getLastVisitsDetails', array(
                                    'idSite' => '1',
                                    'date'         => self::$fixture->dateTime,
                                    'periods'      => 'month',
                                    'testSuffix'             => '_shouldShowOneVisit_InEachOfThreeDays',
                                    'otherRequestParameters' => array('hideColumns' => 'visitorId')

            )),
        );
    }



}

Test_Piwik_Integration_OneVisitor_SeveralDays_ImportedInRandomOrderTest::$fixture = new Test_Piwik_Fixture_VisitOverSeveralDaysImportedLogs();

