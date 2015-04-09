<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests;

use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UserPreferences;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Access;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UsersManager
 * @group UserPreferencesTest
 * @group Plugins
 * @group Plugins
 */
class UserPreferencesTest extends IntegrationTestCase
{
    /**
     * @var UserPreferences
     */
    private $userPreferences;

    public function setUp()
    {
        parent::setUp();

        $this->userPreferences = new UserPreferences();

        $this->setSuperUser();
    }

    public function test_getDefaultReport_ShouldReturnFalseByDefault()
    {
        $this->assertEquals(false, $this->userPreferences->getDefaultReport());
    }

    public function test_getDefaultReport_ShouldReturnTheRawValueIfNotNumeric()
    {
        $this->setDefaultReport('MultiSites');
        $this->assertEquals('MultiSites', $this->userPreferences->getDefaultReport());
    }

    public function test_getDefaultReport_ShouldNotReturnSiteIdIfNoPermissionForSite()
    {
        $this->createSite();
        $this->setDefaultReport(1);
        $this->setAnonymous();
        $this->assertEquals(false, $this->userPreferences->getDefaultReport());
    }

    public function test_getDefaultReport_ShouldReturnSiteIdIfPermissionForSite()
    {
        $this->createSite();
        $this->setDefaultReport(1);
        $this->assertEquals(1, $this->userPreferences->getDefaultReport());
    }

    public function test_getDefaultWebsiteId_ShouldReturnFalseByDefault()
    {
        $this->assertEquals(false, $this->userPreferences->getDefaultWebsiteId());
    }

    public function test_getDefaultWebsiteId_ShouldReturnASiteIfOneExistsAndHasAccess()
    {
        $this->createSite();
        $this->assertEquals(1, $this->userPreferences->getDefaultWebsiteId());
    }

    public function test_getDefaultWebsiteId_ShouldReturnFalseIfASiteExistsButHasNoAccess()
    {
        $this->createSite();
        $this->setAnonymous();
        $this->assertEquals(false, $this->userPreferences->getDefaultWebsiteId());
    }

    public function test_getDefaultWebsiteId_ShouldReturnASiteEvenIfMultiSitesIsDefaultReport()
    {
        $this->setDefaultReport('MultiSites');
        $this->createSite();
        $this->assertEquals(1, $this->userPreferences->getDefaultWebsiteId());
    }

    public function test_getDefaultPeriod_ShouldReturnDayIfToday()
    {
        $this->setDefaultDate('today');
        $this->assertEquals('day', $this->userPreferences->getDefaultPeriod());
    }

    public function test_getDefaultPeriod_ShouldReturnDayIfYesterday()
    {
        $this->setDefaultDate('yesterday');
        $this->assertEquals('day', $this->userPreferences->getDefaultPeriod());
    }

    public function test_getDefaultPeriod_ShouldOnlyReturnAllowedPeriods()
    {
        // Only allow for week period
        Config::getInstance()->General['enabled_periods_UI'] = 'week';
        Config::getInstance()->General['default_period'] = 'week';
        $this->setDefaultDate('today');
        $this->assertEquals('week', $this->userPreferences->getDefaultPeriod());
    }

    private function setSuperUser()
    {
        $pseudoMockAccess = new FakeAccess();
        FakeAccess::$superUser = true;
        Access::setSingletonInstance($pseudoMockAccess);
    }

    private function setAnonymous()
    {
        $pseudoMockAccess = new FakeAccess();
        FakeAccess::$superUser = false;
        Access::setSingletonInstance($pseudoMockAccess);
    }

    private function createSite()
    {
        Fixture::createWebsite('2013-01-23 01:23:45');
    }

    private function setDefaultReport($defaultReport)
    {
        APIUsersManager::getInstance()->setUserPreference(
            Piwik::getCurrentUserLogin(),
            APIUsersManager::PREFERENCE_DEFAULT_REPORT,
            $defaultReport
        );
    }

    private function setDefaultDate($date)
    {
        APIUsersManager::getInstance()->setUserPreference(
            Piwik::getCurrentUserLogin(),
            APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE,
            $date
        );
    }
}
