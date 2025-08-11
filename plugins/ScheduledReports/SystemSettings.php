<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports;

use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $slackOauthToken;

    protected function init()
    {
        // System setting --> allows selection of a single value
        $this->slackOauthToken = $this->createSlackOauthTokenSetting();
    }

    private function createSlackOauthTokenSetting()
    {
        return $this->makeSetting('slackOauthToken', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Slack Oauth Token';
            $field->uiControl = FieldConfig::UI_CONTROL_PASSWORD;
            $field->description = 'Enter your Slack Oauth Token';
        });
    }
}
