<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Config\GeneralConfig;
use Piwik\Option;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class StorageTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', 1);
    }

    public function testConstructorFailsOnInvalidPlugin()
    {
        self::expectException(\Exception::class);

        $storage = new Storage('Inval$dPlü§1n');
    }

    public function testWasRequested()
    {
        $storage = new Storage('PremiumPlugin');
        self::assertFalse($storage->wasRequested());

        $storage->setRequested();
        self::assertTrue($storage->wasRequested());

        // ensure same result with new object
        $storage = new Storage('PremiumPlugin');
        self::assertTrue($storage->wasRequested());
    }

    public function testClearStorage()
    {
        // Manually create a request that is 25 hours old
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => time() - (25 * 3600),
            'displayName' => 'Premium Plugin',
            'dismissed' => [],
            'requestedBy' => 'olaf',
        ]));

        $storage = new Storage('PremiumPlugin');
        $storage->clearStorage();
        self::assertFalse(Option::get('Marketplace.PluginTrialRequest.PremiumPlugin'));
    }

    public function testWasRequestedClearsStorageWhenOutdated()
    {
        // Manually create a request that is 25 hours old
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => time() - (25 * 3600),
            'displayName' => 'Premium Plugin',
            'dismissed' => [],
            'requestedBy' => 'olaf',
        ]));

        $storage = new Storage('PremiumPlugin');
        self::assertFalse($storage->wasRequested());
        self::assertFalse(Option::get('Marketplace.PluginTrialRequest.PremiumPlugin'));
    }

    public function testDismissRequest()
    {
        $storage = new Storage('PremiumPlugin');
        $storage->setRequested();
        self::assertTrue($storage->wasRequested());
        self::assertFalse($storage->isNotificationDismissed());
        $storage->setNotificationDismissed();
        self::assertTrue($storage->isNotificationDismissed());
    }

    public function testGetPluginsInStorage()
    {
        // Manually create some requests
        Option::set('Marketplace.PluginTrialRequest.PremiumPlugin', json_encode([
            'requestTime' => time() - 10,
            'displayName' => 'A useful plugin',
            'dismissed' => [],
            'requestedBy' => 'olaf',
        ]));
        Option::set('Marketplace.PluginTrialRequest.BestPluginEver', json_encode([
            'requestTime' => time(),
            'displayName' => '',
            'dismissed' => ['admin'],
            'requestedBy' => 'peter',
        ]));

        self::assertEquals(['BestPluginEver', 'PremiumPlugin'], Storage::getPluginsInStorage());
    }
}
