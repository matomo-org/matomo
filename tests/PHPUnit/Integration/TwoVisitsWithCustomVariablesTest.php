<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration;

use Piwik\Tests\IntegrationTestCase;
use Piwik\Tests\Fixtures\TwoVisitsWithCustomVariables;

/**
 * Tests w/ two visits & custom variables.
 *
 * @group TwoVisitsWithCustomVariablesTest
 * @group Integration
 */
class TwoVisitsWithCustomVariablesTest extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        $apiToCall = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');

        $return = array(
            array($apiToCall, array('idSite'       => 'all',
                                    'date'         => $dateTime,
                                    'periods'      => array('day', 'week'),
                                    'setDateLastN' => true)),

            // test getProcessedReport w/ custom variables subtable
            array('API.getProcessedReport', array('idSite'        => $idSite,
                                                  'date'          => $dateTime,
                                                  'periods'       => 'day',
                                                  'apiModule'     => 'CustomVariables',
                                                  'apiAction'     => 'getCustomVariablesValuesFromNameId',
                                                  'supertableApi' => 'CustomVariables.getCustomVariables',
                                                  'testSuffix'    => '__subtable')),
        );

        return $return;
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public static function getOutputPrefix()
    {
        return 'twoVisitsWithCustomVariables';
    }
}

TwoVisitsWithCustomVariablesTest::$fixture = new TwoVisitsWithCustomVariables();