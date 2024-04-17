<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration\Column;

use Piwik\Cache;
use Piwik\Metrics;
use Piwik\Plugins\CoreHome\Columns\UserId;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\DataTable;

/**
 * @group CoreHome
 * @group UserIdTest
 * @group Plugins
 * @group Column
 */
class UserIdTest extends IntegrationTestCase
{
    /**
     * @var UserId
     */
    private $userId;

    protected $date = '2014-04-04';

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        Fixture::createSuperUser();
        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->userId = new UserId();

        $this->setSuperUser();
    }

    public function test_isUsedInAtLeastOneSite_shouldReturnFalseByDefault_WhenNothingIsTracked()
    {
        $this->assertNotUsedInAtLeastOneSite($idSites = array(1), 'day', $this->date);
    }

    public function test_isUsedInAtLeastOneSite_shouldCache()
    {
        $key   = '1.month.' . $this->date;
        $cache = Cache::getTransientCache();
        $this->assertFalse($cache->contains($key));

        $this->userId->isUsedInAtLeastOneSite($idSites = array(1), 'day', $this->date);

        $this->assertTrue($cache->contains($key));
        $this->assertFalse($cache->fetch($key));
    }

    public function test_isUsedInAtLeastOneSite_shouldDetectUserIdWasUsedInAllSites_WhenOneSiteGiven()
    {
        $this->trackPageviewsWithUsers();

        $this->assertUsedInAtLeastOneSite($idSites = array(1), 'day', $this->date);
    }

    public function test_isUsedInAtLeastOneSite_shouldDetectUserIdWasUsedInAtLeastOneSite_WhenMultipleSitesGiven()
    {
        $this->trackPageviewsWithUsers();

        $this->assertUsedInAtLeastOneSite($idSites = array(1,2), 'day', $this->date);
    }

    public function test_isUsedInAtLeastOneSite_shouldDetectUserIdWasNotUsedInAtLeastOneSite_WhenMultipleSitesGiven()
    {
        $this->trackPageviewsWithoutUsers();

        $this->assertNotUsedInAtLeastOneSite($idSites = array(1,2), 'day', $this->date);
    }

    public function test_isUsedInAtLeastOneSite_shouldDetectUserIdWasNotUsed_WhenOneSiteGiven()
    {
        $this->trackPageviewsWithUsers();

        $this->assertNotUsedInAtLeastOneSite($idSites = array(2), 'day', $this->date);
    }

    public function test_isUsedInAtLeastOneSite_shouldDefaultToMonthPeriodAndDetectUserIdIsUsedAlthoughNotTodayButYesterday()
    {
        $this->trackPageviewsWithUsers();

        $this->assertUsedInAtLeastOneSite($idSites = array(1), 'day', '2014-04-03');
    }

    public function test_isUsedInAtLeastOneSite_shouldDefaultToMonthPeriodAndDetectUserIdIsUsedAlthoughNotTodayButTomorrow()
    {
        $this->trackPageviewsWithUsers();

        $this->assertUsedInAtLeastOneSite($idSites = array(1), 'day', '2014-04-05');
    }

    public function test_isUsedInAtLeastOneSite_shouldDetectItWasNotUsedInMarchAlthoughItWasUsedInApril()
    {
        $this->trackPageviewsWithUsers();

        $this->assertNotUsedInAtLeastOneSite($idSites = array(1), 'day', '2014-03-04');
    }

    public function test_isUsedInAtLeastOneSite_shouldDetectItCorrectWithRangeDates()
    {
        $this->trackPageviewsWithUsers();

        $this->assertUsedInAtLeastOneSite($idSites = array(1), 'range', '2014-04-01,2014-05-05');

        // not used in that range date
        $this->assertNotUsedInAtLeastOneSite($idSites = array(1), 'range', '2014-04-01,2014-04-03');
    }

    public function test_hasDataTableUsers_shouldReturnFalse_IfEmptyTablesAreGiven()
    {
        $this->assertNotDataTableHasUsers(new DataTable\Map());
        $this->assertNotDataTableHasUsers(new DataTable());
    }

    public function test_hasDataTableUsers_shouldHandleADataTableMap()
    {
        $map = new DataTable\Map();
        $map->addTable(new DataTable(), 'label1');
        $map->addTable(new DataTable(), 'label2');
        $map->addTable($this->getDataTableWithoutUsersColumn(), 'label3');

        $this->assertNotDataTableHasUsers($map);

        $map->addTable($this->getDataTableWithZeroUsers(), 'label4');
        $map->addTable(new DataTable(), 'label5');

        $this->assertNotDataTableHasUsers($map);

        $map->addTable($this->getDataTableWithUsers(), 'label6');

        $this->assertDataTableHasUsers($map);
    }

    public function test_hasDataTableUsers_shouldHandleADataTable()
    {
        $this->assertNotDataTableHasUsers($this->getDataTableWithoutUsersColumn());
        $this->assertNotDataTableHasUsers($this->getDataTableWithZeroUsers());
        $this->assertDataTableHasUsers($this->getDataTableWithUsers());
    }

    public function test_hasDataTableUsers_shouldBeAbleToDetectIfNbUsersMetricIdIsused()
    {
        $table = $this->getDataTableWithZeroUsers();
        $table->renameColumn('nb_users', Metrics::INDEX_NB_USERS);
        $this->assertNotDataTableHasUsers($table);

        $table = $this->getDataTableWithUsers();
        $table->renameColumn('nb_users', Metrics::INDEX_NB_USERS);
        $this->assertDataTableHasUsers($this->getDataTableWithUsers());
    }

    private function getDataTableWithoutUsersColumn()
    {
        $tableWithoutUsers = new DataTable();
        $tableWithoutUsers->addRowFromSimpleArray(array('label' => 'test', 'nb_visits' => 0));

        return $tableWithoutUsers;
    }

    private function getDataTableWithZeroUsers()
    {
        $tableWithZeroUsers = new DataTable();
        $tableWithZeroUsers->addRowFromSimpleArray(array('label' => 'test', 'nb_users' => 0));

        return $tableWithZeroUsers;
    }

    private function getDataTableWithUsers()
    {
        $tableWithUsers = new DataTable();
        $tableWithUsers->addRowFromSimpleArray(array('label' => 'test', 'nb_users' => 10));

        return $tableWithUsers;
    }

    private function assertNotDataTableHasUsers($table)
    {
        $has = $this->userId->hasDataTableUsers($table);
        $this->assertFalse($has);
    }

    private function assertDataTableHasUsers($table)
    {
        $has = $this->userId->hasDataTableUsers($table);
        $this->assertTrue($has);
    }

    private function assertUsedInAtLeastOneSite($idSites, $period, $date)
    {
        $result = $this->userId->isUsedInAtLeastOneSite($idSites, $period, $date);

        $this->assertTrue($result);
    }

    private function assertNotUsedInAtLeastOneSite($idSites, $period, $date)
    {
        $result = $this->userId->isUsedInAtLeastOneSite($idSites, $period, $date);

        $this->assertFalse($result);
    }

    private function trackPageviewsWithUsers()
    {
        $this->trackPageviewsWithDifferentUsers(array('user1', false, 'user3'));
    }

    private function trackPageviewsWithoutUsers()
    {
        $this->trackPageviewsWithDifferentUsers(array(false, false, false));
    }

    private function trackPageviewsWithDifferentUsers($userIds)
    {
        $tracker = $this->getTracker();

        foreach ($userIds as $index => $userId) {
            $tracker->setForceNewVisit();
            $this->trackPageview($tracker, $userId, '/index/' . $index . '.html');
        }
    }

    private function trackPageview(\MatomoTracker $tracker, $userId, $url = null)
    {
        if (null !== $url) {
            $tracker->setUrl('http://www.example.org' . $url);
        }

        $tracker->setUserId($userId);

        $title = $url ? : 'test';

        $tracker->doTrackPageView($title);
    }

    private function getTracker()
    {
        $tracker = Fixture::getTracker(1, $this->date . ' 00:01:01', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        return $tracker;
    }

    private function setSuperUser()
    {
        FakeAccess::$superUser = true;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
