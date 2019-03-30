<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Validators\NumberRange;

/**
 * Defines Settings for UserCountry.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $minLength;
    
    /** @var Setting */
    public $isOneUppercaseLetterRequired;
    
    /** @var Setting */
    public $isOneLowercaseLetterRequired;
    
    /** @var Setting */
    public $isOneNumberRequired;
    
    /** @var Setting */
    public $isOneSpecialCharacterRequired;
    
    protected function init()
    {
        $this->title = Piwik::translate('UsersManager_PasswordPolicyConfiguration');
        
        $this->minLength = $this->createMinLengthSetting();
        $this->isOneUppercaseLetterRequired = $this->createRequireOneUppercaseLetterSetting();
        $this->isOneLowercaseLetterRequired = $this->createRequireOneLowercaseLetterSetting();
        $this->isOneNumberRequired = $this->createRequireOneNumberSetting();
        $this->isOneSpecialCharacterRequired = $this->createRequireOneSpecialCharacterSetting();
    }
    
    private function createMinLengthSetting()
    {
        return $this->makeSetting('minLength', UsersManager::PASSWORD_DEFAULT_MIN_LENGTH, FieldConfig::TYPE_INT, function (FieldConfig $field) {
            $field->title = Piwik::translate('UsersManager_PasswordPolicyMinLengthSetting');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = Piwik::translate('UsersManager_PasswordPolicyMinLengthSettingDescription', [UsersManager::PASSWORD_DEFAULT_MIN_LENGTH]);
            $field->validators[] = new NumberRange(UsersManager::PASSWORD_DEFAULT_MIN_LENGTH);
        });
    }
    
    private function createRequireOneUppercaseLetterSetting()
    {
        return $this->makeSetting('requireOneUppercaseLetter', false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('UsersManager_PasswordPolicyOneUppercaseLetterSetting');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = Piwik::translate('UsersManager_PasswordPolicyOneUppercaseLetterSettingDescription');
        });
    }
    
    private function createRequireOneLowercaseLetterSetting()
    {
        return $this->makeSetting('requireOneLowercaseLetter', false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('UsersManager_PasswordPolicyOneLowercaseLetterSetting');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = Piwik::translate('UsersManager_PasswordPolicyOneLowercaseLetterSettingDescription');
        });
    }
    
    private function createRequireOneNumberSetting()
    {
        return $this->makeSetting('requireOneNumber', false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('UsersManager_PasswordPolicyOneNumberSetting');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = Piwik::translate('UsersManager_PasswordPolicyOneNumberSettingDescription');
        });
    }
    
    private function createRequireOneSpecialCharacterSetting()
    {
        return $this->makeSetting('requireOneSpecialCharacter', false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('UsersManager_PasswordPolicyOneSpecialCharacterSetting');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = Piwik::translate('UsersManager_PasswordPolicyOneSpecialCharacterSettingDescription');
        });
    }
    
}
