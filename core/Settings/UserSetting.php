<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Settings;
use Piwik\Common;
use Piwik\Piwik;

/**
 * Per User Setting.
 *
 * @package Piwik
 * @subpackage Settings
 */
class UserSetting extends Setting
{
    private $userLogin = null;

    public function __construct($name, $title, $userLogin = null)
    {
        parent::__construct($name, $title);

        $this->setUserLogin($userLogin);

        $this->displayedForCurrentUser = !Piwik::isUserIsAnonymous();
    }

    public function hasKey($key, $userLogin = null)
    {
        $thisKey = $this->buildUserSettingName($key, $userLogin);

        return ($key == $thisKey);
    }

    private function buildUserSettingName($name, $userLogin = null)
    {
        if (empty($userLogin)) {
            $userLogin = Piwik::getCurrentUserLogin();
        }

        // the asterisk tag is indeed important here and better than an underscore. Imagine a plugin has the settings
        // "api_password" and "api". A user having the login "_password" could otherwise under circumstances change the
        // setting for "api" although he is not allowed to. It is not so important at the moment because only alNum is
        // currently allowed as a name this might change in the future.
        $appendix = '#' . $userLogin . '#';

        if (Common::stringEndsWith($name, $appendix)) {
            return $name;
        }

        return $name . $appendix;
    }

    public function setUserLogin($userLogin)
    {
        $this->userLogin = $userLogin;
        $this->key       = $this->buildUserSettingName($this->name, $userLogin);
    }

    public static function removeAllUserSettingsForUser($userLogin)
    {
        $pluginsSettings = Manager::getAllPluginSettings();

        foreach ($pluginsSettings as $pluginSettings) {

            $settings = $pluginSettings->getSettings();

            foreach ($settings as $setting) {

                if ($setting instanceof UserSetting) {
                    $setting->setUserLogin($userLogin);
                    $pluginSettings->removeValue($setting);
                }

            }

            $pluginSettings->save();
        }
    }

}
