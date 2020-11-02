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
    public $disableVisitorLog;

    /** @var SystemSetting|null */
    public $disableVisitorProfile;

    protected function init()
    {
        $this->disableVisitorLog     = $this->makeVisitorLogSetting();
        $this->disableVisitorProfile = $this->makeVisitorProfileSetting();
    }

    private function makeVisitorLogSetting(): SystemSetting
    {
        $defaultValue = false;
        $type = FieldConfig::TYPE_BOOL;

        return $this->makeSetting('disable_visitor_log', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = Piwik::translate('Live_DisableVisitsLogAndProfile');
            $field->inlineHelp = Piwik::translate('Live_DisableVisitsLogAndProfileDescription');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });
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