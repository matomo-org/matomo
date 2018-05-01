<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Plugin;

use Piwik\Settings\FieldConfig;
use Piwik\Settings\Setting;
use Piwik\Settings\Storage\Storage;
use Piwik\Settings\Storage\Backend;
use Exception;
use Piwik\Tests\Framework\Mock\Settings\FakeBackend;
use Piwik\Validators\NotEmpty;
use Piwik\Validators\NumberRange;


/**
 * @group Settings
 * @group Setting
 */
class SettingTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage setting name "myname-" in plugin "MyPluginName" is invalid
     */
    public function test_constructor_shouldThrowException_IfTheSettingNameIsNotValid()
    {
        $this->makeSetting('myname-');
    }

    public function test_configureField_shouldAssignDefaultField_IfTypeIsGivenButNoField()
    {
        $setting = $this->makeSetting('myname', FieldConfig::TYPE_ARRAY);
        $field = $setting->configureField();
        $this->assertEquals(FieldConfig::UI_CONTROL_MULTI_SELECT, $field->uiControl);

        $setting = $this->makeSetting('myname2', FieldConfig::TYPE_BOOL);
        $field = $setting->configureField();
        $this->assertEquals(FieldConfig::UI_CONTROL_CHECKBOX, $field->uiControl);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Type must be an array when using a multi select
     */
    public function test_configureField_ShouldCheckThatTypeMakesActuallySenseForConfiguredUiControl()
    {
        $setting = $this->makeSetting('myname', FieldConfig::TYPE_STRING, $default = '', function (FieldConfig $field) {
            $field->uiControl = FieldConfig::UI_CONTROL_MULTI_SELECT;
        });
        $setting->configureField();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Type does not exist
     */
    public function test_configureField_ChecksTheGivenTypeIsKnown()
    {
        $setting = $this->makeSetting('myname', 'unknOwnTyPe');
        $setting->configureField();
    }

    public function test_setValue_shouldValidateAutomatically_IfFieldOptionsAreGiven()
    {
        $setting = $this->makeSetting('myname', null, $default = '', function (FieldConfig $field) {
            $field->availableValues = array('allowedval' => 'DisplayName', 'allowedval2' => 'Name 2');
        });

        // valid value
        $setting->setValue('allowedval');
        $this->assertSame('allowedval', $setting->getValue());

        try {
            $setting->setValue('invAliDValue');
        } catch (Exception $e) {
            $this->assertContains('CoreAdminHome_PluginSettingsValueNotAllowed', $e->getMessage());
            return;
        }

        $this->fail('An expected exception has not been thrown');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingsValueNotAllowed
     */
    public function test_setValue_shouldApplyValidationAndFail_IfOptionsAreSetAndValueIsAnArray()
    {
        $setting = $this->makeSetting('myname', FieldConfig::TYPE_ARRAY, $default = '', function (FieldConfig $field) {
            $field->availableValues = array('allowedval' => 'DisplayName', 'allowedval2' => 'Name 2');
            $field->uiControl = FieldConfig::UI_CONTROL_MULTI_SELECT;
        });

        $setting->setValue(array('allowed', 'notallowed'));
    }

    public function test_setSettingValue_shouldApplyValidationAndSucceed_IfOptionsAreSet()
    {
        $setting = $this->makeSetting('myname', FieldConfig::TYPE_ARRAY, $default = '', function (FieldConfig $field) {
            $field->availableValues = array('allowedval' => 'DisplayName', 'allowedval2' => 'Name 2');
            $field->uiControl = FieldConfig::UI_CONTROL_MULTI_SELECT;
        });
        $setting->setValue(array('allowedval', 'allowedval2'));
        $this->assertSame($setting->getValue(), array('allowedval', 'allowedval2'));

        $setting = $this->makeSetting('myname2', null, $default = '', function (FieldConfig $field) {
            $field->availableValues = array('allowedval' => 'DisplayName', 'allowedval2' => 'Name 2');
        });

        $setting->setValue('allowedval');
        $this->assertSame($setting->getValue(), 'allowedval');
    }

    public function test_setValue_shouldValidateAutomatically_IfTypeBoolIsUsed()
    {
        $setting = $this->makeSetting('myname', FieldConfig::TYPE_BOOL);

        // valid values
        $setting->setValue('1');
        $this->assertSame(true, $setting->getValue());
        $setting->setValue(false);
        $this->assertSame(false, $setting->getValue());
        $setting->setValue(1);
        $this->assertSame(true, $setting->getValue());

        try {
            $setting->setValue('invAliDValue');
        } catch (Exception $e) {
            $this->assertContains('CoreAdminHome_PluginSettingsValueNotAllowed', $e->getMessage());
            return;
        }

        $this->fail('An expected exception has not been thrown');
    }

    /**
     * @dataProvider getNumericTypes
     */
    public function test_setValue_shouldValidateAutomatically_IfTypeIsNumeric($type)
    {
        $setting = $this->makeSetting('myname', $type);

        // valid values
        $setting->setValue('1');
        $setting->setValue('1.5');
        $setting->setValue(0);
        $setting->setValue(0.5);
        $setting->setValue(-22.5);

        try {
            $setting->setValue('1invalid');
        } catch (Exception $e) {
            $this->assertContains('CoreAdminHome_PluginSettingsValueNotAllowed', $e->getMessage());
            return;
        }

        $this->fail('An expected exception has not been thrown');
    }

    public function test_setValue_shouldExecuteValidators()
    {
        $setting = $this->makeSetting('myname');
        $config = $setting->configureField();
        $config->validators[] = new NotEmpty();
        $config->validators[] = new NumberRange(5, 10);

        // valid values
        $setting->setValue('7');
        $setting->setValue('8.5');

        try {
            $setting->setValue('1invalid');
            $this->fail('An expected exception has not been thrown');
        } catch (Exception $e) {
            $this->assertContains('General_ValidatorErrorNotANumber', $e->getMessage());
        }

        try {
            $setting->setValue('3');
            $this->fail('An expected exception has not been thrown');
        } catch (Exception $e) {
            $this->assertContains('General_ValidatorErrorNumberTooLow', $e->getMessage());
        }

        try {
            $setting->setValue('');
            $this->fail('An expected exception has not been thrown');
        } catch (Exception $e) {
            $this->assertContains('General_ValidatorErrorEmptyValue', $e->getMessage());
        }
    }

    public function getNumericTypes()
    {
        return array(array(FieldConfig::TYPE_INT), array(FieldConfig::TYPE_FLOAT));
    }

    public function test_isWritableByCurrentUser_shouldNotBeWritableByDefault()
    {
        $setting = new Setting($name = 'test', $default = 0, $type = FieldConfig::TYPE_INT, function () {});
        $this->assertFalse($setting->isWritableByCurrentUser());
    }

    public function test_setIsWritableByCurrentUser()
    {
        $setting = $this->makeSetting('myName');
        $this->assertTrue($setting->isWritableByCurrentUser());

        $setting->setIsWritableByCurrentUser(0);
        $this->assertFalse($setting->isWritableByCurrentUser());

        $setting->setIsWritableByCurrentUser(1);
        $this->assertTrue($setting->isWritableByCurrentUser());

        $setting->setIsWritableByCurrentUser(false);
        $this->assertFalse($setting->isWritableByCurrentUser());
    }

    public function test_setDefaultValue_getDefaultValue()
    {
        $setting = $this->makeSetting('myname');
        $setting->setDefaultValue(5);
        $this->assertSame(5, $setting->getDefaultValue());
    }

    public function test_getType()
    {
        $setting = $this->makeSetting('myname', FieldConfig::TYPE_ARRAY);
        $this->assertSame(FieldConfig::TYPE_ARRAY, $setting->getType());
    }

    public function test_getName()
    {
        $setting = $this->makeSetting('myName');
        $this->assertSame('myName', $setting->getName());
    }

    protected function makeSetting($name, $type = null, $default = '', $configure = null)
    {
        if (!isset($type)) {
            $type = FieldConfig::TYPE_STRING;
        }

        $setting = new Setting($name, $default, $type, 'MyPluginName');
        $setting->setStorage(new Storage(new Backend\NullBackend('myId')));
        $setting->setIsWritableByCurrentUser(true);

        if (isset($configure)) {
            $setting->setConfigureCallback($configure);
        }

        return $setting;
    }

    public function test_save_shouldPersistValue()
    {
        $value = array(2,3,4);

        $backend = new FakeBackend('test');
        $backend->save(array());
        $storage = new Storage($backend);

        $setting = $this->makeSetting('mysetting', FieldConfig::TYPE_ARRAY);
        $setting->setStorage($storage);
        $setting->setValue($value);

        // assert not saved in backend
        $this->assertSame(array(), $backend->load());

        $setting->save();

        // assert saved in backend
        $this->assertSame(array('mysetting' => $value), $backend->load());
    }
}
