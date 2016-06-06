<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Measurable\Settings;

use Piwik\Db;
use Piwik\Measurable\MeasurableSetting;
use Piwik\Measurable\Settings\Storage;
use Piwik\Settings\Setting;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class StorageTest extends IntegrationTestCase
{
    private $idSite = 1;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var MeasurableSetting
     */
    private $setting;

    public function setUp()
    {
        parent::setUp();

        if (!Fixture::siteCreated($this->idSite)) {
            Fixture::createWebsite('2015-01-01 00:00:00');
        }

        $this->storage = $this->createStorage();
        $this->setting = $this->createSetting('test');
    }

    private function createStorage($idSite = null)
    {
        if (!isset($idSite)) {
            $idSite = $this->idSite;
        }

        return new Storage(Db::get(), $idSite);
    }

    private function createSetting($name)
    {
        return new MeasurableSetting($name, $name . ' Name');
    }

    public function test_getValue_shouldReturnNullByDefault()
    {
        $value = $this->storage->getValue($this->setting);
        $this->assertNull($value);
    }

    public function test_getValue_shouldReturnADefaultValueIfOneIsSet()
    {
        $this->setting->defaultValue = 194.34;
        $value = $this->storage->getValue($this->setting);
        $this->assertSame(194.34, $value);
    }

    public function test_setValue_getValue_shouldSetAndGetActualValue()
    {
        $this->storage->setValue($this->setting, 'myRandomVal');
        $value = $this->storage->getValue($this->setting);
        $this->assertEquals('myRandomVal', $value);
    }

    public function test_setValue_shouldNotSaveItInDatabase()
    {
        $this->storage->setValue($this->setting, 'myRandomVal');

        // make sure not actually stored
        $this->assertSettingValue(null, $this->setting);
    }

    public function test_save_shouldPersistValueInDatabase()
    {
        $this->storage->setValue($this->setting, 'myRandomVal');
        $this->storage->save();

        // make sure actually stored
        $this->assertSettingValue('myRandomVal', $this->setting);
    }

    public function test_save_shouldPersistValueForEachSiteInDatabase()
    {
        $this->storage->setValue($this->setting, 'myRandomVal');
        $this->storage->save();

        // make sure actually stored
        $this->assertSettingValue('myRandomVal', $this->setting);

        $storage = $this->createStorage($idSite = 2);
        $valueForDifferentSite = $storage->getValue($this->setting);
        $this->assertNull($valueForDifferentSite);
    }

    public function test_save_shouldPersistMultipleValues_ContainingInt()
    {
        $this->saveMultipleValues();

        $this->assertSettingValue('myRandomVal', $this->setting);
        $this->assertSettingValue(5, $this->createSetting('test2'));
        $this->assertSettingValue(array(1, 2, '4'), $this->createSetting('test3'));
    }

    public function test_deleteAll_ShouldRemoveTheEntireEntry()
    {
        $this->saveMultipleValues();

        $this->assertSettingNotEmpty($this->setting);
        $this->assertSettingNotEmpty($this->createSetting('test2'));
        $this->assertSettingNotEmpty($this->createSetting('test3'));

        $this->storage->deleteAllValues();

        $this->assertSettingEmpty($this->setting);
        $this->assertSettingEmpty($this->createSetting('test2'));
        $this->assertSettingEmpty($this->createSetting('test3'));
    }

    public function test_deleteValue_ShouldOnlyDeleteOneValue()
    {
        $this->saveMultipleValues();

        $this->assertSettingNotEmpty($this->setting);
        $this->assertSettingNotEmpty($this->createSetting('test2'));
        $this->assertSettingNotEmpty($this->createSetting('test3'));

        $this->storage->deleteValue($this->createSetting('test2'));
        $this->storage->save();

        $this->assertSettingEmpty($this->createSetting('test2'));

        $this->assertSettingNotEmpty($this->setting);
        $this->assertSettingNotEmpty($this->createSetting('test3'));
    }

    public function test_deleteValue_saveValue_ShouldNotResultInADeletedValue()
    {
        $this->saveMultipleValues();

        $this->storage->deleteValue($this->createSetting('test2'));
        $this->storage->setValue($this->createSetting('test2'), 'PiwikTest');
        $this->storage->save();

        $this->assertSettingValue('PiwikTest', $this->createSetting('test2'));
    }

    private function assertSettingValue($expectedValue, $setting)
    {
        $value = $this->createStorage()->getValue($setting);
        $this->assertSame($expectedValue, $value);
    }

    private function assertSettingNotEmpty(Setting $setting)
    {
        $value = $this->createStorage()->getValue($setting);
        $this->assertNotNull($value);
    }

    private function assertSettingEmpty(Setting $setting)
    {
        $value = $this->createStorage()->getValue($setting);
        $this->assertNull($value);
    }

    private function saveMultipleValues()
    {
        $this->storage->setValue($this->setting, 'myRandomVal');
        $this->storage->setValue($this->createSetting('test2'), 5);
        $this->storage->setValue($this->createSetting('test3'), array(1, 2, '4'));
        $this->storage->save();
    }
}
