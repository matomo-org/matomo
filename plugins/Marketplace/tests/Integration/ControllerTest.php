<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Access;
use Piwik\DI;
use Piwik\FrontController;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    /**
     * @var array
     */
    private $originalGet = [];

    /**
     * @var Service
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalGet = $_GET;
    }

    public function tearDown(): void
    {
        // these tests drive controller actions through $_GET, which outlives the test otherwise
        $_GET = $this->originalGet;

        parent::tearDown();
    }

    public function testSearchPluginsReturnsExactlyTheCardFieldAllowList()
    {
        $plugins = $this->searchPlugins();

        self::assertNotEmpty($plugins);

        // asserting the exact set, not just the absence of the heavy fields: a field added to the
        // Marketplace API or to the allow list has to be a deliberate change, since anything the
        // cards do not render is paid for by every visitor. See Controller::keepPluginCardFields().
        $always = [
            'canBeUpdated',
            'canTrialBeRequested',
            'consumer',
            'coverImage',
            'description',
            'displayName',
            'hasDownloadLink',
            'hasExceededLicense',
            'isActivated',
            'isDownloadable',
            'isEligibleForFreeTrial',
            'isFree',
            'isInstalled',
            'isInvalid',
            'isMissingLicense',
            'isPaid',
            'isTrialRequested',
            'missingRequirements',
            'name',
            'numDownloads',
            'numDownloadsPretty',
            'owner',
            'priceFrom',
        ];
        // only set for a plugin that can actually be downloaded
        $conditional = ['downloadNonce'];

        foreach ($plugins as $plugin) {
            $keys = array_keys($plugin);
            sort($keys);

            self::assertSame([], array_diff($keys, array_merge($always, $conditional)), sprintf(
                'unexpected field(s) in the plugin list for %s: %s',
                $plugin['name'] ?? '?',
                implode(', ', array_diff($keys, array_merge($always, $conditional)))
            ));
            self::assertSame([], array_diff($always, $keys), sprintf(
                'missing card field(s) for %s: %s',
                $plugin['name'] ?? '?',
                implode(', ', array_diff($always, $keys))
            ));
        }
    }

    public function testGetPluginDetailsReturnsTheFieldsTheListOmits()
    {
        $plugin = $this->getPluginDetails('TreemapVisualization');

        self::assertSame('TreemapVisualization', $plugin['name']);

        foreach (['versions', 'shop', 'screenshots', 'support', 'authors', 'changelog'] as $field) {
            self::assertArrayHasKey($field, $plugin, "the modal renders $field");
        }
    }

    public function testGetPluginDetailsReturnsTheLatestVersionOnly()
    {
        $plugin = $this->getPluginDetails('TreemapVisualization');

        // only the latest version is rendered and each version carries its own readme HTML, which
        // is what made the list response large in the first place
        self::assertCount(1, $plugin['versions']);
        self::assertSame($plugin['latestVersion'], $plugin['versions'][0]['name']);
    }

    /**
     * @return array<string, mixed>
     */
    private function getPluginDetails(string $pluginName): array
    {
        $_GET['module'] = 'Marketplace';
        $_GET['action'] = 'getPluginDetails';
        $_GET['format'] = 'JSON';
        $_GET['pluginName'] = $pluginName;

        $response = Access::doAsSuperUser(function () {
            return FrontController::getInstance()->fetchDispatch('Marketplace', 'getPluginDetails');
        });

        return json_decode($response, true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function searchPlugins(): array
    {
        $_GET['module'] = 'Marketplace';
        $_GET['action'] = 'searchPlugins';
        $_GET['format'] = 'JSON';

        $response = Access::doAsSuperUser(function () {
            return FrontController::getInstance()->fetchDispatch('Marketplace', 'searchPlugins');
        });

        return json_decode($response, true);
    }

    public function provideContainerConfig()
    {
        // these actions dispatch through the container, so the Client they reach has to answer out
        // of the fixtures. The repository that backs Http::sendHttpRequest() keys on the full query
        // string, uid included, so no hand-written manifest entry can match and the request would
        // otherwise fall through to the real Marketplace.
        $this->service = new Service();
        $this->service->setOnDownloadCallback(function ($action) {
            return $this->service->getFixtureContent($this->fixtureFor($action));
        });

        return [
            'dev.forced_plugin_update_result' => [],
            'Piwik\Plugins\Marketplace\Api\Service' => DI::value($this->service),
        ];
    }

    private function fixtureFor(string $action): string
    {
        // exact matches, so a call these tests do not expect - 'plugins/checkUpdates' or a
        // per-plugin 'plugins/Foo/info' - fails loudly instead of being handed the list
        if ($action === 'themes') {
            return 'v2.0_themes.json';
        }

        if ($action === 'plugins') {
            return 'v2.0_plugins.json';
        }

        throw new \Exception(sprintf('No fixture wired up for the Marketplace action "%s"', $action));
    }
}
