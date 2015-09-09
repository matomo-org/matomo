<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Exception;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\SettingsPiwik;

/**
 * Manage Piwik users
 *
 */
class UsersManager extends \Piwik\Plugin
{
    const PASSWORD_MIN_LENGTH = 6;
    const PASSWORD_MAX_LENGTH = 80;

    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'SitesManager.deleteSite.end'            => 'deleteSite',
            'Tracker.Cache.getSiteAttributes'        => 'recordAdminUsersInCache',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Platform.initialized'                   => 'onPlatformInitialized',
            'CronArchive.getTokenAuth'               => 'getCronArchiveTokenAuth'
        );
    }

    public function onPlatformInitialized()
    {
        $lastSeenTimeLogger = new LastSeenTimeLogger();
        $lastSeenTimeLogger->logCurrentUserLastSeenTime();
    }

    /**
     * Hooks when a website tracker cache is flushed (website/user updated, cache deleted, or empty cache)
     * Will record in the tracker config file the list of Admin token_auth for this website. This
     * will be used when the Tracking API is used with setIp(), setForceDateTime(), setVisitorId(), etc.
     *
     * @param $attributes
     * @param $idSite
     * @return void
     */
    public function recordAdminUsersInCache(&$attributes, $idSite)
    {
        // add the 'hosts' entry in the website array
        $users = API::getInstance()->getUsersWithSiteAccess($idSite, 'admin');

        $tokens = array();
        foreach ($users as $user) {
            $tokens[] = $user['token_auth'];
        }

        $attributes['admin_token_auth'] = $tokens;
    }

    public function getCronArchiveTokenAuth(&$tokens)
    {
        $model      = new Model();
        $superUsers = $model->getUsersHavingSuperUserAccess();

        foreach($superUsers as $superUser) {
            $tokens[] = $superUser['token_auth'];
        }
    }

    /**
     * Delete user preferences associated with a particular site
     */
    public function deleteSite($idSite)
    {
        Option::deleteLike('%\_' . API::PREFERENCE_DEFAULT_REPORT, $idSite);
    }

    /**
     * Return list of plug-in specific JavaScript files to be imported by the asset manager
     *
     * @see Piwik\AssetManager
     */
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/UsersManager/javascripts/usersManager.js";
        $jsFiles[] = "plugins/UsersManager/javascripts/usersSettings.js";
    }

    /**
     * Get CSS files
     */
    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/UsersManager/stylesheets/usersManager.less";
    }

    /**
     * Returns true if the password is complex enough (at least 6 characters and max 26 characters)
     *
     * @param $input string
     * @return bool
     */
    public static function isValidPasswordString($input)
    {
        if (!SettingsPiwik::isUserCredentialsSanityCheckEnabled()
            && !empty($input)
        ) {
            return true;
        }

        $l = strlen($input);

        return $l >= self::PASSWORD_MIN_LENGTH && $l <= self::PASSWORD_MAX_LENGTH;
    }

    public static function checkPassword($password)
    {
        /**
         * Triggered before core password validator check password.
         *
         * This event exists for enable option to create custom password validation rules.
         * It can be used to validate password (length, used chars etc) and to notify about checking password.
         *
         * **Example**
         *
         *     Piwik::addAction('UsersManager.checkPassword', function ($password) {
         *         if (strlen($password) < 10) {
         *             throw new Exception('Password is too short.');
         *         }
         *     });
         *
         * @param string $password Checking password in plain text.
         */
        Piwik::postEvent('UsersManager.checkPassword', array($password));

        if (!self::isValidPasswordString($password)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidPassword', array(self::PASSWORD_MIN_LENGTH,
                self::PASSWORD_MAX_LENGTH)));
        }
    }

    public static function getPasswordHash($password)
    {
        // if change here, should also edit the installation process
        // to change how the root pwd is saved in the config file
        return md5($password);
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "General_OrCancel";
        $translationKeys[] = "General_Save";
        $translationKeys[] = "General_Done";
        $translationKeys[] = "UsersManager_DeleteConfirm";
        $translationKeys[] = "UsersManager_ConfirmGrantSuperUserAccess";
        $translationKeys[] = "UsersManager_ConfirmProhibitOtherUsersSuperUserAccess";
        $translationKeys[] = "UsersManager_ConfirmProhibitMySuperUserAccess";
    }
}
