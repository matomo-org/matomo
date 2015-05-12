<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\DataAccess\RawLogDao;
use Piwik\LogDeleter;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TestDataHelper\LogInserter;

/**
 * @group Core
 */
class LogDeleterTest extends IntegrationTestCase
{
    /**
     * @var LogInserter
     */
    private $logInserter;

    /**
     * @var LogDeleter
     */
    private $logDeleter;

    public function setUp()
    {
        parent::setUp();

        $this->logDeleter = new LogDeleter(new RawLogDao());

        $this->logInserter = new LogInserter();
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

    public function test_deleteConversions_RemovesConversionsAndConversionItems()
    {
        $this->logDeleter->deleteConversions(array(2, 3));

        $this->assertConversionNotExists(2);
        $this->assertConversionNotExists(3);

        $this->assertVisitExists(1);
        $this->assertVisitExists(2, $checkAggregates = false);
        $this->assertVisitExists(3, $checkAggregates = false);
        $this->assertVisitExists(4);
    }

    public function test_deleteConversionItems_RemovesConversionItems()
    {
        $this->logDeleter->deleteConversionItems(array(2, 3));

        $this->assertConversionItemNotExists(2);
        $this->assertConversionItemNotExists(3);

        $this->assertConversionExists(2, $checkAggregates = false);
        $this->assertConversionExists(3, $checkAggregates = false);

        $this->assertVisitExists(1);
        $this->assertVisitExists(2, $checkAggregates = false);
        $this->assertVisitExists(3, $checkAggregates = false);
        $this->assertVisitExists(4);
    }

    public function test_deleteVisitsFor_DeletesVisitsForSpecifiedRangeAndSites_AndInvokesCallbackAfterEveryChunkIsDeleted()
    {
        // TODO
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

    }
}