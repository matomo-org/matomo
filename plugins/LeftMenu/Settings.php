<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\LeftMenu;

use Piwik\Piwik;
use Piwik\Settings\SystemSetting;
use Piwik\Settings\UserSetting;

/**
 * Defines Settings for LeftMenu.
 */
class Settings extends \Piwik\Plugin\Settings
{
    /** @var SystemSetting */
    public $globalEnabled;

    /** @var UserSetting */
    public $userEnabled;

    protected function init()
    {
        $this->setIntroduction($this->t('SettingsIntroduction'));

        $this->createGlobalEnabledSetting();

        $this->createUserEnabledSetting();
    }

    private function createGlobalEnabledSetting()
    {
        $this->globalEnabled = new SystemSetting('globalEnabled', $this->t('GlobalSettingTitle'));
        $this->globalEnabled->type = static::TYPE_BOOL;
        $this->globalEnabled->description  = $this->t('GlobalSettingDescription');
        $this->globalEnabled->inlineHelp   = $this->t('GlobalSettingInlineHelp');
        $this->globalEnabled->defaultValue = false;
        $this->globalEnabled->readableByCurrentUser = true;

        $this->addSetting($this->globalEnabled);
    }

    private function createUserEnabledSetting()
    {
        $this->userEnabled = new UserSetting('userEnabled', $this->t('UserSettingTitle'));
        $this->userEnabled->type            = static::TYPE_STRING;
        $this->userEnabled->uiControlType   = static::CONTROL_RADIO;
        $this->userEnabled->defaultValue    = 'system';
        $this->userEnabled->inlineHelp      = $this->t('UserSettingInlineHelp');
        $this->userEnabled->availableValues = array(
            'system' => Piwik::translate('General_Default'),
            'yes'    => Piwik::translate('General_Yes'),
            'no'     => Piwik::translate('General_No')
        );

        $this->addSetting($this->userEnabled);
    }

    private function t($key)
    {
        return Piwik::translate('LeftMenu_' . $key);
    }

}
