<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live;

use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Measurable\MeasurableSetting;

class MeasurableSettings extends \Piwik\Settings\Measurable\MeasurableSettings
{
    /** @var MeasurableSetting|null */
    public $activateVisitorLog;

    /** @var MeasurableSetting|null */
    public $activateVisitorProfile;

    protected function init()
    {
        $this->activateVisitorLog     = $this->makeVisitorLogSetting();
        $this->activateVisitorProfile = $this->makeVisitorProfileSetting();

        $systemSettings = new SystemSettings();

        $this->activateVisitorLog->setIsWritableByCurrentUser($systemSettings->activateVisitorLog->getValue());
        $this->activateVisitorProfile->setIsWritableByCurrentUser($systemSettings->activateVisitorProfile->getValue());
    }

    private function makeVisitorLogSetting(): MeasurableSetting
    {
        $defaultValue = true;
        $type = FieldConfig::TYPE_BOOL;

        return $this->makeSetting('activate_visitor_log', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = Piwik::translate('Live_EnableVisitsLog');
            $field->inlineHelp = '';
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });
    }

    private function makeVisitorProfileSetting(): MeasurableSetting
    {
        $defaultValue = true;
        $type = FieldConfig::TYPE_BOOL;

        return $this->makeSetting('activate_visitor_profile', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = Piwik::translate('Live_EnableVisitorProfile');
            $field->inlineHelp = '';
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->condition = 'activate_visitor_log==1';
        });
    }
}