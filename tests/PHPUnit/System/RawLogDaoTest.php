<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\DataAccess\RawLogDao;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Core
 * @group RawLogDao
 * @group RawLogDaoTest
 */
class RawLogDaoTest extends SystemTestCase
{
    /**
     * @var RawLogDao
     */
    private $dao;

    private $idSite = 1;

    public function setUp()
    {
        parent::setUp();

        if (!Fixture::siteCreated($this->idSite)) {
            Fixture::createWebsite('2010-00-00 00:00:00');
        }

        $this->dao = new RawLogDao();
    }

    /**
     * @dataProvider getVisitsInTimeFrameData
     */
    public function test_hasSiteVisitsInTimeframe_shouldDetectWhetherThereAreVisitsInCertainTimeframe($from, $to, $idSite, $expectedHasVisits)
    {
        Fixture::getTracker($this->idSite, '2015-01-25 05:35:27')->doTrackPageView('/test');

        $hasVisits = $this->dao->hasSiteVisitsBetweenTimeframe($from, $to, $idSite);
        $this->assertSame($expectedHasVisits, $hasVisits);
    }

    public function getVisitsInTimeFrameData()
    {
        return array(
            array($from = '2015-01-25 05:35:26', $to = '2015-01-25 05:35:27', $this->idSite, $hasVisits = false), // there is no second "between" the timeframe so cannot have visits
            array($from = '2015-01-25 05:35:27', $to = '2015-01-25 05:35:28', $this->idSite, $hasVisits = false), // there is no second "between" the timeframe so cannot have visits
            array($from = '2015-01-25 05:35:26', $to = '2015-01-25 05:35:28', $this->idSite, $hasVisits = true), // only one sec difference between from and to
            array($from = '2015-01-25 05:35:26', $to = '2015-01-26 05:35:27', $this->idSite, $hasVisits = true),
            array($from = '2015-01-24 05:35:26', $to = '2015-01-26 05:35:27', $this->idSite, $hasVisits = true),
            array($from = '2015-01-25 05:35:26', $to = '2015-01-25 05:35:27', $idSite = 2, $hasVisits = false),  // no because idSite does not match
            array($from = '2015-01-24 05:35:26', $to = '2015-01-25 05:35:27', $idSite = 2, $hasVisits = false),  // ...
            array($from = '2015-01-25 05:35:26', $to = '2015-01-26 05:35:27', $idSite = 2, $hasVisits = false),  // ...
            array($from = '2015-01-24 05:35:26', $to = '2015-01-26 05:35:27', $idSite = 2, $hasVisits = false),  // ... no because not matching idsite
            array($from = '2015-01-24 05:35:26', $to = '2015-01-25 05:35:26', $this->idSite, $hasVisits = false), // time of visit is later
            array($from = '2015-01-25 05:35:28', $to = '2015-01-27 05:35:27', $this->idSite, $hasVisits = false),  // time of visit is earlier
        );
    }

}
