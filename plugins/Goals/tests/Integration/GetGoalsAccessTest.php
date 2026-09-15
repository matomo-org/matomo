<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\NoAccessException;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * getGoals must verify view access on every call, including when the requested goals are already
 * present in the per-request cache.
 *
 * @group Goals
 * @group Plugins
 */
class GetGoalsAccessTest extends IntegrationTestCase
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

        $this->api = API::getInstance();

        FakeAccess::clearAccess(true);
        $this->idSite = Fixture::createWebsite('2014-01-01 00:00:00');
        $this->api->addGoal($this->idSite, 'secret goal', 'url', 'secret-pattern', 'contains');
    }

    public function provideContainerConfig()
    {
        return ['Piwik\Access' => new FakeAccess()];
    }

    public function testGetGoalsRequiresViewAccessEvenWhenGoalsAreAlreadyCached()
    {
        // Populate the cache while access is granted.
        FakeAccess::clearAccess(true);
        $this->api->getGoals($this->idSite);

        // A user with no access to the site must still be refused.
        FakeAccess::clearAccess(false, [], [], 'otheruser');

        $this->expectException(NoAccessException::class);
        $this->api->getGoals($this->idSite);
    }

    public function testGetGoalsAllowsUserWithViewAccessWhenGoalsAreCached()
    {
        FakeAccess::clearAccess(true);
        $this->api->getGoals($this->idSite);

        // View access to the site: the call must still succeed and return the goal.
        FakeAccess::clearAccess(false, [], [$this->idSite], 'viewer');
        $goals = $this->api->getGoals($this->idSite);

        $this->assertNotEmpty($goals);
    }
}
