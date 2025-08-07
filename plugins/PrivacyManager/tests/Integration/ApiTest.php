<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Piwik\Plugins\PrivacyManager\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group PrivacyManager
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

    public function testSetComplianceStatusReturnsFalseIfFeatureFlagDisabled(): void
    {
        $this->api->setComplianceStatus(123);
    }

    public function testSetComplianceStatusReturnsFalseIfUserDoesntHaveSuperAdmin(): void
    {

    }

    public function testSetComplianceStatusReturnsTheNewStateIfEnabled(): void
    {

    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
