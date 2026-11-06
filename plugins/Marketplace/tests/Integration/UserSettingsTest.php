<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Config;
use Piwik\Plugins\CorePluginsAdmin\API as CorePluginsAdminApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group Plugins
 */
class UserSettingsTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2012-01-01 00:00:00');
    }

    public function testSuperUsersSeeOnlyThePluginRequestSetting()
    {
        $this->setPluginRequestsFlag('enabled');
        FakeAccess::$superUser = true;
        FakeAccess::$identity = 'superUserLogin';

        self::assertSame(['notifyOnPluginRequest'], $this->getMarketplaceSettingNames());
    }

    public function testOtherUsersSeeOnlyTheDecisionSetting()
    {
        $this->setPluginRequestsFlag('enabled');
        $this->setViewUser();

        self::assertSame(['notifyOnPluginRequestDecision'], $this->getMarketplaceSettingNames());
    }

    public function testOtherUsersSeeNoSettingWhilePluginRequestsAreDisabled()
    {
        $this->setPluginRequestsFlag('disabled');
        $this->setViewUser();

        self::assertSame([], $this->getMarketplaceSettingNames());
    }

    private function setViewUser(): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'viewUser';
        FakeAccess::$idSitesView = [1];
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    /**
     * The local config may set the flag, so each test sets it itself
     */
    private function setPluginRequestsFlag(string $value): void
    {
        Config::getInstance()->FeatureFlags = ['PluginRequests_feature' => $value];
    }

    /**
     * @return string[] the names of the Marketplace user settings the current user can see
     */
    private function getMarketplaceSettingNames(): array
    {
        foreach (CorePluginsAdminApi::getInstance()->getUserSettings() as $pluginSettings) {
            if ($pluginSettings['pluginName'] === 'Marketplace') {
                return array_column($pluginSettings['settings'], 'name');
            }
        }

        return [];
    }
}
