<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use PHPUnit\Framework\Assert;
use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;

class NonProfilableData extends Fixture
{
    public $idSite = 1;
    public $dateTime = '2020-04-04 03:00:00';

    public function setUp(): void
    {
        $this->createTestWebsite();
        $this->trackNonProfilableVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function trackNonProfilableVisits()
    {
        // two non profilable visits?
        $t = self::getTracker($this->idSite, $this->dateTime);
        $t->setUrl('http://example.com/isapage');
        $this->unsetVisitorId($t);
        Fixture::checkResponse($t->doTrackPageView('page view'));

        // TODO Rest of visits

        $this->assertNoProfilableData();
    }

    private function createTestWebsite()
    {
        self::createWebsite('2020-03-04 03:00:00', $ecommerce = 1);
    }

    private function unsetVisitorId(\MatomoTracker $t)
    {
        $t->randomVisitorId = false;
    }

    private function assertNoProfilableData()
    {
        $table = Common::prefixTable('log_visit');
        $sql = "SELECT COUNT(*) FROM $table WHERE profilable = 1";
        $count = Db::fetchOne($sql);
        Assert::assertEquals(0, $count);
    }
}