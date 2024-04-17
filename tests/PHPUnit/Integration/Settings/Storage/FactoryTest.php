<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Storage;

use Piwik\Settings\FieldConfig;
use Piwik\Settings\Storage\Backend\Cache;
use Piwik\Settings\Storage\Backend\NullBackend;
use Piwik\Settings\Storage\Backend\SitesTable;
use Piwik\Settings\Storage\Backend\MeasurableSettingsTable;
use Piwik\Settings\Storage\Backend\PluginSettingsTable;
use Piwik\Settings\Storage\Storage;
use Piwik\Settings\Storage\Factory;
use Piwik\SettingsServer;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Tracker
 * @group Handler
 * @group Visit
 * @group Factory
 * @group FactoryTest
 */
class FactoryTest extends IntegrationTestCase
{
    /**
     * @var Factory
     */
    private $factory;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = new Factory();
    }

    public function test_getPluginStorage_shouldReturnStorageWithPluginBackend()
    {
        $storage = $this->factory->getPluginStorage('PluginName', $login = 'user5');
        $this->assertTrue($storage instanceof Storage);

        $backend = $storage->getBackend();
        $this->assertTrue($backend instanceof PluginSettingsTable);
        $this->assertSame('PluginSettings_PluginName_User_user5', $backend->getStorageId());
    }

    public function test_getPluginStorage_shouldDecorateWithCacheBackendInTrackerMode()
    {
        SettingsServer::setIsTrackerApiRequest();
        $storage = $this->factory->getPluginStorage('pluginName', 'userlogin');
        SettingsServer::setIsNotTrackerApiRequest();

        $this->assertTrue($storage->getBackend() instanceof Cache);
    }

    public function test_getMeasurableSettingsStorage_shouldReturnStorageWithMeasurableSettingsBackend()
    {
        $storage = $this->factory->getMeasurableSettingsStorage($idSite = 4, 'PluginNameFoo');
        $this->assertTrue($storage instanceof Storage);

        $backend = $storage->getBackend();
        $this->assertTrue($backend instanceof MeasurableSettingsTable);
        $this->assertSame('MeasurableSettings_4_PluginNameFoo', $backend->getStorageId());
    }

    public function test_getMeasurableSettingsStorage_shouldDecorateWithCacheBackendInTrackerMode()
    {
        SettingsServer::setIsTrackerApiRequest();
        $storage = $this->factory->getMeasurableSettingsStorage($idSite = 4, 'PluginNameFoo');
        SettingsServer::setIsNotTrackerApiRequest();

        $this->assertTrue($storage->getBackend() instanceof Cache);
    }

    public function test_getMeasurableSettingsStorage_shouldReturnNonPersistentStorageWhenEmptySiteIsGiven()
    {
        $storage = $this->factory->getMeasurableSettingsStorage($idSite = 0, 'PluginNameFoo');
        $this->assertTrue($storage instanceof Storage);

        $backend = $storage->getBackend();
        $this->assertTrue($backend instanceof NullBackend);
        $this->assertSame('measurableSettings0#PluginNameFoo#nonpersistent', $backend->getStorageId());
    }

    public function test_getSitesTable_shouldReturnStorageWithSitesTableBackend()
    {
        $storage = $this->factory->getSitesTable($idSite = 3);
        $this->assertTrue($storage instanceof Storage);

        $backend = $storage->getBackend();
        $this->assertTrue($backend instanceof SitesTable);
        $this->assertSame('SitesTable_3', $backend->getStorageId());
    }

    public function test_getSitesTable_shouldDecorateWithCacheBackendInTrackerMode()
    {
        SettingsServer::setIsTrackerApiRequest();
        $storage = $this->factory->getSitesTable($idSite = 3);
        SettingsServer::setIsNotTrackerApiRequest();

        $this->assertTrue($storage->getBackend() instanceof Cache);
    }

    public function test_getSitesTable_shouldReturnNonPersistentStorageWhenEmptySiteIsGiven()
    {
        $storage = $this->factory->getSitesTable($idSite = 0);
        $this->assertTrue($storage instanceof Storage);

        $backend = $storage->getBackend();
        $this->assertTrue($backend instanceof NullBackend);
        $this->assertSame('sitesTable#0#nonpersistent', $backend->getStorageId());
    }

    public function test_getNonPersistentStorage_shouldReturnStorageWithNullBackend()
    {
        $storage = $this->factory->getNonPersistentStorage('myKey');
        $this->assertTrue($storage instanceof Storage);

        $backend = $storage->getBackend();
        $this->assertTrue($backend instanceof NullBackend);
        $this->assertSame('myKey', $backend->getStorageId());
    }

    public function test_getNonPersistentStorage_shouldNotDecorateWithCacheBackendInTrackerMode()
    {
        SettingsServer::setIsTrackerApiRequest();
        $storage = $this->factory->getNonPersistentStorage('anykey');
        SettingsServer::setIsNotTrackerApiRequest();

        $this->assertTrue($storage->getBackend() instanceof NullBackend);
    }

    public function test_getNonPersistentStorage_shouldNotUseCache()
    {
        $storage = $this->factory->getNonPersistentStorage('myKey');
        $storage->setValue('mytest', 'myval');

        $storage = $this->factory->getNonPersistentStorage('myKey');
        $this->assertSame('', $storage->getValue('mytest', $default = '', FieldConfig::TYPE_STRING));
    }

    public function test_makeStorage_returnsStorageWithGivenBackend()
    {
        $backend = new NullBackend('test');
        $storage = $this->factory->makeStorage($backend);

        $this->assertTrue($storage instanceof Storage);

        $this->assertSame($backend, $storage->getBackend());
    }
}
