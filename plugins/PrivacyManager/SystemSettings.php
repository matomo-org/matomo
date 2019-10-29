<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

/**
 * Defines Settings for PrivacyManager.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $privacyPolicyUrl;

    /** @var Setting */
    public $termsAndConditionUrl;

    /** @var Setting */
    public $showInEmbeddedWidgets;

    protected function init()
    {
        $this->privacyPolicyUrl = $this->createPrivacyPolicyUrlSetting();
        $this->termsAndConditionUrl = $this->createTermsAndConditionUrlSetting();
        $this->showInEmbeddedWidgets = $this->createShowInEmbeddedWidgetsSetting();
    }

    private function createPrivacyPolicyUrlSetting()
    {
        return $this->makeSetting('privacyPolicyUrl', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = Piwik::translate('PrivacyManager_PrivacyPolicyUrl');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = Piwik::translate('PrivacyManager_PrivacyPolicyUrlDescription') . ' ' .
                Piwik::translate('PrivacyManager_PrivacyPolicyUrlDescriptionSuffix', ['anonymous']);
        });
    }

    private function createTermsAndConditionUrlSetting()
    {
        return $this->makeSetting('termsAndConditionUrl', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = Piwik::translate('PrivacyManager_TermsAndConditionUrl');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = Piwik::translate('PrivacyManager_TermsAndConditionUrlDescription') . ' ' .
                Piwik::translate('PrivacyManager_PrivacyPolicyUrlDescriptionSuffix', ['anonymous']);
        });
    }

    private function createShowInEmbeddedWidgetsSetting()
    {
        return $this->makeSetting('showInEmbeddedWidgets', $default = false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('PrivacyManager_ShowInEmbeddedWidgets');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = Piwik::translate('PrivacyManager_ShowInEmbeddedWidgetsDescription');
        });
    }
}
