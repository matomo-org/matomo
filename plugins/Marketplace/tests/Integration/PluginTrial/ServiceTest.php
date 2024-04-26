<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\Plugins;

use Piwik\Config\GeneralConfig;
use Piwik\Notification\Manager;
use Piwik\Plugins\Marketplace\PluginTrial\Service;
use Piwik\Plugins\Marketplace\PluginTrial\Storage;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class ServiceTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', 1);
        \Zend_Session::$_unitTestEnabled = true;
        Manager::cancelAllNotifications();
    }

    public function testRequestDisabled()
    {
        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', -1);

        $service = new Service();
        $service->request('PremiumPlugin', 'Pretty Premium Plugin');

        $this->assertRequested(false);
    }

    public function testRequestSucceeds()
    {
        $this->assertRequested(false);

        $service = new Service();
        $service->request('PremiumPlugin');

        $this->assertRequested(true);
    }

    public function testWasRequestedDisabled()
    {
        GeneralConfig::setConfigValue('plugin_trial_request_expiration_in_days', -1);

        $service = new Service();
        self::assertFalse($service->wasRequested('PremiumPlugin'));
    }

    public function testWasNotRequested()
    {
        $service = new Service();
        self::assertFalse($service->wasRequested('PremiumPlugin'));
    }

    public function testWasRequested()
    {
        $this->setRequested();

        $service = new Service();
        self::assertTrue($service->wasRequested('PremiumPlugin'));
    }

    public function testCancel()
    {
        $this->setRequested();

        $service = new Service();
        self::assertTrue($service->wasRequested('PremiumPlugin'));
        $service->cancelRequest('PremiumPlugin');
        self::assertFalse($service->wasRequested('PremiumPlugin'));
    }

    public function testCreateAndDismissNotifications()
    {
        $service = new Service();
        $service->request('PremiumPlugin');
        $service->request('PremiumPlugin2');

        $service->createNotificationsIfNeeded();

        $notifications = Manager::getPendingInMemoryNotifications();

        self::assertCount(2, $notifications);

        Manager::cancelAllNotifications();

        $service->dismissNotification('Marketplace_PluginTrialRequest_' . md5(FakeAccess::$superUserLogin) . '_PremiumPlugin2');
        $service->createNotificationsIfNeeded();

        $notifications = Manager::getPendingInMemoryNotifications();

        self::assertCount(1, $notifications);
    }

    protected function assertRequested(bool $expected): void
    {
        $storage = new Storage('PremiumPlugin');
        self::assertEquals($expected, $storage->wasRequested());
    }

    protected function setRequested(): void
    {
        $storage = new Storage('PremiumPlugin');
        $storage->setRequested();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
