<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Benchmarks/Fixtures/SqlDump.php';

/**
 * Tests that Piwik 2.0 works w/ data from Piwik 1.13.
 */
class Test_Piwik_Integration_BackwardsCompatibility1XTest extends IntegrationTestCase
{
    const FIXTURE_LOCATION = '/tests/resources/piwik-1.13-dump.sql';

    public static $fixture = null; // initialized below class
    public static $defaultApiNotToCall = null; // initialized below class

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
        $idSite = 1;
        $dateTime = '2010-03-06 11:22:33';

        return array(
            array('all', array('idSite' => $idSite, 'date' => $dateTime, 'compareAgainst' => 'OneVisitorTwoVisits',
                               'disableArchiving' => true)),
        );
    }
}

Test_Piwik_Integration_BackwardsCompatibility1XTest::$fixture = new Piwik_Test_Fixture_SqlDump();
Test_Piwik_Integration_BackwardsCompatibility1XTest::$fixture->dumpUrl =
    PIWIK_INCLUDE_PATH . Test_Piwik_Integration_BackwardsCompatibility1XTest::FIXTURE_LOCATION;
Test_Piwik_Integration_BackwardsCompatibility1XTest::$fixture->tablesPrefix = 'piwiktests_';

// NOTE: VisitFrequency.get cannot be tested since it now uses a segment and thus requires archiving
//       to be enabled.
Test_Piwik_Integration_BackwardsCompatibility1XTest::$defaultApiNotToCall =
    array_merge(IntegrationTestCase::$defaultApiNotToCall, array('Referrers', 'VisitFrequency.get'));