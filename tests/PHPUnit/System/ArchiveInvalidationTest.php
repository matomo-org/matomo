<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Tests\Fixtures\VisitsTwoWebsitesWithAdditionalVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Track visits before website creation date and test that Piwik handles them correctly.
 *
 * This tests that the API method invalidateArchivedReports works correctly, that it deletes data:
 * - on one or multiple websites
 * - for a given set of dates (and optional period)
 *
 * @group Core
 * @group ArchiveInvalidationTest
 */
class ArchiveInvalidationTest extends SystemTestCase
{
    const TEST_SEGMENT = 'pageUrl=@category%252F';

    /**
     * @var VisitsTwoWebsitesWithAdditionalVisits
     */
    public static $fixture = null; // initialized below class definition

    protected $suffix = '_NewDataShouldNotAppear';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::addSegments();
    }

    private static function addSegments()
    {
        Rules::setBrowserTriggerArchiving(false);
        API::getInstance()->add('segment 1', urlencode(self::TEST_SEGMENT));
        Rules::setBrowserTriggerArchiving(true);
    }

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
        // We test a typical Numeric and a Recursive blob reports
        $apiToCall = array('VisitsSummary.get', 'Actions.getPageUrls');

        // Build tests for the 2 websites
        return array(

            array($apiToCall, array('idSite'                 => self::$fixture->idSite2,
                                    'testSuffix'             => 'Website' . self::$fixture->idSite2 . $this->suffix,
                                    'date'                   => self::$fixture->dateTimeFirstDateWebsite2,
                                    'periods'                => 'day',
                                    'segment'                => self::TEST_SEGMENT,
                                    'setDateLastN'           => 4, // 4months ahead
                                    'otherRequestParameters' => array('expanded' => 1))
            ),
            array($apiToCall, array('idSite'                 => self::$fixture->idSite1,
                                    'testSuffix'             => 'Website' . self::$fixture->idSite1 . $this->suffix,
                                    'date'                   => self::$fixture->dateTimeFirstDateWebsite1,
                                    'periods'                => 'month',
                                    'setDateLastN'           => 4, // 4months ahead
                                    'otherRequestParameters' => array('expanded' => 1))
            ),

            array($apiToCall, array('idSite'                 => self::$fixture->idSite2,
                                    'testSuffix'             => 'Website' . self::$fixture->idSite2 . $this->suffix,
                                    'date'                   => self::$fixture->dateTimeFirstDateWebsite2,
                                    'periods'                => 'month',
                                    'segment'                => self::TEST_SEGMENT,
                                    'setDateLastN'           => 4, // 4months ahead
                                    'otherRequestParameters' => array('expanded' => 1))
            )
        );
    }

    /**
     * test same api w/o invalidating or tracking (which also invalidates), (NewDataShouldNotAppear)
     *
     * @depends      testApi
     * @dataProvider getApiForTesting
     */
    public function testSameApi($api, $params)
    {
        Rules::setBrowserTriggerArchiving(false);
        $this->runApiTests($api, $params);
    }

    /**
     * test same api after invalidating (NewDataShouldAppear)
     *
     * @depends      testApi
     * @depends      testSameApi
     */
    public function testAnotherApi()
    {
        self::$fixture->trackMoreVisits(self::$fixture->idSite1);
        self::$fixture->trackMoreVisits(self::$fixture->idSite2);

        Rules::setBrowserTriggerArchiving(true);

        foreach ($this->getAnotherApiForTesting() as list($api, $params)) {
            $this->runApiTests($api, $params);
        }
    }

    /**
     * This is called after getApiToTest()
     * We invalidate old reports and check that data is now returned for old dates
     */
    public function getAnotherApiForTesting()
    {
        $this->suffix = '_NewDataShouldAppear';
        return $this->getApiForTesting();
    }

    public static function getOutputPrefix()
    {
        return 'Archive_Invalidation';
    }

    protected function invalidateTestArchives()
    {
        $dateToInvalidate1 = new \DateTime(self::$fixture->dateTimeFirstDateWebsite1);

        $r = new Request("module=API&method=CoreAdminHome.invalidateArchivedReports&idSites=" . self::$fixture->idSite1 . "&dates=" . $dateToInvalidate1->format('Y-m-d'));
        $this->assertApiResponseHasNoError($r->process());

        // week reports only are invalidated. we test our daily report will show new data, even though weekly reports only are invalidated,
        // because when we track data, it invalidates day periods as well.
        $this->invalidateTestArchive(self::$fixture->idSite2, 'week', self::$fixture->dateTimeFirstDateWebsite2);
    }

    private function invalidateTestArchive($idSite, $period, $dateTime, $cascadeDown = false)
    {
        $dates = new \DateTime($dateTime);
        $dates = $dates->format('Y-m-d');
        $r = new Request("module=API&method=CoreAdminHome.invalidateArchivedReports&period=$period&idSites=$idSite&dates=$dates&cascadeDown=" . (int)$cascadeDown);
        $this->assertApiResponseHasNoError($r->process());
    }
}

ArchiveInvalidationTest::$fixture = new VisitsTwoWebsitesWithAdditionalVisits();