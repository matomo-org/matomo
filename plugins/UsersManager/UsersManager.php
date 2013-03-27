<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UsersManager
 */

/**
 * Manage Piwik users
 *
 * @package Piwik_UsersManager
 */
class Piwik_UsersManager extends Piwik_Plugin
{
    const PASSWORD_MIN_LENGTH = 6;
    const PASSWORD_MAX_LENGTH = 26;

    /**
     * Plugin information
     *
     * @see Piwik_Plugin
     *
     * @return array
     */
    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('UsersManager_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );

        return $info;
    }

    /**
     * Get list of hooks to register.
     *
     * @see Piwik_PluginsManager.loadPlugin()
     *
     * @return array
     */
    function getListHooksRegistered()
    {
        return array(
            'AdminMenu.add'                 => 'addMenu',
            'AssetManager.getJsFiles'       => 'getJsFiles',
            'SitesManager.deleteSite'       => 'deleteSite',
            'Common.fetchWebsiteAttributes' => 'recordAdminUsersInCache',
        );
    }


    /**
     * Hooks when a website tracker cache is flushed (website/user updated, cache deleted, or empty cache)
     * Will record in the tracker config file the list of Admin token_auth for this website. This
     * will be used when the Tracking API is used with setIp(), setForceDateTime(), setVisitorId(), etc.
     *
     * @param Piwik_Event_Notification $notification  notification object
     * @return void
     */
    function recordAdminUsersInCache($notification)
    {
        $idSite = $notification->getNotificationInfo();
        // add the 'hosts' entry in the website array
        $users = Piwik_UsersManager_API::getInstance()->getUsersWithSiteAccess($idSite, 'admin');

        $tokens = array();
        foreach ($users as $user) {
            $tokens[] = $user['token_auth'];
        }
        $array =& $notification->getNotificationObject();
        $array['admin_token_auth'] = $tokens;
    }

    /**
     * Delete user preferences associated with a particular site
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    function deleteSite($notification)
    {
        $idSite = & $notification->getNotificationObject();

        Piwik_Option::getInstance()->deleteLike('%\_' . Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT, $idSite);
    }

    /**
     * Return list of plug-in specific JavaScript files to be imported by the asset manager
     *
     * @see Piwik_AssetManager
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();

        $jsFiles[] = "plugins/UsersManager/templates/UsersManager.js";
        $jsFiles[] = "plugins/UsersManager/templates/userSettings.js";
    }

    /**
     * Add admin menu items
     */
    function addMenu()
    {
        Piwik_AddAdminSubMenu('CoreAdminHome_MenuManage', 'UsersManager_MenuUsers',
            array('module' => 'UsersManager', 'action' => 'index'),
            Piwik::isUserHasSomeAdminAccess(),
            $order = 2);
        Piwik_AddAdminSubMenu('CoreAdminHome_MenuManage', 'UsersManager_MenuUserSettings',
            array('module' => 'UsersManager', 'action' => 'userSettings'),
            Piwik::isUserHasSomeViewAccess(),
            $order = 3);
    }

    /**
     * Returns true if the password is complex enough (at least 6 characters and max 26 characters)
     *
     * @param string email
     * @return bool
     */
    public static function isValidPasswordString($input)
    {
        if (!Piwik::isChecksEnabled()
            && !empty($input)
        ) {
            return true;
        }
        $l = strlen($input);
        return $l >= self::PASSWORD_MIN_LENGTH && $l <= self::PASSWORD_MAX_LENGTH;
    }

    public static function checkPassword($password)
    {
        if (!self::isValidPasswordString($password)) {
            throw new Exception(Piwik_TranslateException('UsersManager_ExceptionInvalidPassword', array(self::PASSWORD_MIN_LENGTH,
                                                                                                        self::PASSWORD_MAX_LENGTH)));
        }
    }

    public static function getPasswordHash($password)
    {
        // if change here, should also edit the installation process
        // to change how the root pwd is saved in the config file
        return md5($password);
    }
}
