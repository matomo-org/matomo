<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration;

use Piwik\API\Request;
use Piwik\Config;
use Piwik\Tests\Fixtures\SiteVisitsWithInvalidation;
use Piwik\Tests\IntegrationTestCase;
use Exception;

/**
 * Track visits before website creation date and test that Piwik handles them correctly.
 *
 * @group Integration
 * @group ArchiveInvalidationTest
 */
class ArchiveInvalidationTest extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    /**
     * This should NOT return data for old dates before website creation
     */
    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTimeDateInPastWebsite = self::$fixture->dateTimeFirstDateWebsite;

        // We test a typical Numeric and a Recursive blob reports
        $apiToCall = array('VisitsSummary.get', 'Actions.getPageUrls');

        // We also test a segment
        //TODO

        // Build tests for the 2 websites
        return array(
            array($apiToCall, array('idSite'                 => $idSite,
                                    'testSuffix'             => 'Website' . $idSite . '_NewDataShouldNotAppear',
                                    'date'                   => $dateTimeDateInPastWebsite,
                                    'periods'                => 'month',
                                    'setDateLastN'           => 4, // 4months ahead
                                    'otherRequestParameters' => array('expanded' => 1)))
        );
    }

    /**
     * @depends testApi
     * @dataProvider getSameApiForTesting
     */
    public function testSameApi($api, $params)
    {

        self::$fixture->trackMoreVisits();

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        $idSite = self::$fixture->idSite;
        $dateTimeDateInPastWebsite = new \DateTime(self::$fixture->dateTimeFirstDateWebsite);

        $r = new Request("module=API&method=CoreAdminHome.invalidateArchivedReports&idSites=" . $idSite . "&dates=" . $dateTimeDateInPastWebsite->format('Y-m-d'));
        $this->assertApiResponseHasNoError($r->process());

        // 2) Call API again, with an older date, which should now return data
        $this->runApiTests($api, $params);

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;

    }

    public function getSameApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTimeFirstDateWebsite = self::$fixture->dateTimeFirstDateWebsite;

        $apiToCall = array('VisitsSummary.get', 'Actions.getPageUrls');

        return array(
            array($apiToCall, array('idSite'                 => $idSite,
                                    'testSuffix'             => 'Website' . $idSite . '_NewDataShouldNotAppear',
                                    'date'                   => $dateTimeFirstDateWebsite,
                                    'periods'                => 'month',
                                    'setDateLastN'           => 4, // 4months ahead
                                    'otherRequestParameters' => array('expanded' => 1))),
        );
    }

    /**
     * @depends      testApi
     * @depends      testSameApi
     * @dataProvider getAnotherApiForTesting
     */
    public function testAnotherApi($api, $params)
    {
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;
        $idSite = self::$fixture->idSite;
        $dateTimeDateInPastWebsite = new \DateTime(self::$fixture->dateTimeFirstDateWebsite);

        $r = new Request("module=API&method=CoreAdminHome.invalidateArchivedReports&idSites=" . $idSite . "&dates=" . $dateTimeDateInPastWebsite->format('Y-m-d'));
        $this->assertApiResponseHasNoError($r->process());

        // 2) Call API again, with an older date, which should now return data
        $this->runApiTests($api, $params);
    }

    /**
     * This is called after getApiToTest()
     * WE invalidate old reports and check that data is now returned for old dates
     */
    public function getAnotherApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTimeFirstDateWebsite = self::$fixture->dateTimeFirstDateWebsite;

        $apiToCall = array('VisitsSummary.get', 'Actions.getPageUrls');

        return array(
            array($apiToCall, array('idSite'                 => $idSite,
                                    'testSuffix'             => 'Website' . $idSite . '_NewDataShouldAppear',
                                    'date'                   => $dateTimeFirstDateWebsite,
                                    'periods'                => 'month',
                                    'setDateLastN'           => 4, // 4months ahead
                                    'otherRequestParameters' => array('expanded' => 1))),
        );
    }

    public static function getOutputPrefix()
    {
        return 'Archive_Invalidation';
    }
}

ArchiveInvalidationTest::$fixture = new SiteVisitsWithInvalidation();