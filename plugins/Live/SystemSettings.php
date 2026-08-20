<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live;

use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Plugins\Live\Settings\AggregatedRealtimeReportsEnabled as AggregatedRealtimeReportsEnabledSetting;
use Piwik\Plugins\Live\Settings\VisitorLogDisabled as VisitorLogDisabledSetting;

class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var SystemSetting|null */
    public $disableVisitorLog;

    /** @var SystemSetting|null */
    public $disableVisitorProfile;

    /** @var SystemSetting|null */
    public $enableAggregatedRealtimeReports;

    protected function init()
    {
        $this->disableVisitorLog               = $this->makeVisitorLogSetting();
        $this->enableAggregatedRealtimeReports = $this->makeAggregatedRealtimeReportsSetting();
        $this->disableVisitorProfile           = $this->makeVisitorProfileSetting();
    }

    private function makeVisitorLogSetting(): SystemSetting
    {
        $setting = VisitorLogDisabledSetting::getSystemSetting();
        $setting->setConfigureCallback(function (FieldConfig $field) {
            // the checkbox keeps its imperative label; getTitle() is the state-phrased compliance dashboard title
            $field->title = Piwik::translate('Live_DisableVisitsLogAndProfile');
            $field->inlineHelp = VisitorLogDisabledSetting::getInlineHelp();
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });

        $this->addSetting($setting);
        return $setting;
    }

    private function makeAggregatedRealtimeReportsSetting(): SystemSetting
    {
        $setting = AggregatedRealtimeReportsEnabledSetting::getSystemSetting();
        $setting->setConfigureCallback(function (FieldConfig $field) {
            $field->title = AggregatedRealtimeReportsEnabledSetting::getTitle();
            $field->inlineHelp = AggregatedRealtimeReportsEnabledSetting::getInlineHelp();
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });

        // Only expose the setting while the Visits log is disabled - it is irrelevant otherwise.
        // This is gated server-side rather than with a client-side "disable_visitor_log==1" condition
        // because a non-writable disable_visitor_log (set via config.ini.php or forced globally) is
        // omitted from the settings payload, which would leave the condition unresolved and hide this
        // setting in exactly the state it is meant for.
        if (VisitorLogDisabledSetting::getInstance()->getValue()) {
            $this->addSetting($setting);
        }

        return $setting;
    }

    private function makeVisitorProfileSetting(): SystemSetting
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
