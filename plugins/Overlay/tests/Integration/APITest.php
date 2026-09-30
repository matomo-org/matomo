<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Overlay\tests\Integration;

use Piwik\DataTable;
use Piwik\NoAccessException;
use Piwik\Plugins\Overlay\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Overlay
 * @group Plugins
 */
class APITest extends IntegrationTestCase
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
    }

    public function provideContainerConfig()
    {
        return ['Piwik\Access' => new FakeAccess()];
    }

    public function testGetFollowingPagesDeniesUserWithoutViewAccess()
    {
        FakeAccess::clearAccess(false, [], [], 'otheruser');

        $this->expectException(NoAccessException::class);
        $this->api->getFollowingPages('http://example.com/', $this->idSite, 'day', 'today');
    }

    public function testGetFollowingPagesAllowsUserWithViewAccess()
    {
        FakeAccess::clearAccess(false, [], [$this->idSite], 'viewer');

        $result = $this->api->getFollowingPages('http://example.com/', $this->idSite, 'day', 'today');

        $this->assertInstanceOf(DataTable::class, $result);
    }
}
