<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

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
        FakeAccess::$superUser = true;
        FakeAccess::$identity = 'superUserLogin';

        self::assertSame(['notifyOnPluginRequest'], $this->getMarketplaceSettingNames());
    }

    public function testOtherUsersSeeOnlyTheDecisionSetting()
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'viewUser';
        FakeAccess::$idSitesView = [1];

        self::assertSame(['notifyOnPluginRequestDecision'], $this->getMarketplaceSettingNames());
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
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
