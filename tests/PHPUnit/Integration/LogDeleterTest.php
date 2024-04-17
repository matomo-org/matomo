<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\DataAccess\RawLogDao;
use Piwik\Db;
use Piwik\LogDeleter;
use Piwik\Tests\Framework\Mock\Plugin\LogTablesProvider;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TestDataHelper\LogHelper;

/**
 * @group Core
 */
class LogDeleterTest extends IntegrationTestCase
{
    /**
     * @var LogHelper
     */
    private $logInserter;

    /**
     * @var LogDeleter
     */
    private $logDeleter;

    public function setUp(): void
    {
        parent::setUp();

        $this->logDeleter = new LogDeleter(new RawLogDao(), new LogTablesProvider());

        $this->logInserter = new LogHelper();
        $this->insertTestData();
    }

    public function test_deleteVisits_RemovesVisitsAndOtherRelatedLogs()
    {
        $this->logDeleter->deleteVisits(array(2, 3));

        $this->assertVisitExists(1);
        $this->assertVisitNotExists(2);
        $this->assertVisitNotExists(3);
        $this->assertVisitExists(4);
    }

    public function test_deleteVisitsFor_DeletesVisitsForSpecifiedRangeAndSites_AndInvokesCallbackAfterEveryChunkIsDeleted()
    {
        $iterationCount = 0;
        $this->logDeleter->deleteVisitsFor('2012-01-01', '2012-01-02 05:05:05', 2, $iterationStep = 1, function () use (&$iterationCount) {
            ++$iterationCount;
        });

        $this->assertEquals(2, $iterationCount);

        // visits for idSite = 1 do not get deleted
        $this->assertVisitExists(1);
        $this->assertVisitExists(2);

        // visits for idSite = 2 do get deleted
        $this->assertVisitNotExists(3);
        $this->assertVisitNotExists(4);
    }

    private function insertTestData()
    {
        // two visits for site = 1
        $this->insertVisit($idSite = 1, $dateTime = '2012-01-01 00:00:00');
        $this->insertVisit($idSite = 1, $dateTime = '2012-01-02 00:00:00');

        // two visits for site = 2
        $this->insertVisit($idSite = 2, $dateTime = '2012-01-01 00:00:00');
        $this->insertVisit($idSite = 2, $dateTime = '2012-01-02 00:00:00');
    }

    private function insertVisit($idSite, $dateTime)
    {
        $visit = $this->logInserter->insertVisit(array('idsite' => $idSite, 'visit_last_action_time' => $dateTime));

        $orderId = 'idorder_' . $visit['idvisit'];

        // insert two actions
        $this->logInserter->insertVisitAction($visit['idvisit'], array('idsite' => $idSite));
        $this->logInserter->insertVisitAction($visit['idvisit'], array('idsite' => $idSite));

        // insert two conversions
        $this->logInserter->insertConversion($visit['idvisit'], array('idsite' => $idSite, 'buster' => 1));
        $this->logInserter->insertConversion($visit['idvisit'], array('idsite' => $idSite, 'buster' => 2, 'idorder' => $orderId));

        // insert two conversion items for last conversion
        $this->logInserter->insertConversionItem($visit['idvisit'], $orderId, array('idsite' => $idSite));
        $this->logInserter->insertConversionItem($visit['idvisit'], $orderId, array('idsite' => $idSite, 'idaction_sku' => 123));
    }

    private function assertVisitExists($idVisit, $checkAggregates = true)
    {
        $this->assertEquals(1, $this->getRowCountWithIdVisit('log_visit', $idVisit));
        $this->assertEquals(2, $this->getRowCountWithIdVisit('log_link_visit_action', $idVisit));

        if ($checkAggregates) {
            $this->assertConversionsExists($idVisit);
        }
    }

    private function assertConversionsExists($idVisit, $checkAggregates = true)
    {
        $this->assertEquals(2, $this->getRowCountWithIdVisit('log_conversion', $idVisit));

        if ($checkAggregates) {
            $this->assertConversionItemsExist($idVisit);
        }
    }

    private function assertConversionItemsExist($idVisit)
    {
        $this->assertEquals(2, $this->getRowCountWithIdVisit('log_conversion_item', $idVisit));
    }

    private function assertVisitNotExists($idVisit)
    {
        $this->assertEquals(0, $this->getRowCountWithIdVisit('log_visit', $idVisit));
        $this->assertEquals(0, $this->getRowCountWithIdVisit('log_link_visit_action', $idVisit));

        $this->assertConversionsNotExists($idVisit);
    }

    private function getRowCountWithIdVisit($table, $idVisit)
    {
        return Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable($table) . " WHERE idvisit = $idVisit");
    }

    private function assertConversionsNotExists($idVisit)
    {
        $this->assertEquals(0, $this->getRowCountWithIdVisit('log_conversion', $idVisit));

        $this->assertConversionItemsNotExist($idVisit);
    }

    private function assertConversionItemsNotExist($idVisit)
    {
        $this->assertEquals(0, $this->getRowCountWithIdVisit('log_conversion_item', $idVisit));
    }
}
