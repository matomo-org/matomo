<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace PHPUnit\Integration\DataAccess;


use Piwik\Common;
use Piwik\DataAccess\RawLogDao;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class RawLogDaoTest extends IntegrationTestCase
{
    /**
     * @var RawLogDao
     */
    private $dao;

    private $idSite = 1;

    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::siteCreated($this->idSite)) {
            Fixture::createWebsite('2010-00-00 00:00:00');
        }

        $this->dao = new RawLogDao();
    }

    public function test_hasVisitDataOutOfOrder_returnsFalseWhenThereAreNoVisits()
    {
        list($result, $middleVisit, $outOfOrderVisit) = $this->dao->hasVisitDataOutOfOrder($this->idSite);
        $this->assertFalse($result);
        $this->assertNull($middleVisit);
        $this->assertNull($outOfOrderVisit);
    }

    public function test_hasVisitDataOutOfOrder_returnsFalseIfVisitDataIsNotOutOfOrder()
    {
        $table = Common::prefixTable('log_visit');
        Db::query("INSERT INTO `$table` (idsite, visit_last_action_time) VALUES (?, ?), (?, ?), (?, ?), (?, ?), (?, ?)", [
            $this->idSite, '2021-01-03 04:05:00',
            $this->idSite, '2021-02-03 06:05:00',
            $this->idSite, '2021-03-04 04:05:00',
            $this->idSite, '2021-03-04 04:05:00',
            $this->idSite, '2021-05-05 05:05:00',
        ]);

        list($result, $middleVisit, $outOfOrderVisit) = $this->dao->hasVisitDataOutOfOrder($this->idSite);
        $this->assertFalse($result);
        $this->assertNull($middleVisit);
        $this->assertNull($outOfOrderVisit);
    }

    public function test_hasVisitDataOutOfOrder_returnsTrueIfVisitDataIsOutOfOrder()
    {
        $table = Common::prefixTable('log_visit');
        Db::query("INSERT INTO `$table` (idsite, visit_last_action_time) VALUES (?, ?), (?, ?), (?, ?), (?, ?), (?, ?), (?, ?)", [
            $this->idSite, '2021-01-03 04:05:00',
            $this->idSite, '2021-06-04 04:05:00',
            $this->idSite, '2021-03-04 04:05:00',
            $this->idSite, '2021-04-05 05:05:00',
            $this->idSite, '2021-05-03 06:05:00',
            $this->idSite, '2021-06-05 06:05:00',
        ]);

        list($result, $middleVisit, $outOfOrderVisit) = $this->dao->hasVisitDataOutOfOrder($this->idSite);
        $this->assertTrue($result);
        $this->assertEquals(3, $middleVisit);
        $this->assertEquals(2, $outOfOrderVisit);
    }

    public function test_hasVisitDataOutOfOrder_returnsFalseIfVisitDataIsNotOutOfOrder_forASmallNumberOfVisits()
    {
        $table = Common::prefixTable('log_visit');
        Db::query("INSERT INTO `$table` (idsite, visit_last_action_time) VALUES (?, ?), (?, ?)", [
            $this->idSite, '2021-02-03 04:05:00',
            $this->idSite, '2021-02-05 06:05:00',
        ]);

        list($result, $middleVisit, $outOfOrderVisit) = $this->dao->hasVisitDataOutOfOrder($this->idSite);
        $this->assertFalse($result);
        $this->assertNull($middleVisit);
        $this->assertNull($outOfOrderVisit);
    }

    public function test_hasVisitDataOutOfOrder_returnsTrueIfVisitDataIsOutOfOrder_forASmallNumberOfVisits()
    {
        $table = Common::prefixTable('log_visit');
        Db::query("INSERT INTO `$table` (idsite, visit_last_action_time) VALUES (?, ?), (?, ?)", [
            $this->idSite, '2021-02-05 04:05:00',
            $this->idSite, '2021-02-03 06:05:00',
        ]);

        list($result, $middleVisit, $outOfOrderVisit) = $this->dao->hasVisitDataOutOfOrder($this->idSite);
        $this->assertTrue($result);
        $this->assertEquals(1, $middleVisit);
        $this->assertEquals(2, $outOfOrderVisit);
    }
}