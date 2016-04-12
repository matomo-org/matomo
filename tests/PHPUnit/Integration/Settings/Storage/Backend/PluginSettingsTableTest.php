<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Storage\Backend;

use Piwik\Config;
use Piwik\Db;
use Piwik\Settings\Storage\Backend\PluginSettingsTable;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Settings
 * @group Backend
 * @group Storage
 */
class PluginSettingsTableTest extends IntegrationTestCase
{

    /**
     * @var PluginSettingsTable
     */
    private $backendPlugin1;

    /**
     * @var PluginSettingsTable
     */
    private $backendPlugin2;

    /**
     * @var PluginSettingsTable
     */
    private $backendUser1;

    /**
     * @var PluginSettingsTable
     */
    private $backendUser2;

    /**
     * @var PluginSettingsTable[]
     */
    private $allBackends = array();

    public function setUp()
    {
        parent::setUp();

        $this->backendPlugin1 = $this->createSettings('MyPluginName', '');
        $this->backendPlugin2 = $this->createSettings('MyPluginName2', '');
        $this->backendUser1 = $this->createSettings('MyPluginName', 'user1');
        $this->backendUser2 = $this->createSettings('MyPluginName', 'user2');
        $this->allBackends = array($this->backendPlugin1, $this->backendPlugin2, $this->backendUser1, $this->backendUser2);
    }

