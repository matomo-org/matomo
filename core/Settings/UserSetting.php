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
 * Per user setting. Each user will be able to change this setting but each user can set a different value. That means
 * a changed value does not effect any other users.
 *
 * @package Piwik
 * @subpackage Settings
 *
 * @api
 */
class UserSetting extends Setting
{
    private $userLogin = null;

    /**
     * @param string $name
     * @param string $title
     * @param null|string $userLogin  Defaults to the current user login.
     */
    public function __construct($name, $title, $userLogin = null)
    {
        parent::__construct($name, $title);

        $this->setUserLogin($userLogin);

        $this->displayedForCurrentUser = !Piwik::isUserIsAnonymous() && Piwik::isUserHasSomeViewAccess();
    }

    public function getOrder()
    {
        return 60;
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

    /**
     * Sets (overwrites) the userLogin.
     *
     * @param $userLogin
     *
     * @throws \Exception In case you set a userLogin that is not your userLogin and you are not the superUser.
     */
    public function setUserLogin($userLogin)
    {
        if (!empty($userLogin) && !Piwik::isUserIsSuperUserOrTheUser($userLogin)) {
            throw new \Exception('You do not have the permission to read the settings of a different user');
        }

        $this->userLogin = $userLogin;
        $this->key       = $this->buildUserSettingName($this->name, $userLogin);
    }

    /**
     * Remove all stored settings of the given userLogin. This is important to cleanup all settings for a user once he
     * is deleted. Otherwise a user could register with the same name afterwards and see the previous user's settings.
     *
     * @param string $userLogin
     *
     * @throws \Exception In case the userLogin is empty.
     */
    public static function removeAllUserSettingsForUser($userLogin)
    {
        if (empty($userLogin)) {
            throw new \Exception('No userLogin specified');
        }

        $pluginsSettings = Manager::getAllPluginSettings();

        foreach ($pluginsSettings as $pluginSettings) {

            $settings = $pluginSettings->getSettings();

            foreach ($settings as $setting) {

                if ($setting instanceof UserSetting) {
                    $setting->setUserLogin($userLogin);
                    $pluginSettings->removeSettingValue($setting);
                }

            }

            $pluginSettings->save();
        }
    }

}
