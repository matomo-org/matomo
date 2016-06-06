<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Cache as PiwikCache;
use Piwik\Settings\Storage;
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

        $this->assertTrue($this->hasCache());

        SettingsStorage::clearCache();

        $this->assertFalse($this->hasCache());
    }

    private function hasCache()
    {
        return $this->getCache()->contains($this->storage->getOptionKey());
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

        $this->assertFalse($this->hasCache());
        $this->assertNotFalse($this->getValueFromOptionTable()); // make sure saved in db

        $storage = $this->buildStorage();
        $this->assertEquals(5, $storage->getValue($this->setting));
        $this->assertTrue($this->hasCache());
    }

    public function test_storageCreateACacheEntryIfNoCacheExistsYet()
    {
        $cache = Cache::getCacheGeneral();
        $this->assertArrayNotHasKey('settingsStorage', $cache); // make sure there is no cache entry yet

        $this->setSettingValueAndMakeSureCacheGetsCreated('myVal');

        $cache = $this->getCache()->fetch($this->storage->getOptionKey());

        $this->assertEquals(array(
            $this->setting->getKey() => 'myVal'
        ), $cache);
    }

    protected function buildStorage()
    {
        return new SettingsStorage('PluginName');
    }

    private function getCache()
    {
        return PiwikCache::getEagerCache();
    }

    private function setSettingValueInCache($value)
    {
        $cache = $this->getCache();
        $cache->save($this->storage->getOptionKey(), array(
            $this->setting->getKey() => $value
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
