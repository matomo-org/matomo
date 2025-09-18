<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Piwik\Access;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\NoAccessException;
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

    /**
     * @var int
     */
    private $siteId;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->siteId = Fixture::createWebsite('2014-01-01 01:02:03');
        $this->api = API::getInstance();
    }

    public function testSetComplianceStatusThrowsExceptionIfFeatureFlagDisabled(): void
    {
        $container = StaticContainer::getContainer();
        $container->get(Config::class)->FeatureFlags = ['PrivacyCompliance_feature' => 'disabled'];

        $this->expectExceptionMessage('Feature not available');
        $this->api->setComplianceStatus(
            (string) $this->siteId,
            'cnil',
            true
        );
    }

    public function testSetComplianceStatusThrowsExceptionIfInvalidComplianceType(): void
    {
        $container = StaticContainer::getContainer();
        $container->get(Config::class)->FeatureFlags = ['PrivacyCompliance_feature' => 'enabled'];

        $this->expectExceptionMessage('Invalid compliance type');
        $this->api->setComplianceStatus(
            (string) $this->siteId,
            'egg',
            true
        );
    }

    public function testSetComplianceStatusThrowsExceptionIfUserDoesntHaveSuperAdmin(): void
    {
        $container = StaticContainer::getContainer();
        $container->get(Config::class)->FeatureFlags = ['PrivacyCompliance_feature' => 'enabled'];

        $fakeAccess = $container->get(Access::class);
        $fakeAccess->setSuperUserAccess(false);

        $this->expectException(NoAccessException::class);

        $this->api->setComplianceStatus(
            (string) $this->siteId,
            'cnil',
            true
        );
    }

    public function testSetComplianceStatusReturnsTheNewStateIfEnabled(): void
    {
        $container = StaticContainer::getContainer();
        $container->get(Config::class)->FeatureFlags = ['PrivacyCompliance_feature' => 'enabled'];

        $result = $this->api->setComplianceStatus(
            (string) $this->siteId,
            'cnil',
            true
        );

        $this->assertTrue($result);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }
}
