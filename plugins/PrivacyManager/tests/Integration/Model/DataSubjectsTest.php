<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration\Model;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\MultipleSitesMultipleVisitsFixture;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\TestLogFoo;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\TestLogFooBar;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\TestLogFooBarBaz;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;

/**
 * Class DataSubjectsTest
 *
 * @group DataSubjectsTest
 * @group Plugins
 */
class DataSubjectsTest extends IntegrationTestCase
{
    /**
     * @var DataSubjects
     */
    private $dataSubjects;

    /**
     * @var MultipleSitesMultipleVisitsFixture
     */
    private $theFixture;

    private $originalTrackingTime;

    private $originalTimezone;

    public function setUp(): void
    {
        parent::setUp();

        $this->theFixture = new MultipleSitesMultipleVisitsFixture();
        $this->theFixture->installLogTables();
        $this->theFixture->setUpLocation();

        $logTablesProvider = StaticContainer::get('Piwik\Plugin\LogTablesProvider');
        $this->dataSubjects = new DataSubjects($logTablesProvider);
        $this->originalTimezone = ini_get('date.timezone');
        $this->originalTrackingTime = $this->theFixture->trackingTime;
    }

    public function tearDown(): void
    {
        $this->theFixture->uninstallLogTables();
        $this->theFixture->tearDownLocation();
        $this->removeArchiveInvalidationOptions();
        ini_set('date.timezone', $this->originalTimezone);
        $this->theFixture->trackingTime = $this->originalTrackingTime;
    }

    public function testDeleteDataSubjectsWithoutInvalidatingArchivesDeleteVisitsWithoutIdsite()
    {
        $this->theFixture->setUpWebsites();
        $this->theFixture->trackVisits($idSite = 1, 1);

        $this->assertNotEmpty($this->getVisit(1, 1));
        $this->assertNotEmpty($this->getLinkAction(1, 1));
        $this->assertNotEmpty($this->getConversion(1, 1));
        $this->assertNotEmpty($this->getOneVisit(1, 1, TestLogFoo::TABLE));

        $this->assertNotEmpty($this->getVisit(1, 3));
        $this->assertNotEmpty($this->getLinkAction(1, 3));
        $this->assertNotEmpty($this->getConversion(1, 3));

        $this->assertNotEmpty($this->getVisit(1, 2));
        $this->assertNotEmpty($this->getLinkAction(1, 2));
        $this->assertNotEmpty($this->getOneVisit(1, 2, TestLogFoo::TABLE));

        $visits = array(array('idvisit' => 1),array('idvisit' => 3),array('idvisit' => 999));
        $result = $this->dataSubjects->deleteDataSubjectsWithoutInvalidatingArchives($visits);

        $this->assertEquals(array(
            'log_conversion' => 2,
            'log_conversion_item' => 0,
            'log_link_visit_action' => 12,
            'log_visit' => 2,
            'log_foo_bar_baz' => 2,
            'log_foo_bar' => 2,
            'log_foo' => 2
        ), $result);

        $this->assertEmpty($this->getVisit(1, 1));
        $this->assertEmpty($this->getLinkAction(1, 1));
        $this->assertEmpty($this->getConversion(1, 1));
        $this->assertEmpty($this->getOneVisit(1, 1, TestLogFoo::TABLE));

        $this->assertEmpty($this->getVisit(1, 3));
        $this->assertEmpty($this->getLinkAction(1, 3));
        $this->assertEmpty($this->getConversion(1, 3));
        $this->assertEmpty($this->getOneVisit(1, 3, TestLogFoo::TABLE));

        // idvisit 2 still exists
        $this->assertNotEmpty($this->getVisit(1, 2));
        $this->assertNotEmpty($this->getLinkAction(1, 2));
        $this->assertNotEmpty($this->getOneVisit(1, 2, TestLogFoo::TABLE));
    }

