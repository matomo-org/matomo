<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\DI;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\CoreUpdater\Updater;
use Piwik\Plugins\Marketplace\Api\Service\Exception as ServiceException;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Version;

/**
 * @group CoreUpdater
 * @group Plugins
 */
class UpdaterTest extends IntegrationTestCase
{
    /**
     * @var Service
     */
    private $service;

    /**
     * @var string[] the API actions the update run asked the Marketplace for, in order
     */
    private $requestedActions = [];

    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();

        if (!PluginManager::getInstance()->isPluginActivated('Marketplace')) {
            PluginManager::getInstance()->activatePlugin('Marketplace');
        }
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testOneClickUpdatePartTwoKeepsUpdatingAfterAPluginWithoutAValidLicense()
    {
        $this->answerWith([
            'PaidPlugin1' => 'v2.0_plugins_PaidPlugin1_info.json', // isDownloadable: false
            'TreemapVisualization' => 'v2.0_plugins_TreemapVisualization_info.json',
        ]);

        $messages = $this->buildUpdater()->oneClickUpdatePartTwo(Version::VERSION);

        // the plugin without a valid license is reported and skipped ...
        self::assertContains(
            'Could not update plugin PaidPlugin1: Failed to download plugin: Plugin is not downloadable.'
            . ' License may be missing or expired.',
            $messages
        );

        // ... and the one that can be updated is still attempted
        self::assertContains('Updating plugin TreemapVisualization to version 99.0.0', $messages);
        self::assertContains('plugins/TreemapVisualization/info', $this->requestedActions);
    }

    public function testOneClickUpdatePartTwoReportsAFailedUpdateCheckInsteadOfStayingSilent()
    {
        $this->service->throwException(new ServiceException('Marketplace unavailable', ServiceException::HTTP_ERROR));

        $messages = $this->buildUpdater()->oneClickUpdatePartTwo(Version::VERSION);

        self::assertContains('Could not check for plugin updates: Marketplace unavailable', $messages);
    }

    private function answerWith(array $pluginInfoFixtures): void
    {
        $updates = [];
        foreach (array_keys($pluginInfoFixtures) as $pluginName) {
            $updates[] = ['name' => $pluginName, 'version' => '99.0.0'];
        }

        $this->service->setOnFetchCallback(function ($action) use ($updates, $pluginInfoFixtures) {
            $this->requestedActions[] = $action;

            if ($action === 'plugins/checkUpdates') {
                return $updates;
            }

            foreach ($pluginInfoFixtures as $pluginName => $fixture) {
                if ($action === sprintf('plugins/%s/info', $pluginName)) {
                    return json_decode($this->service->getFixtureContent($fixture), true);
                }
            }

            return [];
        });
    }

    private function buildUpdater(): Updater
    {
        return new Updater(
            StaticContainer::get('Piwik\Translation\Translator'),
            StaticContainer::get('Piwik\Plugin\ReleaseChannels'),
            StaticContainer::get('path.tmp')
        );
    }

    public function provideContainerConfig()
    {
        $this->service = new Service();

        return [
            'Piwik\Plugins\Marketplace\Api\Service' => DI::value($this->service),
        ];
    }
}
