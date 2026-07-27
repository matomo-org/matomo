<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live;

use Piwik\Piwik;
use Piwik\Plugins\Live\Settings\AggregatedRealtimeReportsEnabled as AggregatedRealtimeReportsEnabledSetting;
use Piwik\Plugins\Live\Settings\VisitorLogDisabled as VisitorLogDisabledSetting;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Measurable\MeasurableSetting;

class MeasurableSettings extends \Piwik\Settings\Measurable\MeasurableSettings
{
    /** @var MeasurableSetting|null */
    public $disableVisitorLog;

    /** @var MeasurableSetting|null */
    public $disableVisitorProfile;

    /** @var MeasurableSetting|null */
    public $enableAggregatedRealtimeReports;

    protected function init()
    {
        $this->disableVisitorLog               = $this->makeVisitorLogSetting();
        $this->enableAggregatedRealtimeReports = $this->makeAggregatedRealtimeReportsSetting();
        $this->disableVisitorProfile           = $this->makeVisitorProfileSetting();

        $systemSettings = new SystemSettings();

        $this->disableVisitorLog->setIsWritableByCurrentUser(!VisitorLogDisabledSetting::getInstance()->getValue());
        $this->enableAggregatedRealtimeReports->setIsWritableByCurrentUser(!$systemSettings->enableAggregatedRealtimeReports->getValue());
        $this->disableVisitorProfile->setIsWritableByCurrentUser(!$systemSettings->disableVisitorProfile->getValue());
    }

    private function makeVisitorLogSetting(): MeasurableSetting
    {
        $setting = VisitorLogDisabledSetting::getMeasurableSetting($this->idSite);
        $setting->setConfigureCallback(function (FieldConfig $field) {
            $field->title = VisitorLogDisabledSetting::getTitle();
            $field->inlineHelp = VisitorLogDisabledSetting::getInlineHelp();
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });

        $this->addSetting($setting);

        return $setting;
    }

    private function makeAggregatedRealtimeReportsSetting(): MeasurableSetting
    {
        $setting = AggregatedRealtimeReportsEnabledSetting::getMeasurableSetting($this->idSite);
        $setting->setConfigureCallback(function (FieldConfig $field) {
            $field->title = AggregatedRealtimeReportsEnabledSetting::getTitle();
            $field->inlineHelp = AggregatedRealtimeReportsEnabledSetting::getInlineHelp();
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->condition = 'disable_visitor_log==1';
        });

        $this->addSetting($setting);

        return $setting;
    }

    private function makeVisitorProfileSetting(): MeasurableSetting
    {
        $defaultValue = false;
        $type = FieldConfig::TYPE_BOOL;

        return $this->makeSetting('disable_visitor_profile', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = Piwik::translate('Live_DisableVisitorProfile');
            $field->inlineHelp = Piwik::translate('Live_DisableVisitorProfileDescription');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->condition = 'disable_visitor_log==0';
        });
    }
}
