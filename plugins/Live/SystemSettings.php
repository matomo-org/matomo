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
use Piwik\Settings\Plugin\SystemSetting;

class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var SystemSetting|null */
    public $activateVisitorLog;

    /** @var SystemSetting|null */
    public $activateVisitorProfile;

    protected function init()
    {
        $this->activateVisitorLog     = $this->makeVisitorLogSetting();
        $this->activateVisitorProfile = $this->makeVisitorProfileSetting();
    }

    private function makeVisitorLogSetting(): SystemSetting
    {
        $defaultValue = true;
        $type = FieldConfig::TYPE_BOOL;

        return $this->makeSetting('activate_visitor_log', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = Piwik::translate('Live_EnableVisitsLog');
            $field->inlineHelp = '';
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });
    }

    private function makeVisitorProfileSetting(): SystemSetting
    {
        $defaultValue = true;
        $type = FieldConfig::TYPE_BOOL;

        return $this->makeSetting('activate_visitor_profile', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = Piwik::translate('Live_EnableVisitorProfile');
            $field->inlineHelp = '';
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });
    }
}