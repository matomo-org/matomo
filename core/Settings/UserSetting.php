<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings;

use Piwik\Common;
use Piwik\Piwik;

/**
 * Describes a per user setting. Each user will be able to change this setting for themselves,
 * but not for other users.
 *
 *
 * @api
 */
class UserSetting extends Setting
{
    private $userLogin = null;

    /**
     * Null while not initialized, bool otherwise.
     * @var null|bool
     */
    private $hasReadAndWritePermission = null;

    /**
     * Constructor.
     *
     * @param string $name The setting's persisted name.
     * @param string $title The setting's display name.
     * @param null|string $userLogin The user this setting applies to. Will default to the current user login.
     */
    public function __construct($name, $title, $userLogin = null)
    {
        parent::__construct($name, $title);

        $this->setUserLogin($userLogin);
    }

    /**
     * Returns `true` if this setting can be displayed for the current user, `false` if otherwise.
     *
     * @return bool
     */
    public function isReadableByCurrentUser()
    {
        return $this->isWritableByCurrentUser();
    }

    /**
     * Returns `true` if this setting can be displayed for the current user, `false` if otherwise.
     *
     * @return bool
     */
    public function isWritableByCurrentUser()
    {
        if (isset($this->hasReadAndWritePermission)) {
            return $this->hasReadAndWritePermission;
        }

        $this->hasReadAndWritePermission = Piwik::isUserHasSomeViewAccess();

        return $this->hasReadAndWritePermission;
    }

    /**
     * Returns the display order. User settings are displayed after system settings.
     *
     * @return int
     */
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
     * Sets the name of the user this setting will be set for.
     *
     * @param $userLogin
     * @throws \Exception If the current user does not have permission to set the setting value
     *                    of `$userLogin`.
     */
    public function setUserLogin($userLogin)
    {
        if (!empty($userLogin) && !Piwik::hasUserSuperUserAccessOrIsTheUser($userLogin)) {
            throw new \Exception('You do not have the permission to read the settings of a different user');
        }

        $this->userLogin = $userLogin;
        $this->key       = $this->buildUserSettingName($this->name, $userLogin);
    }

    /**
     * Unsets all settings for a user. The settings will be removed from the database. Used when
     * a user is deleted.
     *
     * @param string $userLogin
     * @throws \Exception If the `$userLogin` is empty.
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
                    $setting->removeValue();
                }
            }

            $pluginSettings->save();
        }
    }
}