    private function createSettings($plugin, $login)
    {
        return new PluginSettingsTable($plugin, $login);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No plugin name given
     */
    public function test_construct_shouldThrowAnException_IfPluginNameIsEmpty()
    {
        $this->createSettings('', '');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid user login name
     */
    public function test_construct_shouldThrowAnException_IfUserLoginFalse()
    {
        $this->createSettings('MyPlugin', false);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid user login name
     */
    public function test_construct_shouldThrowAnException_IfUserLoginNull()
    {
        $this->createSettings('MyPlugin', null);
    }

    public function test_load_shouldNotHaveAnySettingsByDefault()
    {
        $this->assertSame(array(), $this->backendPlugin1->load());
        $this->assertSame(array(), $this->backendPlugin2->load());
        $this->assertSame(array(), $this->backendUser1->load());
        $this->assertSame(array(), $this->backendUser2->load());
    }

    public function test_getStorageId_shouldIncludePluginNameAndLogin()
    {
        $this->assertSame('PluginSettings_MyPluginName_User_', $this->backendPlugin1->getStorageId());
        $this->assertSame('PluginSettings_MyPluginName2_User_', $this->backendPlugin2->getStorageId());
        $this->assertSame('PluginSettings_MyPluginName_User_user1', $this->backendUser1->getStorageId());
        $this->assertSame('PluginSettings_MyPluginName_User_user2', $this->backendUser2->getStorageId());
    }

    public function test_save_ShouldOnlySaveForSpecificPlugin_NoUserGiven()
    {
        $value1 = array('Mysetting1' => 'value1', 'Mysetting2' => 'val');

        $this->backendPlugin1->save($value1);

        $this->assertSame($value1, $this->backendPlugin1->load());
        $this->assertSame(array(), $this->backendPlugin2->load());
        $this->assertSame(array(), $this->backendUser1->load());
        $this->assertSame(array(), $this->backendUser2->load());

        $value2 = array('mytest' => 'test2');
        $this->backendPlugin2->save($value2);

        $this->assertSame($value1, $this->backendPlugin1->load());
        $this->assertSame($value2, $this->backendPlugin2->load());
        $this->assertSame(array(), $this->backendUser1->load());
        $this->assertSame(array(), $this->backendUser2->load());
    }

    public function test_save_ShouldOnlySaveForSpecificPluginAndUser()
    {
        $values = array_fill(0, count($this->allBackends), array());

        foreach ($this->allBackends as $index => $backend) {
            $values[$index] = $this->getExampleValues();
            $backend->save($values[$index]);

            foreach ($this->allBackends as $j => $backend2) {
                $this->assertSame($values[$j], $backend2->load());
            }
        }
    }

    public function test_delete_shouldDeleteAllValuesButOnlyForSpecificPluginAndLogin()
    {
        $values = array();
        foreach ($this->allBackends as $index => $backend) {
            $values[$index] = $this->getExampleValues();
            $backend->save($values[$index]);
            $this->assertSame($values[$index], $backend->load());
        }

        foreach ($this->allBackends as $index => $backend) {
            $backend->delete();
            $values[$index] = array();

            // we make sure the values for all others are still set and only the current one was deleted
            foreach ($this->allBackends as $j => $backend2) {
                $this->assertSame($values[$j], $backend2->load());
            }
        }
    }

    public function test_save_DuplicateValuesShouldBeOverwritten()
    {
        $value1 = array('Mysetting1' => 'value1', 'Mysetting2' => 'val');

        $this->backendPlugin1->save($value1);

        $this->assertSame($value1, $this->backendPlugin1->load());

        $value2 = array('mytest' => 'test2', 'Mysetting2' => 'val', 'Mysetting1' => 'valueNew');
        $this->backendPlugin1->save($value2);

        $this->assertEquals($value2, $this->backendPlugin1->load());
    }

    public function test_save_NoLongerExistingValues_shouldBeRemoved()
    {
        $value = $this->saveValueForAllBackends();

        // overwrite only user1
        $value2 = array('mytest' => 'test2', 'Mysetting1' => 'valueNew');
        $this->backendUser1->save($value2);
        $this->assertEquals($value2, $this->backendUser1->load());

        // make sure other backends remain unchanged
        foreach ($this->allBackends as $backend) {
            if ($backend !== $this->backendUser1) {
                $this->assertSame($value, $backend->load());
            }
        }
    }

    public function test_save_load_ShouldBeAbleToSaveAndLoadArrayValues()
    {
        $value1 = array('Mysetting1' => 'value1', 'Mysetting2' => array('val', 'val7', 'val5'));

        $this->backendUser1->save($value1);
        $this->assertEquals($value1, $this->backendUser1->load());
    }

    public function test_save_load_ShouldBeAbleToSaveAndLoadArrayValues_OnlyOneKey()
    {
        $value1 = array('Mysetting1' => 'value1', 'Mysetting2' => array('val'));

        $this->backendUser1->save($value1);


        $value1 = array('Mysetting1' => 'value1', 'Mysetting2' => 'val');
        // it doesn't return an array for Mysetting2 but it is supposed to be casted to array by storage in this case
        $this->assertEquals($value1, $this->backendUser1->load());
    }

    public function test_save_ShouldBeAbleToSaveBoolValues()
    {
        $value1 = array('Mysetting1' => true, 'Mysetting2' => array('val', 'val7', false, true, 'val5'));

        $this->backendUser1->save($value1);

        $value1 = array('Mysetting1' => '1', 'Mysetting2' => array('val', 'val7', '0', '1', 'val5'));
        $this->assertEquals($value1, $this->backendUser1->load());
    }

    public function test_save_ShouldIgnoreNullValues()
    {
        $value1 = array('Mysetting1' => true, 'MySetting3' => null, 'Mysetting2' => array('val', null, true, 'val5'));

        $this->backendUser1->save($value1);

        $value1 = array('Mysetting1' => '1', 'Mysetting2' => array('val', '1', 'val5'));
        $this->assertEquals($value1, $this->backendUser1->load());
    }

    public function test_removeAllUserSettingsForUser_shouldOnlyRemoveSettingsForThatUser()
    {
        $value = $this->saveValueForAllBackends();

        PluginSettingsTable::removeAllUserSettingsForUser('user1');

        foreach ($this->allBackends as $backend) {
            if ($backend === $this->backendUser1) {
                $this->assertSame(array(), $backend->load());
            } else {
                $this->assertSame($value, $backend->load());
            }
        }
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No userLogin specified
     */
    public function test_removeAllUserSettingsForUser_shouldThrowAnExceptionIfLoginIsEmpty()
    {
        PluginSettingsTable::removeAllUserSettingsForUser('');
    }

    public function test_removeAllSettingsForPlugin_shouldOnlyRemoveSettingsForThatPlugin()
    {
        $value = $this->saveValueForAllBackends();

        PluginSettingsTable::removeAllSettingsForPlugin('MyPluginName');

        foreach ($this->allBackends as $backend) {
            if ($backend === $this->backendPlugin2) {
                $this->assertSame($value, $backend->load());
            } else {
                $this->assertSame(array(), $backend->load());
            }
        }
    }

    private function saveValueForAllBackends()
    {
        $value = array('Mysetting1' => 'value1', 'Mysetting2' => 'val');

        foreach ($this->allBackends as $backend) {
            $backend->save($value);
            $this->assertSame($value, $backend->load());
        }

        return $value;
    }

    private function getExampleValues()
    {
        return array('Mysetting3' => 'value3', 'Mysetting4' . rand(4,99) => 'val' . rand(0, 10));
    }
}
