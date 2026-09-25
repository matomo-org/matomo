<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\tests\Integration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\AIProviders\API;
use Piwik\Plugins\AIProviders\Menu;
use Piwik\Plugins\AIProviders\Model\AIProcessingSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group AIProviders
 * @group AIProvidersAIProcessing
 * @group Plugins
 */
class AIProcessingSettingsTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var AIProcessingSettings
     */
    private $settings;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        FakeAccess::$superUser = true;
        Manager::getInstance()->loadPluginTranslations();

        $this->api = API::getInstance();
        $this->settings = StaticContainer::get(AIProcessingSettings::class);
    }

    public function tearDown(): void
    {
        Config::getInstance()->AIProviders = [];

        parent::tearDown();
    }

    public function testAllCategoriesAreDisabledByDefault(): void
    {
        self::assertSame([
            ['id' => 'nonAnalytics', 'enabled' => false, 'usedBy' => []],
            ['id' => 'aggregatedAnalytics', 'enabled' => false, 'usedBy' => []],
        ], $this->api->getAIProcessingSettings());
        self::assertFalse($this->settings->isEnabled(AIProcessingSettings::CATEGORY_NON_ANALYTICS));
    }

    public function testSavingEnablesOnlyTheGivenCategories(): void
    {
        $result = $this->api->setAIProcessingSettings([AIProcessingSettings::CATEGORY_AGGREGATED_ANALYTICS]);

        self::assertSame([false, true], array_column($result, 'enabled'));
        self::assertTrue($this->settings->isEnabled(AIProcessingSettings::CATEGORY_AGGREGATED_ANALYTICS));
        self::assertFalse($this->settings->isEnabled(AIProcessingSettings::CATEGORY_NON_ANALYTICS));

        $result = $this->api->setAIProcessingSettings([]);

        self::assertSame([false, false], array_column($result, 'enabled'));
    }

    public function testFeaturesRegisteredByPluginsAreListedPerCategory(): void
    {
        $askMatomo = ['name' => 'Ask Matomo', 'disclosureUrl' => 'https://matomo.org/faq/ask-matomo'];
        Piwik::addAction('AIProviders.addAIProcessingFeatures', function (array &$features) use ($askMatomo): void {
            $features[AIProcessingSettings::CATEGORY_AGGREGATED_ANALYTICS][] = $askMatomo;
            $features['raw'][] = $askMatomo;
        });

        self::assertSame(
            [[], [$askMatomo]],
            array_column($this->api->getAIProcessingSettings(), 'usedBy')
        );
    }

    public function testChangeEventIsPostedOnlyWhenTheCategoriesChange(): void
    {
        $events = [];
        Piwik::addAction('AIProviders.aiProcessingSettingsChanged', function (array $enabled, array $previous) use (&$events): void {
            $events[] = [$enabled, $previous];
        });

        $this->api->setAIProcessingSettings([AIProcessingSettings::CATEGORY_AGGREGATED_ANALYTICS]);
        $this->api->setAIProcessingSettings([AIProcessingSettings::CATEGORY_AGGREGATED_ANALYTICS]);
        $this->api->setAIProcessingSettings([]);

        self::assertSame([
            [[AIProcessingSettings::CATEGORY_AGGREGATED_ANALYTICS], []],
            [[], [AIProcessingSettings::CATEGORY_AGGREGATED_ANALYTICS]],
        ], $events);
    }

    public function testUnknownCategoryIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown AI processing data category "raw"');

        $this->api->setAIProcessingSettings(['raw']);
    }

    public function testMenuEntryStaysAvailableInAManagedEnvironment(): void
    {
        Config::getInstance()->AIProviders = ['defaultProvider' => 'openai'];

        $menu = MenuAdmin::getInstance();
        StaticContainer::get(Menu::class)->configureAdminMenu($menu);

        self::assertStringContainsString('AIProviders_AIProcessing', json_encode($menu->getMenu()));
    }

    public function testNonSuperUserCannotReadOrChangeTheSettings(): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesAdmin = [1];

        try {
            $this->api->getAIProcessingSettings();
            self::fail('Expected a permission error.');
        } catch (\Exception $e) {
            self::assertStringContainsString('superuser', strtolower($e->getMessage()));
        }

        $this->expectException(\Exception::class);
        $this->api->setAIProcessingSettings([AIProcessingSettings::CATEGORY_NON_ANALYTICS]);
    }

    public function provideContainerConfig(): array
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
