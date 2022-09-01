<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Storage\Backend;

use Piwik\Settings\Storage\Backend\MeasurableSettingsTable;
use Piwik\Settings\Storage\Backend\Config;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Settings
 * @group Backend
 * @group Storage
 */
class ConfigTest extends IntegrationTestCase
{

    /**
     * @var MeasurableSettingsTable
     */
    private $backend1;

    /**
     * @var MeasurableSettingsTable
     */
    private $backend2;

    /**
     * @var MeasurableSettingsTable
     */
    private $backend3;

    /**
     * @var MeasurableSettingsTable[]
     */
    private $allBackends = array();

    public function setUp(): void
    {
        parent::setUp();

        $this->backend1 = $this->createSettings('MySection1');
        $this->backend2 = $this->createSettings('MySection2');
        $this->backend3 = $this->createSettings('MySection3');
        $this->allBackends = array($this->backend1, $this->backend2, $this->backend3);
    }

    private function createSettings($plugin)
    {
        return new Config($plugin);
    }

    public function test_construct_shouldThrowAnException_IfSectionIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No section given');

        $this->createSettings('');
    }

    public function test_load_shouldNotHaveAnySettingsByDefault()
    {
        $this->assertSame(array(), $this->backend1->load());
        $this->assertSame(array(), $this->backend2->load());
        $this->assertSame(array(), $this->backend3->load());
    }

    public function test_getStorageId_shouldIncludePluginNameAndLogin()
    {
        $this->assertSame('Config_MySection1', $this->backend1->getStorageId());
        $this->assertSame('Config_MySection2', $this->backend2->getStorageId());
        $this->assertSame('Config_MySection3', $this->backend3->getStorageId());
    }

    public function test_save_ShouldOnlySaveForSpecificPluginAndIdSite()
    {
        $value1 = array('Mysetting1' => 'value1', 'Mysetting2' => 'val');

        $this->backend1->save($value1);

        $this->assertSame($value1, $this->backend1->load());
        $this->assertSame(array(), $this->backend2->load());
        $this->assertSame(array(), $this->backend3->load());

        $value2 = $this->getExampleValues();
        $this->backend2->save($value2);

        $this->assertSame($value1, $this->backend1->load());
        $this->assertSame($value2, $this->backend2->load());
        $this->assertSame(array(), $this->backend3->load());

        $value3 = $this->getExampleValues();
        $this->backend3->save($value3);

        $this->assertSame($value1, $this->backend1->load());
        $this->assertSame($value2, $this->backend2->load());
        $this->assertSame($value3, $this->backend3->load());
    }

    public function test_delete_shouldDeleteAllValuesForGivenSection()
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

        $this->backend1->save($value1);

        $this->assertSame($value1, $this->backend1->load());

        $value2 = array('mytest' => 'test2', 'Mysetting2' => 'val', 'Mysetting1' => 'valueNew');
        $this->backend1->save($value2);

        $this->assertEquals($value2, $this->backend1->load());
    }

    public function test_save_NoLongerExistingValues_shouldNotBeRemoved()
    {
        $value = $this->saveValueForAllBackends();

        // overwrite for backend 3
        $value2 = array('mytest' => 'test2', 'Mysetting1' => 'valueNew');
        $this->backend3->save($value2);

        $expectedNew = $value2;
        $expectedNew['Mysetting2'] = 'val'; // should not delete no longer existing values

        $this->assertEquals($expectedNew, $this->backend3->load());

        // make sure other backends remain unchanged
        foreach ($this->allBackends as $backend) {
            if ($backend !== $this->backend3) {
                $this->assertSame($value, $backend->load());
            }
        }
    }

    public function test_save_load_ShouldBeAbleToSaveAndLoadArrayValues()
    {
        $value1 = array('Mysetting1' => 'value1', 'Mysetting2' => array('val', 'val7', 'val5'));

        $this->backend3->save($value1);
        $this->assertEquals($value1, $this->backend3->load());
    }

    public function test_save_load_ShouldBeAbleToSaveAndLoadArrayValues_OnlyOneKey()
    {
        $value1 = array('Mysetting1' => 'value1', 'Mysetting2' => array('val'));

        $this->backend3->save($value1);


        $value1 = array('Mysetting1' => 'value1', 'Mysetting2' => array('val'));
        $this->assertEquals($value1, $this->backend3->load());
    }

    public function test_save_ShouldBeAbleToSaveBoolValues()
    {
        $value1 = array('Mysetting1' => true, 'Mysetting2' => array('val', 'val7', false, true, 'val5'));

        $this->backend3->save($value1);

        $value1 = array('Mysetting1' => '1', 'Mysetting2' => array('val', 'val7', false, true, 'val5'));
        $this->assertEquals($value1, $this->backend3->load());
    }

    public function test_save_ShouldNotIgnoreNullValues()
    {
        $value1 = array('Mysetting1' => true, 'MySetting3' => null, 'Mysetting2' => array('val', null, true, 'val5'));

        $this->backend3->save($value1);

        $value1 = array('Mysetting1' => '1', 'Mysetting2' => array('val', null, true, 'val5'), 'MySetting3' => null);
        $this->assertEquals($value1, $this->backend3->load());
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
