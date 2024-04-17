<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomJsTracker\tests\Integration;

use Piwik\Plugins\CustomJsTracker\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomJsTracker
 * @group ApiTest
 * @group Api
 * @group Plugins
 */
class ApiTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Fixture::createWebsite('2014-01-01 01:02:03');
        $this->api = API::getInstance();
    }

    public function test_doesIncludePluginTrackersAutomatically_failsIfNotEnoughPermission()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $this->expectExceptionMessage('checkUserHasSomeAdminAccess');

        $this->setUser();
        $this->api->doesIncludePluginTrackersAutomatically();
    }

    public function test_doesIncludePluginTrackersAutomatically_failsIfNotEnoughPermissionAnonymous()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $this->expectExceptionMessage('checkUserHasSomeAdminAccess');

        $this->setAnonymousUser();
        $this->api->doesIncludePluginTrackersAutomatically();
    }

    public function test_doesIncludePluginTrackersAutomatically_returnsValueWhenEnoughPermission()
    {
        $this->assertTrue($this->api->doesIncludePluginTrackersAutomatically());
    }

    protected function setUser()
    {
        FakeAccess::clearAccess(false);
        FakeAccess::$identity = 'testUsername';
        FakeAccess::$idSitesView = array(1);
        FakeAccess::$idSitesAdmin = array();
    }

    protected function setAnonymousUser()
    {
        FakeAccess::clearAccess();
        FakeAccess::$identity = 'anonymous';
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
