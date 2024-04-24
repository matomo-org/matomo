<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\Login\API;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group API
 * @group APITest
 */
class APITest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        $this->api = API::getInstance();
    }

    public function testUnblockBruteForceIPsRequiresSuperUser()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        FakeAccess::clearAccess(false, array(1,2,3));
        $this->api->unblockBruteForceIPs();
    }

    public function testUnblockBruteForceIPsDoesNotFailWhenNothingToRemove()
    {
        self::expectNotToPerformAssertions();

        $this->api->unblockBruteForceIPs();
    }

    public function testUnblockBruteForceIPsRemovesBlockedIps()
    {
        $bruteForce = StaticContainer::get('Piwik\Plugins\Login\Security\BruteForceDetection');
        $bruteForce->addFailedAttempt('127.2.3.4');
        for ($i = 0; $i < 22; $i++) {
            $bruteForce->addFailedAttempt('127.2.3.5');
        }
        $this->assertCount(23, $bruteForce->getAll());
        $this->api->unblockBruteForceIPs();
        $this->assertCount(1, $bruteForce->getAll());
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
