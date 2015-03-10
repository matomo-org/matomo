<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\Date;
use Piwik\Option;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Archive\ArchiveInvalidator;

/**
 * @group Archiver
 * @group ArchiveInvalidator
 * @group DataAccess
 */
class ArchiveInvalidatorTest extends IntegrationTestCase
{
    /**
     * @var ArchiveInvalidator
     */
    private $invalidator;

    public function setUp()
    {
        parent::setUp();

        $this->invalidator = new ArchiveInvalidator();
    }

    public function test_rememberToInvalidateArchivedReportsLater_shouldCreateAnEntryInCaseThereIsNoneYet()
    {
        $key = 'report_to_invalidate_2_2014-04-05';
        $this->assertFalse(Option::get($key));

        $this->rememberReport(2, '2014-04-05');

        $this->assertSame('1', Option::get($key));
    }

    public function test_rememberToInvalidateArchivedReportsLater_shouldNotCreateEntryTwice()
    {
        $this->rememberReport(2, '2014-04-05');
        $this->rememberReport(2, '2014-04-05');
        $this->rememberReport(2, '2014-04-05');

        $this->assertCount(1, Option::getLike('report_to_invalidate%'));
    }

    public function test_getRememberedArchivedReportsThatShouldBeInvalidated_shouldNotReturnEntriesInCaseNoneAreRemembered()
    {
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $this->assertSame(array(), $reports);
    }

    public function test_getRememberedArchivedReportsThatShouldBeInvalidated_shouldGroupEntriesByDate()
    {
        $this->rememberReportsForManySitesAndDates();

        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $this->assertSame($this->getRememberedReportsByDate(), $reports);
    }

    public function test_forgetRememberedArchivedReportsToInvalidateForSite_shouldNotDeleteAnythingInCaseNoReportForThatSite()
    {
        $this->rememberReportsForManySitesAndDates();

        $this->invalidator->forgetRememberedArchivedReportsToInvalidateForSite(10);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $this->assertSame($this->getRememberedReportsByDate(), $reports);
    }

    public function test_forgetRememberedArchivedReportsToInvalidateForSite_shouldOnlyDeleteReportsBelongingToThatSite()
    {
        $this->rememberReportsForManySitesAndDates();

        $this->invalidator->forgetRememberedArchivedReportsToInvalidateForSite(7);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $expected = array(
            '2014-04-05' => array(1, 2, 4),
            '2014-05-05' => array(2, 5),
            '2014-04-06' => array(3)
        );
        $this->assertSame($expected, $reports);
    }

    public function test_forgetRememberedArchivedReportsToInvalidate_shouldNotForgetAnythingIfThereIsNoMatch()
    {
        $this->rememberReportsForManySitesAndDates();

        // site does not match
        $this->invalidator->forgetRememberedArchivedReportsToInvalidate(10, Date::factory('2014-04-05'));
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSame($this->getRememberedReportsByDate(), $reports);

        // date does not match
        $this->invalidator->forgetRememberedArchivedReportsToInvalidate(7, Date::factory('2012-04-05'));
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSame($this->getRememberedReportsByDate(), $reports);
    }

    public function test_forgetRememberedArchivedReportsToInvalidate_shouldOnlyDeleteReportBelongingToThatSiteAndDate()
    {
        $this->rememberReportsForManySitesAndDates();

        $this->invalidator->forgetRememberedArchivedReportsToInvalidate(2, Date::factory('2014-04-05'));
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $expected = array(
            '2014-04-05' => array(1, 4, 7),
            '2014-05-05' => array(2, 5),
            '2014-04-06' => array(3),
            '2014-04-08' => array(7),
            '2014-05-08' => array(7),
        );
        $this->assertSame($expected, $reports);

        unset($expected['2014-05-08']);

        $this->invalidator->forgetRememberedArchivedReportsToInvalidate(7, Date::factory('2014-05-08'));
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSame($expected, $reports);
    }

    public function test_markArchivesAsInvalidated_shouldForgetInvalidatedSitesAndDates()
    {
        $this->rememberReportsForManySitesAndDates();

        $idSites = array(2, 10, 7, 5);
        $dates   = '2014-04-05,2014-04-08,2010-10-10';
        $this->invalidator->markArchivesAsInvalidated($idSites, $dates, false);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $expected = array(
            '2014-04-05' => array(1, 4),
            '2014-05-05' => array(2, 5),
            '2014-04-06' => array(3),
            '2014-05-08' => array(7),
        );
        $this->assertSame($expected, $reports);
    }

    private function rememberReport($idSite, $date)
    {
        $date = Date::factory($date);
        $this->invalidator->rememberToInvalidateArchivedReportsLater($idSite, $date);
    }

    private function getRememberedReportsByDate()
    {
        return array(
            '2014-04-05' => array(1, 2, 4, 7),
            '2014-05-05' => array(2, 5),
            '2014-04-06' => array(3),
            '2014-04-08' => array(7),
            '2014-05-08' => array(7),
        );
    }

    private function rememberReportsForManySitesAndDates()
    {
        $this->rememberReport(2, '2014-04-05');
        $this->rememberReport(2, '2014-04-05'); // should appear only once for this site and date
        $this->rememberReport(3, '2014-04-06');
        $this->rememberReport(1, '2014-04-05');
        $this->rememberReport(2, '2014-05-05');
        $this->rememberReport(5, '2014-05-05');
        $this->rememberReport(4, '2014-04-05');
        $this->rememberReport(7, '2014-04-05');
        $this->rememberReport(7, '2014-05-08');
        $this->rememberReport(7, '2014-04-08');
    }
}
