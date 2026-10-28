<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager;

use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Measurable\MeasurableSetting;

class MeasurableSettings extends \Piwik\Settings\Measurable\MeasurableSettings
{
    /** @var MeasurableSetting|null */
    public $consentAwareAnalytics;

    protected function init()
    {
        $this->consentAwareAnalytics = $this->makeConsentAwareAnalyticsSetting();

        // the instance has to have the feature on before a site can be opted in to it
        $this->consentAwareAnalytics->setIsWritableByCurrentUser(ConsentAwareAnalytics::isEnabledForInstance());
    }

    private function makeConsentAwareAnalyticsSetting(): MeasurableSetting
    {
        $setting = ConsentAwareAnalytics::getMeasurableSetting($this->idSite);
        $setting->setConfigureCallback(function (FieldConfig $field) {
            $field->title = Piwik::translate('PrivacyManager_ConsentAwareAnalyticsMeasurableTitle');
            $field->inlineHelp = Piwik::translate('PrivacyManager_ConsentAwareAnalyticsMeasurableHelp');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });

        $this->addSetting($setting);

        return $setting;
    }
}
