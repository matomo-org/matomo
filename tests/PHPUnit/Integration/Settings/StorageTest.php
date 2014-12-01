<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings;

use Piwik\Option;
use Piwik\Settings\Storage;
use Piwik\Settings\Setting;

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
     * @var Setting
     */
    protected $setting;

    public function setUp()
    {
        parent::setUp();

        $this->setSuperUser();
        $this->storage = $this->buildStorage();
        $this->setting = $this->buildUserSetting('myname', 'My Name');
    }

    public function test_constructor_shouldNotEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        $this->buildStorage();

        $this->assertNotDbConnectionCreated();
    }

    public function test_getValue_shouldEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        $this->storage->getValue($this->setting);

        $this->assertDbConnectionCreated();
    }

    public function test_setValue_shouldEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        $this->storage->setValue($this->setting, 5);

        $this->assertDbConnectionCreated();
    }

    public function test_deleteValue_shouldEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        $this->storage->deleteValue($this->setting, 5);

        $this->assertDbConnectionCreated();
    }

    public function test_deleteAll_shouldEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        $this->storage->deleteAllValues();

        $this->assertDbConnectionCreated();
    }

    public function test_save_shouldEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        $this->storage->save();

        $this->assertDbConnectionCreated();
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

        $this->assertFalse($this->getValueFromOptionTable());
    }

    public function test_save_shouldPersistValueInDatabase()
    {
        $this->storage->setValue($this->setting, 'myRandomVal');
        $this->storage->save();

        $this->assertEquals('a:1:{s:22:"myname#superUserLogin#";s:11:"myRandomVal";}', $this->getValueFromOptionTable());
    }

    public function test_save_shouldPersistMultipleValues_ContainingInt()
    {
        $this->storage->setValue($this->setting, 'myRandomVal');
        $this->storage->setValue($this->buildUserSetting('mySecondName', 'My Name'), 5);
        $this->storage->save();

        $this->assertEquals('a:2:{s:22:"myname#superUserLogin#";s:11:"myRandomVal";s:28:"mySecondName#superUserLogin#";i:5;}', $this->getValueFromOptionTable());
    }

    public function test_deleteAll_ShouldRemoveTheEntireEntry()
    {
        $this->storage->setValue($this->setting, 'myRandomVal');
        $this->storage->save();
        $this->storage->deleteAllValues();

        $this->assertFalse($this->getValueFromOptionTable());
    }

    public function test_getOptionKey_shouldContainThePluginName()
    {
        $this->assertEquals('Plugin_PluginName_Settings', $this->storage->getOptionKey());
    }

    protected function buildStorage()
    {
        return new Storage('PluginName');
    }

    protected function getValueFromOptionTable()
    {
        return Option::get($this->storage->getOptionKey());
    }

}
