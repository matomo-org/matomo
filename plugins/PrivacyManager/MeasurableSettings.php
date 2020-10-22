<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Piwik;
use Piwik\Settings\Measurable\MeasurableSetting;
use Piwik\Settings\FieldConfig;

/**
 * Defines Measurable Settings for PrivacyManager.
 */
class MeasurableSettings extends \Piwik\Settings\Measurable\MeasurableSettings
{
    /** @var MeasurableSetting */
    public $forceCookielessTracking;

    protected function init()
    {
        $systemSettings = new SystemSettings();

        $this->forceCookielessTracking = $this->createForceCookielessTrackingSetting();
        $this->forceCookielessTracking->setIsWritableByCurrentUser(!$systemSettings->forceCookielessTracking->getValue());
    }

    private function createForceCookielessTrackingSetting(): MeasurableSetting
    {
        return $this->makeSetting('forceCookielessTracking', $default = false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('PrivacyManager_ForceCookielessTracking');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = Piwik::translate('PrivacyManager_ForceCookielessTrackingDescriptionPerSite');
        });
    }
}
