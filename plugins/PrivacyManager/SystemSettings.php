<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Columns\Dimension;
use Piwik\Container\StaticContainer;
use Piwik\Log;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Manager;
use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Setting;

/**
 * Defines Settings for PrivacyManager.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var SystemSetting */
    public $privacyPolicyUrl;

    /** @var SystemSetting */
    public $termsAndConditionUrl;

    /** @var SystemSetting */
    public $showInEmbeddedWidgets;

    /** @var Setting */
    public $disabledDimensions;

    protected function init()
    {
        $this->privacyPolicyUrl = $this->createPrivacyPolicyUrlSetting();
        $this->termsAndConditionUrl = $this->createTermsAndConditionUrlSetting();
        $this->showInEmbeddedWidgets = $this->createShowInEmbeddedWidgetsSetting();

        $isWritable = Piwik::hasUserSuperUserAccess();
        $this->disabledDimensions = $this->createDisabledDimensions();
        $this->disabledDimensions->setIsWritableByCurrentUser($isWritable);
    }

    private function createPrivacyPolicyUrlSetting(): SystemSetting
    {
        return $this->makeSetting('privacyPolicyUrl', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = Piwik::translate('PrivacyManager_PrivacyPolicyUrl');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = Piwik::translate('PrivacyManager_PrivacyPolicyUrlDescription') . ' ' .
                Piwik::translate('PrivacyManager_PrivacyPolicyUrlDescriptionSuffix', ['anonymous']);
        });
    }

    private function createTermsAndConditionUrlSetting(): SystemSetting
    {
        return $this->makeSetting('termsAndConditionUrl', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = Piwik::translate('PrivacyManager_TermsAndConditionUrl');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = Piwik::translate('PrivacyManager_TermsAndConditionUrlDescription') . ' ' .
                Piwik::translate('PrivacyManager_PrivacyPolicyUrlDescriptionSuffix', ['anonymous']);
        });
    }

    private function createShowInEmbeddedWidgetsSetting(): SystemSetting
    {
        return $this->makeSetting('showInEmbeddedWidgets', $default = false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('PrivacyManager_ShowInEmbeddedWidgets');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = Piwik::translate('PrivacyManager_ShowInEmbeddedWidgetsDescription');
        });
    }

    private function createDisabledDimensions()
    {
        return $this->makeSetting('disabled_settings', $default = [], FieldConfig::TYPE_ARRAY, function (FieldConfig $field) {
            $field->introduction = 'Disabled Dimensions'; // TODO translate
            $field->inlineHelp = "Disable dimensions to avoid tracking this data no matter what is sent to the tracker. This can be useful in being compliant with various privacy regulations."; // TODO: translate
            $field->uiControl = FieldConfig::UI_CONTROL_MULTI_SELECT;
            $field->availableValues = $this->getAvailableDimensionsToDisable();
        });
    }

    private function getAvailableDimensionsToDisable()
    {
        $dimensions = [];
        $this->addDimensions($dimensions, VisitDimension::getAllDimensions(), $type = Piwik::translate('General_Visit'));
        return $dimensions;
    }

    /**
     * @param string[] $dimensions
     * @param Dimension[] $allDimensions
     * @param $dimensionType
     */
    private function addDimensions(array &$dimensions, array $allDimensions, $dimensionType)
    {
        foreach ($allDimensions as $dimension) {
            if ($dimension->isAlwaysEnabled()
                || !$this->isTrackingDimension($dimension)
            ) {
                continue;
            }

            $dimensions[$dimension->getId()] = ($dimension->getName() ?: get_class($dimension)) . ' (' . $dimensionType . ')';
        }
    }

    private function isTrackingDimension(Dimension $dimension)
    {
        foreach (['onNewVisit', 'onExistingVisit'] as $methodName) {
            if (!method_exists($dimension, $methodName)) {
                continue;
            }

            $method = new \ReflectionMethod($dimension, $methodName);
            $declaringClass = $method->getDeclaringClass();

            if (strpos($declaringClass->name, 'Piwik\Plugins') !== 0) {
                return true;
            }
        }
        return false;
    }
}
