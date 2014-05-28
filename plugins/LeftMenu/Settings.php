<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\LeftMenu;

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
        $this->setIntroduction('The left menu plugin will move the reporting menu from the top to the left if enabled. This is especially useful for large screens.');

        $this->createGlobalEnabledSetting();

        $this->createUserEnabledSetting();
    }

    private function createGlobalEnabledSetting()
    {
        $this->globalEnabled = new SystemSetting('globalEnabled', 'Left menu enabled by default for all users');
        $this->globalEnabled->type = static::TYPE_BOOL;
        $this->globalEnabled->description   = 'Defines the system default for all of your users.';
        $this->globalEnabled->inlineHelp    = 'Users are able to disable/enable the left menu independent of the system default';
        $this->globalEnabled->defaultValue  = true;

        $this->addSetting($this->globalEnabled);
    }

    private function createUserEnabledSetting()
    {
        $this->userEnabled = new UserSetting('userEnabled', 'Enable left reporting menu');
        $this->userEnabled->type            = static::TYPE_STRING;
        $this->userEnabled->uiControlType   = static::CONTROL_RADIO;
        $this->userEnabled->availableValues = array('default' => 'System Default', 'yes' => 'Yes', 'no' => 'No');
        $this->userEnabled->inlineHelp      = 'This will enable or disable the left menu only for you and not affect any other users. A Super User can change the default for all users.';

        $this->addSetting($this->userEnabled);
    }

}
