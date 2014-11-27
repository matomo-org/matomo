<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Option;
use Piwik\Settings\Storage;
use Piwik\Settings\Setting;
use Piwik\Tests\Integration\Settings\IntegrationTestCase;
use Piwik\Tests\Integration\Settings\StorageTest;
use Piwik\Tracker\Cache;
use Piwik\Tracker\SettingsStorage;

/**
 * @group PluginSettings
 * @group Storage
 * @group SettingStorage
 */
class SettingsStorageTest extends StorageTest
{

    public function test_storageShouldLoadSettingsFromCacheIfPossible()
    {
        $this->setSettingValueInCache('my0815RandomName');

        $this->assertEquals('my0815RandomName', $this->storage->getValue($this->setting));
    }

    public function test_storageShouldLoadSettingsFromCache_AndNotCreateADatabaseInstance()
    {
        $this->setSettingValueInCache('my0815RandomName');

        $this->storage->getValue($this->setting);

        $this->assertNotDbConnectionCreated();
    }

    public function test_clearCache_shouldActuallyClearTheCacheEntry()
    {
        $this->setSettingValueInCache('my0815RandomName');

        $this->assertArrayHasKey('settingsStorage', Cache::getCacheGeneral());

        SettingsStorage::clearCache();

        $this->assertArrayNotHasKey('settingsStorage', Cache::getCacheGeneral());
    }

    public function test_storageShouldNotCastAnyCachedValue()
    {
        $this->setSettingValueInCache(5);

        $this->assertEquals(5, $this->storage->getValue($this->setting));
    }

    public function test_storageShouldFallbackToDatebaseInCaseNoCacheExists()
    {
        $this->storage->setValue($this->setting, 5);
        $this->storage->save();

        $this->assertNotFalse($this->getValueFromOptionTable()); // make sure saved in db

        $storage = $this->buildStorage();
        $this->assertEquals(5, $storage->getValue($this->setting));
    }

    public function test_storageCreateACacheEntryIfNoCacheExistsYet()
    {
        $cache = Cache::getCacheGeneral();
        $this->assertArrayNotHasKey('settingsStorage', $cache); // make sure there is no cache entry yet

        $this->setSettingValueAndMakeSureCacheGetsCreated('myVal');

        $cache = Cache::getCacheGeneral();

        $this->assertEquals(array(
            $this->storage->getOptionKey() => array(
                $this->setting->getKey() => 'myVal'
            )
        ), $cache['settingsStorage']);
    }

    public function test_shouldAddACacheEntryToAnotherCacheEntryAndNotOverwriteAll()
    {
        $dummyCacheEntry = array(
            'Plugin_PluginNameOther_Settings' => array(
                'anything' => 'anyval',
                'any' => 'other'
            )
        );
        Cache::setCacheGeneral(array(
            'settingsStorage' => $dummyCacheEntry
        ));

        Option::set($this->storage->getOptionKey(), serialize(array('mykey' => 'myVal')));

        $this->buildStorage()->getValue($this->setting); // force adding new cache entry

        $cache = Cache::getCacheGeneral();

        $dummyCacheEntry[$this->storage->getOptionKey()] = array(
            'mykey' => 'myVal'
        );

        $this->assertEquals($dummyCacheEntry, $cache['settingsStorage']);
    }

    protected function buildStorage()
    {
        return new SettingsStorage('PluginName');
    }

    private function setSettingValueInCache($value)
    {
        Cache::setCacheGeneral(array(
            'settingsStorage' => array(
                $this->storage->getOptionKey() => array(
                    $this->setting->getKey() => $value
                )
            )
        ));
    }

    private function setSettingValueAndMakeSureCacheGetsCreated($value)
    {
        $this->storage->setValue($this->setting, $value);
        $this->storage->save();

        $storage = $this->buildStorage();
        $storage->getValue($this->setting); // force creation of cache by loading settings
    }

}
