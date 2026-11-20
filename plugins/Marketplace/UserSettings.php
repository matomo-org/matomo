<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Marketplace;

use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\UserSetting;

class UserSettings extends \Piwik\Settings\Plugin\UserSettings
{
    public const NOTIFY_ON_PLUGIN_REQUEST = 'notifyOnPluginRequest';

    public UserSetting $notifyOnPluginRequest;

    protected function init()
    {
        $this->title = Piwik::translate('Marketplace_PluginRequestsSettingsTitle');

        $this->notifyOnPluginRequest = $this->makeSetting(
            self::NOTIFY_ON_PLUGIN_REQUEST,
            true,
            FieldConfig::TYPE_BOOL,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('Marketplace_NotifyOnPluginRequest');
                $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
                $field->inlineHelp = Piwik::translate('Marketplace_NotifyOnPluginRequestHelp');
            }
        );

        // only super users are sent plugin request emails
        if (!Piwik::hasUserSuperUserAccess()) {
            $this->notifyOnPluginRequest->setIsWritableByCurrentUser(false);
        }
    }
}
