<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Storage;

use Piwik\Settings\FieldConfig;
use Piwik\Settings\Storage\Backend\BackendInterface;
use Piwik\Settings\Storage\Storage;
use Piwik\Tests\Framework\Mock\Settings\FakeBackend;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * @group PluginSettings
 * @group Storage
 */
class StorageTest extends IntegrationTestCase
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var BackendInterface
     */
    protected $backend;

    /**
     * @var string
     */
    protected $settingName = 'myname';

    public function setUp(): void
    {
        parent::setUp();

        $this->backend = new FakeBackend('MyTestId');
        $this->backend->save(array($this->settingName => 'value1'));
        $this->storage = $this->buildStorage();
    }

    public function testGetBackend()
    {
        $this->assertSame($this->backend, $this->storage->getBackend());
    }

    public function testGetValueShouldReturnDefaultValueIfNoValueIsSet()
    {
        $value = $this->storage->getValue('UnkNownFielD', $default = '123', FieldConfig::TYPE_STRING);
        $this->assertSame($default, $value);
    }

    public function testGetValueShouldReturnDefaultValueAndNotCastDefaultValue()
    {
        $value = $this->storage->getValue('UnkNownFielD', $default = '123', FieldConfig::TYPE_INT);
        $this->assertSame($default, $value);
    }

    public function testGetValueShouldReturnASavedValueFromBackend()
    {
        $value = $this->getValueFromStorage($this->settingName);
        $this->assertSame('value1', $value);
    }

    public function testSetValueGetValueShouldSetAndGetActualValue()
    {
        $this->storage->setValue($this->settingName, 'myRandomVal');
        $value = $this->getValueFromStorage($this->settingName);
        $this->assertEquals('myRandomVal', $value);
    }

    public function testSetValueGetValueShouldCastValueWhenGettingTheValue()
    {
        $this->storage->setValue($this->settingName, '1');
        $value = $this->getValueFromStorage($this->settingName, FieldConfig::TYPE_BOOL);
        $this->assertTrue($value);
    }

    public function testSetValueShouldNotSaveItInDatabase()
    {
        $loaded = $this->backend->load();
        $this->storage->setValue($this->settingName, 'myRandomVal');

        $this->assertSame($loaded, $this->loadValuesFromBackend());
    }

    public function testSaveShouldPersistValueInDatabase()
    {
        $this->storage->setValue($this->settingName, 'myRandomVal');
        $this->storage->save();

        $this->assertEquals(
            array($this->settingName => 'myRandomVal'),
            $this->loadValuesFromBackend()
        );
    }

    public function testSaveShouldPersistMultipleValuesContainingInt()
    {
        $this->storage->setValue($this->settingName, 'myRandomVal');
        $this->storage->setValue('mySecondName', 5);
        $this->storage->save();

        $this->assertEquals(
            array($this->settingName => 'myRandomVal', 'mySecondName' => 5),
            $this->loadValuesFromBackend()
        );
    }

    public function testSaveShouldNotClearTrackerCacheEntriesIfThereWasNoChange()
    {
        TrackerCache::setCacheGeneral(array('testSetting' => 1));

        $this->assertArrayHasKey('testSetting', TrackerCache::getCacheGeneral());

        $this->storage->save();

        $this->assertArrayHasKey('testSetting', TrackerCache::getCacheGeneral());
    }

    public function testSaveShouldClearTrackerCacheEntriesIfThereWasActuallyAChange()
    {
        TrackerCache::setCacheGeneral(array('testSetting' => 1));

        $this->assertArrayHasKey('testSetting', TrackerCache::getCacheGeneral());

        $this->storage->setValue('myTest', 5); // it will save only when there was actually a change
        $this->storage->save();

        $this->assertArrayNotHasKey('testSetting', TrackerCache::getCacheGeneral());
    }

    private function getValueFromStorage($settingName, $type = null)
    {
        if (!isset($type)) {
            $type = FieldConfig::TYPE_STRING;
        }
        return $this->storage->getValue($settingName, $default = '', $type);
    }

    protected function buildStorage()
    {
        return new Storage($this->backend);
    }

    protected function loadValuesFromBackend()
    {
        return $this->backend->load();
    }
}