    public function testDeleteDataSubjectsWithoutInvalidatingArchivesDeleteVisitWithAndWithoutIdSite()
    {
        $this->theFixture->setUpWebsites();
        $this->theFixture->trackVisits($idSite = 1, 1);

        $this->assertNotEmpty($this->getVisit(1, 1));
        $this->assertNotEmpty($this->getLinkAction(1, 1));
        $this->assertNotEmpty($this->getConversion(1, 1));
        $this->assertNotEmpty($this->getOneVisit(1, 1, TestLogFoo::TABLE));

        $this->assertNotEmpty($this->getVisit(1, 3));
        $this->assertNotEmpty($this->getLinkAction(1, 3));
        $this->assertNotEmpty($this->getConversion(1, 3));

        $this->assertNotEmpty($this->getVisit(1, 2));
        $this->assertNotEmpty($this->getLinkAction(1, 2));
        $this->assertNotEmpty($this->getOneVisit(1, 2, TestLogFoo::TABLE));

        $visits = array(array('idvisit' => 1),array('idvisit' => 3, 'idsite' => 1),array('idvisit' => 999),array('idvisit' => 999, 'idsite' => 999));
        $result = $this->dataSubjects->deleteDataSubjectsWithoutInvalidatingArchives($visits);

        $this->assertEquals(array(
            'log_conversion' => 2,
            'log_conversion_item' => 0,
            'log_link_visit_action' => 12,
            'log_visit' => 2,
            'log_foo_bar_baz' => 2,
            'log_foo_bar' => 2,
            'log_foo' => 2
        ), $result);

        $this->assertEmpty($this->getVisit(1, 1));
        $this->assertEmpty($this->getLinkAction(1, 1));
        $this->assertEmpty($this->getConversion(1, 1));
        $this->assertEmpty($this->getOneVisit(1, 1, TestLogFoo::TABLE));

        $this->assertEmpty($this->getVisit(1, 3));
        $this->assertEmpty($this->getLinkAction(1, 3));
        $this->assertEmpty($this->getConversion(1, 3));

        // idvisit 2 still exists
        $this->assertNotEmpty($this->getVisit(1, 2));
        $this->assertNotEmpty($this->getLinkAction(1, 2));
        $this->assertNotEmpty($this->getOneVisit(1, 2, TestLogFoo::TABLE));
    }

    public function testDeleteExportDeleteOneVisit()
    {
        $this->theFixture->setUpWebsites();
        $this->theFixture->trackVisits($idSite = 1, 1);

        $this->assertNotEmpty($this->getVisit(1, 1));
        $this->assertNotEmpty($this->getLinkAction(1, 1));
        $this->assertNotEmpty($this->getConversion(1, 1));
        $this->assertNotEmpty($this->getOneVisit(1, 1, TestLogFoo::TABLE));

        $this->assertNotEmpty($this->getVisit(1, 2));
        $this->assertNotEmpty($this->getLinkAction(1, 2));
        $this->assertNotEmpty($this->getOneVisit(1, 2, TestLogFoo::TABLE));

        $result = $this->dataSubjects->deleteDataSubjects(array(array('idsite' => '1', 'idvisit' => 1)));
        $this->assertEquals(array(
            'log_conversion' => 1,
            'log_conversion_item' => 0,
            'log_link_visit_action' => 11,
            'log_visit' => 1,
            'log_foo_bar_baz' => 2,
            'log_foo_bar' => 2,
            'log_foo' => 2
        ), $result);

        $this->assertEmpty($this->getVisit(1, 1));
        $this->assertEmpty($this->getLinkAction(1, 1));
        $this->assertEmpty($this->getConversion(1, 1));
        $this->assertEmpty($this->getOneVisit(1, 1, TestLogFoo::TABLE));

        // idvisit 2 still exists
        $this->assertNotEmpty($this->getVisit(1, 2));
        $this->assertNotEmpty($this->getLinkAction(1, 2));
        $this->assertNotEmpty($this->getOneVisit(1, 2, TestLogFoo::TABLE));
    }

    public function testDeleteExportDeleteAllVisits()
    {
        $this->theFixture->setUpWebsites();
        $this->theFixture->trackVisits($idSite = 1, 1);
        $this->theFixture->trackVisits($idSite = 2, 1);

        $this->assertNotEmpty($this->getAllVisits());
        $this->assertNotEmpty($this->getAllLinkActions());
        $this->assertNotEmpty($this->getAllConversions());
        $this->assertNotEmpty($this->getAllConversionItems());
        $this->assertNotEmpty($this->getAllLogFoo());
        $this->assertNotEmpty($this->getAllLogFooBar());
        $this->assertNotEmpty($this->getAllLogFooBarBaz());

        $visits = $this->getAllVisits();
        $result = $this->dataSubjects->deleteDataSubjects($visits);
        $this->assertEquals(array(
            'log_conversion' => 32,
            'log_conversion_item' => 8,
            'log_link_visit_action' => 232,
            'log_visit' => 64,
            'log_foo_bar_baz' => 2,
            'log_foo_bar' => 3,
            'log_foo' => 3
        ), $result);

        $this->assertSame(array(), $this->getAllVisits());
        $this->assertSame(array(), $this->getAllLinkActions());
        $this->assertSame(array(), $this->getAllConversions());
        $this->assertSame(array(), $this->getAllConversionItems());
        $this->assertSame(array(), $this->getAllLogFoo());
        $this->assertSame(array(), $this->getAllLogFooBar());
        $this->assertSame(array(), $this->getAllLogFooBarBaz());
    }

