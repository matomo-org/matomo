<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Access;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Client;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service;
use Piwik\Plugins\Marketplace\Widgets\GetNewPlugins;
use Piwik\Plugins\Marketplace\Widgets\GetPremiumFeatures;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Widget\Widget;

/**
 * @group Marketplace
 * @group WidgetsTest
 * @group Plugins
 */
class WidgetsTest extends IntegrationTestCase
{
    /**
     * @var Service
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new Service();
    }

    public function testGetPremiumFeaturesRendersOnlyTheFieldsItUses()
    {
        $this->service->returnFixture('v2.0_plugins-purchase_type-paid-access_token-consumer2_paid1.json');

        $plugins = $this->renderedPlugins(new GetPremiumFeatures(Client::build($this->service)));

        self::assertNotEmpty($plugins);

        // the widget renders these five, and the rest would be json_encoded into the page. Optional
        // ones are absent rather than nulled, so assert the set is not exceeded.
        $rendered = ['name', 'displayName', 'description', 'isBundle', 'specialOffer'];

        foreach ($plugins as $plugin) {
            self::assertSame([], array_diff(array_keys($plugin), $rendered));
            self::assertArrayHasKey('name', $plugin);
            self::assertArrayHasKey('displayName', $plugin);
            self::assertArrayHasKey('description', $plugin);
        }
    }

    public function testGetNewPluginsRendersOnlyTheFieldsItUses()
    {
        $this->service->returnFixture('v2.0_plugins.json');

        $plugins = $this->renderedPlugins(new GetNewPlugins(Client::build($this->service)));

        self::assertNotEmpty($plugins);

        foreach ($plugins as $plugin) {
            foreach (['versions', 'shop', 'support', 'authors', 'changelog', 'activity'] as $field) {
                self::assertArrayNotHasKey($field, $plugin, "$field is not rendered by this widget");
            }
        }
    }

    /**
     * Reads back the plugins the widget passed to its template, out of the rendered vue-entry.
     *
     * @return array<int, array<string, mixed>>
     */
    private function renderedPlugins(Widget $widget): array
    {
        $html = Access::doAsSuperUser(function () use ($widget) {
            return $widget->render();
        });

        preg_match('/plugins="([^"]*)"/', $html, $matches);
        self::assertNotEmpty($matches, 'the widget did not render a plugins attribute');

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);
    }
}
