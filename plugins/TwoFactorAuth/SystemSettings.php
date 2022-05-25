<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Url;
use Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication;

class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $twoFactorAuthRequired;

    /** @var Setting */
    public $twoFactorAuthTitle;

    protected function init()
    {
        $this->twoFactorAuthRequired = $this->createRequire2FA();
        $this->twoFactorAuthTitle = $this->create2FATitle();
    }

    private function createRequire2FA()
    {
        $setting = $this->makeSetting('twoFactorAuthRequired', $default = false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('TwoFactorAuth_RequireTwoFAForAll');
            $field->description = Piwik::translate('TwoFactorAuth_RequireTwoFAForAllInformation');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;

            $isWritable = defined('PIWIK_TEST_MODE') || TwoFactorAuthentication::isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin());
            if (!$isWritable) {
                $field->uiControlAttributes = ['disabled' => 'disabled'];
            }
        });

        return $setting;
    }

    private function create2FATitle()
    {
        $default = 'Analytics - ' . Url::getCurrentHost('');
        if (Plugin\Manager::getInstance()->isPluginActivated('WhiteLabel')) {
            $default = 'Matomo ' . $default;
        }
        return $this->makeSetting('twoFactorAuthName', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Two-factor authentication title';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'The name of the title to display that will be displayed in the Authenticator app.';
        });
    }
}