    public function testDeleteDataSubjectsForDeletedSitesRemovesLogDataThatDoesNotBelongToASite()
    {
        $this->theFixture->setUpWebsites();
        $this->theFixture->trackVisits($idSite = 1, 1);

        $this->theFixture->trackVisits($idSite = 2, 1);
        $this->theFixture->insertOtherLogTableData(2);

        $this->theFixture->trackVisits($idSite = 3, 1);
        $this->theFixture->insertOtherLogTableData(3);

        $this->theFixture->trackVisits($idSite = 4, 1);
        $this->theFixture->insertOtherLogTableData(4);

        // delete idSite = 2 & idSite = 3
        SitesManagerAPI::getInstance()->deleteSite(2);
        SitesManagerAPI::getInstance()->deleteSite(3);

        // assert visits still exist
        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 1));
        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 2));
        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 3));
        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 4));

        // purge
        $result = $this->dataSubjects->deleteDataSubjectsForDeletedSites(SitesManagerAPI::getInstance()->getAllSitesId());
        $this->assertEquals([
            'log_visit' => 64,
            'log_link_visit_action' => 232,
            'log_foo_bar_baz' => 4,
            'log_foo_bar' => 6,
            'log_foo' => 6,
            'log_conversion_item' => 8,
            'log_conversion' => 32,
        ], $result);

        // assert new counts
        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 1));
        $this->assertGreaterThan(0, $this->getLinkActionsCountForSite($idSite = 1));
        $this->assertGreaterThan(0, $this->getConversionsCountForSite($idSite = 1));
        $this->assertGreaterThan(0, $this->getConversionItemsCountForSite($idSite = 1));
        $this->assertGreaterThan(0, $this->getLogFooCountForSite($idSite = 1));

        $this->assertEquals(0, $this->getVisitCountForSite($idSite = 2));
        $this->assertEquals(0, $this->getLinkActionsCountForSite($idSite = 2));
        $this->assertEquals(0, $this->getConversionsCountForSite($idSite = 2));
        $this->assertEquals(0, $this->getConversionItemsCountForSite($idSite = 2));
        $this->assertEquals(0, $this->getLogFooCountForSite($idSite = 2));

        $this->assertEquals(0, $this->getVisitCountForSite($idSite = 3));
        $this->assertEquals(0, $this->getLinkActionsCountForSite($idSite = 3));
        $this->assertEquals(0, $this->getConversionsCountForSite($idSite = 3));
        $this->assertEquals(0, $this->getConversionItemsCountForSite($idSite = 3));
        $this->assertEquals(0, $this->getLogFooCountForSite($idSite = 3));

        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 4));
        $this->assertGreaterThan(0, $this->getLinkActionsCountForSite($idSite = 4));
        $this->assertGreaterThan(0, $this->getConversionsCountForSite($idSite = 4));
        $this->assertGreaterThan(0, $this->getConversionItemsCountForSite($idSite = 4));
        $this->assertGreaterThan(0, $this->getLogFooCountForSite($idSite = 4));

        // assert foo bar + foo bar baz (these tables don't have idsite columns)
        $this->assertEquals([
            ['idlogfoobarbaz' => 51, 'idlogfoobar' => 35],
            ['idlogfoobarbaz' => 52, 'idlogfoobar' => 36],
            ['idlogfoobarbaz' => 351, 'idlogfoobar' => 335],
            ['idlogfoobarbaz' => 352, 'idlogfoobar' => 336],
        ], $this->getAllLogFooBar());
        $this->assertEquals([
            ['idlogfoobar' => 35, 'idlogfoo' => 10],
            ['idlogfoobar' => 36, 'idlogfoo' => 10],
            ['idlogfoobar' => 37, 'idlogfoo' => 22],
            ['idlogfoobar' => 335, 'idlogfoo' => 310],
            ['idlogfoobar' => 336, 'idlogfoo' => 310],
            ['idlogfoobar' => 337, 'idlogfoo' => 322],
        ], $this->getAllLogFooBarBaz());
    }

    public function testDeleteDataSubjectsForDeletedSitesIgnoresSitesNewerThanThoseInList()
    {
        $this->theFixture->setUpWebsites();
        $this->theFixture->trackVisits($idSite = 1, 1);

        $this->theFixture->trackVisits($idSite = 2, 1);
        $this->theFixture->insertOtherLogTableData(2);

        $this->theFixture->trackVisits($idSite = 3, 1);
        $this->theFixture->insertOtherLogTableData(3);

        $this->theFixture->trackVisits($idSite = 4, 1);
        $this->theFixture->insertOtherLogTableData(4);

        // delete idSite = 1
        SitesManagerAPI::getInstance()->deleteSite(1);

        // assert visits still exist
        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 1));
        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 2));
        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 3));
        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 4));

        // purge
        $result = $this->dataSubjects->deleteDataSubjectsForDeletedSites([2, 3]); // pretending 4 is added while purge is running
        $this->assertEquals([
            'log_visit' => 32,
            'log_link_visit_action' => 116,
            'log_foo_bar_baz' => 2,
            'log_foo_bar' => 3,
            'log_foo' => 3,
            'log_conversion_item' => 4,
            'log_conversion' => 16,
        ], $result);

        // assert new counts
        $this->assertEquals(0, $this->getVisitCountForSite($idSite = 1));
        $this->assertEquals(0, $this->getLinkActionsCountForSite($idSite = 1));
        $this->assertEquals(0, $this->getConversionsCountForSite($idSite = 1));
        $this->assertEquals(0, $this->getConversionItemsCountForSite($idSite = 1));
        $this->assertEquals(0, $this->getLogFooCountForSite($idSite = 1));

        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 2));
        $this->assertGreaterThan(0, $this->getLinkActionsCountForSite($idSite = 2));
        $this->assertGreaterThan(0, $this->getConversionsCountForSite($idSite = 2));
        $this->assertGreaterThan(0, $this->getConversionItemsCountForSite($idSite = 2));
        $this->assertGreaterThan(0, $this->getLogFooCountForSite($idSite = 2));

        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 3));
        $this->assertGreaterThan(0, $this->getLinkActionsCountForSite($idSite = 3));
        $this->assertGreaterThan(0, $this->getConversionsCountForSite($idSite = 3));
        $this->assertGreaterThan(0, $this->getConversionItemsCountForSite($idSite = 3));
        $this->assertGreaterThan(0, $this->getLogFooCountForSite($idSite = 3));

        $this->assertGreaterThan(0, $this->getVisitCountForSite($idSite = 4));
        $this->assertGreaterThan(0, $this->getLinkActionsCountForSite($idSite = 4));
        $this->assertGreaterThan(0, $this->getConversionsCountForSite($idSite = 4));
        $this->assertGreaterThan(0, $this->getConversionItemsCountForSite($idSite = 4));
        $this->assertGreaterThan(0, $this->getLogFooCountForSite($idSite = 4));

        // assert foo bar + foo bar baz (these tables don't have idsite columns)
        $this->assertEquals([
            ['idlogfoobarbaz' => 151, 'idlogfoobar' => 135],
            ['idlogfoobarbaz' => 152, 'idlogfoobar' => 136],
            ['idlogfoobarbaz' => 251, 'idlogfoobar' => 235],
            ['idlogfoobarbaz' => 252, 'idlogfoobar' => 236],
            ['idlogfoobarbaz' => 351, 'idlogfoobar' => 335],
            ['idlogfoobarbaz' => 352, 'idlogfoobar' => 336],
        ], $this->getAllLogFooBar());
        $this->assertEquals([
            ['idlogfoobar' => 135, 'idlogfoo' => 110],
            ['idlogfoobar' => 136, 'idlogfoo' => 110],
            ['idlogfoobar' => 137, 'idlogfoo' => 122],
            ['idlogfoobar' => 235, 'idlogfoo' => 210],
            ['idlogfoobar' => 236, 'idlogfoo' => 210],
            ['idlogfoobar' => 237, 'idlogfoo' => 222],
            ['idlogfoobar' => 335, 'idlogfoo' => 310],
            ['idlogfoobar' => 336, 'idlogfoo' => 310],
            ['idlogfoobar' => 337, 'idlogfoo' => 322],
        ], $this->getAllLogFooBarBaz());
    }

    public function testDeleteOneVisitDoesInvalidateArchive()
    {
        $this->theFixture->setUpWebsites();
        $this->theFixture->trackVisits($idSite = 1, 2);
        $this->theFixture->insertArchiveRows($idSite, 2);
        $this->removeArchiveInvalidationOptions();

        $visitDate = Date::factory($this->theFixture->dateTime);
        $secondVisitDate = $visitDate->addDay(3);

        $this->assertArchivesHaveNotBeenInvalidated($visitDate, $idSite);

        $this->dataSubjects->deleteDataSubjects(array(array('idsite' => '1', 'idvisit' => 1)));

        $this->assertArchivesHaveBeenInvalidated($visitDate, $idSite);
        $this->assertArchivesHaveNotBeenInvalidated($secondVisitDate, $idSite);
    }

    public function testDeleteTwoVisitsDoesInvalidateArchive()
    {
        $this->theFixture->setUpWebsites();
        $this->theFixture->trackVisits($idSite = 1, 2);
        $this->theFixture->insertArchiveRows($idSite, 2);
        $this->removeArchiveInvalidationOptions();

        $this->dataSubjects->deleteDataSubjects(array(
            array('idsite' => '1', 'idvisit' => 1),
            array('idsite' => '1', 'idvisit' => 1 + $this->theFixture->numVisitsPerIteration),
        ));

        $visitDate = Date::factory($this->theFixture->dateTime);
        $secondVisitDate = $visitDate->addDay(3);

        $this->assertArchivesHaveBeenInvalidated($visitDate, $idSite);
        $this->assertArchivesHaveBeenInvalidated($secondVisitDate, $idSite);
    }

    public function testDeleteVisitsForMultipleSitesDoesInvalidateArchive()
    {
        $this->theFixture->setUpWebsites();
        for ($idSite = 1; $idSite <= 2; $idSite++) {
            $this->theFixture->trackingTime = $this->originalTrackingTime;
            $this->theFixture->trackVisits($idSite, 2);
            $this->theFixture->insertArchiveRows($idSite, 2);
        }
        $this->removeArchiveInvalidationOptions();

        $this->dataSubjects->deleteDataSubjects(array(
            array('idsite' => '1', 'idvisit' => 1 + $this->theFixture->numVisitsPerIteration),
            array('idsite' => '2', 'idvisit' => 1 + (2 * $this->theFixture->numVisitsPerIteration)),
        ));

        $visitDate = Date::factory($this->theFixture->dateTime);
        $secondVisitDate = $visitDate->addDay(3);

        $this->assertArchivesHaveNotBeenInvalidated($visitDate, 1);
        $this->assertArchivesHaveBeenInvalidated($visitDate, 2);
        $this->assertArchivesHaveBeenInvalidated($secondVisitDate, 1);
        $this->assertArchivesHaveNotBeenInvalidated($secondVisitDate, 2);
    }

    public function testDeleteOneVisitAlreadyMarkedForInvalidation()
    {
        $this->theFixture->setUpWebsites();
        $this->theFixture->trackVisits($idSite = 1, 2);
        $this->theFixture->insertArchiveRows($idSite, 2);
        $this->removeArchiveInvalidationOptions();

        $visitDate = Date::factory($this->theFixture->dateTime);
        $key = '4444_report_to_invalidate_' . $idSite . '_' . $visitDate->toString('Y-m-d') . '_12345';
        Option::set($key, '1');

        $this->assertArchivesHaveBeenInvalidated($visitDate, $idSite);

        $this->dataSubjects->deleteDataSubjects(array(array('idsite' => '1', 'idvisit' => 1)));

        $this->assertArchivesHaveBeenInvalidated($visitDate, $idSite);
    }

    public function testDeleteOneVisitSiteInDifferentTimezone()
    {
        ini_set('date.timezone', 'UTC');
        $websiteTimezone = 'UTC+5';

        // It's 2 January in UTC but 3 January in UTC+5
        $testTime = '2017-01-02 23:00:00';
        $this->theFixture->trackingTime = Date::factory($testTime)->getDatetime();
        $this->theFixture->setUpWebsites();
        $this->setWebsiteTimezone($idSite = 1, $websiteTimezone);
        $this->theFixture->trackVisits($idSite, 1);
        $this->theFixture->insertArchiveRows($idSite, 1);
        $this->removeArchiveInvalidationOptions();

        $visitDate = Date::factory($testTime, $websiteTimezone);

        $this->assertArchivesHaveNotBeenInvalidated($visitDate, $idSite);

        $this->dataSubjects->deleteDataSubjects(array(array('idsite' => '1', 'idvisit' => 1)));

        $this->assertArchivesHaveBeenInvalidated($visitDate, $idSite);
    }

    private function assertArchivesHaveNotBeenInvalidated(Date $visitDate, $idSite)
    {
        $key = 'report_to_invalidate_' . $idSite . '_' . $visitDate->toString('Y-m-d');
        $value = Option::getLike('%' . $key . '%');
        $this->assertEmpty($value);
    }

    private function assertArchivesHaveBeenInvalidated(Date $visitDate, $idSite)
    {
        $key = 'report_to_invalidate_' . $idSite . '_' . $visitDate->toString('Y-m-d');
        $value = Option::getLike('%' . $key . '%');
        $this->assertNotEmpty($value);
        $this->assertEquals('1', array_values($value)[0]);
    }

    private function getFromTable($table)
    {
        $rows = Db::fetchAll('SELECT * from ' . Common::prefixTable($table));
        return $rows;
    }

    private function getAllVisits()
    {
        return $this->getFromTable('log_visit');
    }

    private function getAllLinkActions()
    {
        return $this->getFromTable('log_link_visit_action');
    }

    private function getAllConversions()
    {
        return $this->getFromTable('log_conversion');
    }

    private function getAllConversionItems()
    {
        return $this->getFromTable('log_conversion_item');
    }

    private function getAllLogFooBar()
    {
        return $this->getFromTable(TestLogFooBarBaz::TABLE);
    }

    private function getAllLogFooBarBaz()
    {
        return $this->getFromTable(TestLogFooBar::TABLE);
    }

    private function getAllLogFoo()
    {
        return $this->getFromTable(TestLogFoo::TABLE);
    }

    private function getOneVisit($idSite, $idVisit, $tableName)
    {
        $rows = Db::fetchAll('SELECT idsite, idvisit from ' . Common::prefixTable($tableName) . ' WHERE idsite = ? and idvisit = ?', array($idSite, $idVisit));
        return $rows;
    }

    private function getVisit($idSite, $idVisit)
    {
        return $this->getOneVisit($idSite, $idVisit, 'log_visit');
    }

    private function getConversion($idSite, $idVisit)
    {
        return $this->getOneVisit($idSite, $idVisit, 'log_conversion');
    }

    private function getLinkAction($idSite, $idVisit)
    {
        return $this->getOneVisit($idSite, $idVisit, 'log_link_visit_action');
    }

    private function getVisitCountForSite($idSite)
    {
        return $this->getCountForSite('log_visit', $idSite);
    }

    private function getLinkActionsCountForSite($idSite)
    {
        return $this->getCountForSite('log_link_visit_action', $idSite);
    }

    private function getConversionsCountForSite($idSite)
    {
        return $this->getCountForSite('log_conversion', $idSite);
    }

    private function getConversionItemsCountForSite($idSite)
    {
        return $this->getCountForSite('log_conversion_item', $idSite);
    }

    private function getLogFooCountForSite($idSite)
    {
        return $this->getCountForSite('log_foo', $idSite);
    }

    private function getCountForSite($table, $idSite)
    {
        return Db::fetchOne("SELECT COUNT(*) FROM `" . Common::prefixTable($table) . "` WHERE idsite = ?", [$idSite]);
    }

    private function removeArchiveInvalidationOptions()
    {
        Option::deleteLike('%report_to_invalidate_%');
    }

    private function setWebsiteTimezone($idSite, $timezone)
    {
        $sql = 'UPDATE ' . Common::prefixTable('site') . ' SET timezone = ? WHERE idsite = ?';
        $bind = array($timezone, $idSite);
        Db::query($sql, $bind);
    }
}
