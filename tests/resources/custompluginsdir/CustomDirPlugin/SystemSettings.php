<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDirPlugin;

use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Validators\NotEmpty;

class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $custom;

    protected function init()
    {
        $this->custom = $this->createMetricSetting();
    }

    private function createMetricSetting()
    {
        return $this->makeSetting('custom', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Custom setting';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'Enter some custom text here';
            $field->validators[] = new NotEmpty();
        });
    }
}
