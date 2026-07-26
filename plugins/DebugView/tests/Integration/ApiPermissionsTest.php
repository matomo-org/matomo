<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Integration;

use Piwik\Plugins\DebugView\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group DebugView
 * @group DebugViewApiPermissionsTest
 * @group Plugins
 */
class ApiPermissionsTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        if (class_exists('\Piwik\Plugins\TagManager\TagManager')) {
            \Piwik\Plugins\TagManager\TagManager::$enableAutoContainerCreation = false;
        }

        Fixture::createSuperUser();

        $this->idSite = Fixture::createWebsite('2020-01-01 00:00:00');
        $this->api = API::getInstance();

        $this->setSuperUser();
    }

    public function getApiMethodsToTest()
    {
        return [
            ['getRecentHits'],
        ];
    }

    /**
     * @dataProvider getApiMethodsToTest
     */
    public function testMethodsShouldFailIfNoPermissionAnonymous($method)
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $this->expectExceptionMessage('checkUserHasViewAccess');

        $this->setAnonymousUser();
        $this->api->$method($this->idSite);
    }

    /**
     * @dataProvider getApiMethodsToTest
     */
    public function testMethodsShouldFailIfUserHasNoAccessToTheSite($method)
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $this->expectExceptionMessage('checkUserHasViewAccess');

        $this->setUserWithAccessToOtherSiteOnly();
        $this->api->$method($this->idSite);
    }

    /**
     * @dataProvider getApiMethodsToTest
     */
    public function testMethodsShouldNotFailIfUserHasViewPermission($method)
    {
        $this->setUser();
        $this->api->$method($this->idSite);
        $this->assertTrue(true);
    }

    /**
     * @dataProvider getApiMethodsToTest
     */
    public function testMethodsShouldNotFailIfSuperUser($method)
    {
        $this->setSuperUser();
        $this->api->$method($this->idSite);
        $this->assertTrue(true);
    }

    public function testGetRecentHitsClampsLastMinutesInsteadOfFailing()
    {
        $this->setUser();

        // values outside 1..MAX_LAST_MINUTES are clamped, not rejected
        $this->assertArrayHasKey('hits', $this->api->getRecentHits($this->idSite, 0));
        $this->assertArrayHasKey('hits', $this->api->getRecentHits($this->idSite, 99999));
    }

    public function testGetRecentHitsTreatsNegativeCursorsAsZero()
    {
        $this->setUser();

        $result = $this->api->getRecentHits($this->idSite, 30, -10);

        $this->assertSame([], $result['hits']);
        $this->assertArrayHasKey('serverTime', $result);
    }

    protected function setSuperUser()
    {
        FakeAccess::clearAccess(true);
    }

    protected function setAnonymousUser()
    {
        FakeAccess::clearAccess();
        FakeAccess::$identity = 'anonymous';
    }

    protected function setUser()
    {
        FakeAccess::clearAccess(false);
        FakeAccess::$identity = 'testUser';
        FakeAccess::$idSitesView = [$this->idSite];
        FakeAccess::$idSitesAdmin = [];
    }

    protected function setUserWithAccessToOtherSiteOnly()
    {
        FakeAccess::clearAccess(false);
        FakeAccess::$identity = 'testUser';
        FakeAccess::$idSitesView = [$this->idSite + 999];
        FakeAccess::$idSitesAdmin = [];
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
