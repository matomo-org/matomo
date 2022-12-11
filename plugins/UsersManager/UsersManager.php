<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UsersManager;

use Exception;
use Piwik\Access\Role\Admin;
use Piwik\Access\Role\Write;
use Piwik\API\Request;
use Piwik\Config;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreHome\SystemSummary;
use Piwik\SettingsPiwik;

/**
 * Manage Piwik users
 *
 */
class UsersManager extends \Piwik\Plugin
{
    const PASSWORD_MIN_LENGTH = 6;
    const PASSWORD_MAX_LENGTH = 200;

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return [
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'SitesManager.deleteSite.end'            => 'deleteSite',
            'Tracker.Cache.getSiteAttributes'        => 'recordAdminUsersInCache',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Platform.initialized'                   => 'onPlatformInitialized',
            'System.addSystemSummaryItems'           => 'addSystemSummaryItems',
        ];
    }

    public static function isUsersAdminEnabled()
    {
        return (bool)Config::getInstance()->General['enable_users_admin'];
    }

    public static function dieIfUsersAdminIsDisabled()
    {
        Piwik::checkUserIsNotAnonymous();
        if (!self::isUsersAdminEnabled()) {
            throw new \Exception('Creating, updating, and deleting users has been disabled.');
        }
    }

    public function addSystemSummaryItems(&$systemSummary)
    {
        if (!self::isUsersAdminEnabled()) {
            return;
        }

        $userLogins = Request::processRequest('UsersManager.getUsersLogin', array('filter_limit' => '-1'));

        $numUsers = count($userLogins);
        if (in_array('anonymous', $userLogins)) {
            $numUsers--;
        }

        $systemSummary[] = new SystemSummary\Item($key = 'users', Piwik::translate('General_NUsers', $numUsers),
            $value = null, array('module' => 'UsersManager', 'action' => 'index'), $icon = 'icon-user', $order = 5);
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
        $model = new Model();
        $logins = $model->getUsersLoginWithSiteAccess($idSite, Admin::ID);
        $writeLogins = $model->getUsersLoginWithSiteAccess($idSite, Write::ID);
        $logins = array_merge($logins, $writeLogins);

        $token_auths = $model->getAllHashedTokensForLogins($logins);

        $attributes['tracking_token_auth'] = array();

        if (!empty($token_auths)) {
            foreach ($token_auths as $token_auth) {
                $attributes['tracking_token_auth'][] = self::hashTrackingToken($token_auth, $idSite);
            }
        }
    }

    public static function hashTrackingToken($tokenAuth, $idSite)
    {
        return sha1($idSite.$tokenAuth.SettingsPiwik::getSalt());
    }

    /**
     * Delete user preferences associated with a particular site
     */
    public function deleteSite($idSite)
    {
        Option::deleteLike('%\_'.API::PREFERENCE_DEFAULT_REPORT, $idSite);
    }

    /**
     * Get CSS files
     */
    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/UsersManager/stylesheets/usersManager.less";

        $stylesheets[] = "plugins/UsersManager/vue/src/UsersManager/UsersManager.less";
        $stylesheets[] = "plugins/UsersManager/vue/src/PagedUsersList/PagedUsersList.less";
        $stylesheets[] = "plugins/UsersManager/vue/src/UserEditForm/UserEditForm.less";
        $stylesheets[] = "plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.less";
        $stylesheets[] = "plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.less";
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

        return $l >= self::PASSWORD_MIN_LENGTH;
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
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidPassword',
                array(self::PASSWORD_MIN_LENGTH)));
        }
        if (mb_strlen($password) > self::PASSWORD_MAX_LENGTH) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidPasswordTooLong',
                array(self::PASSWORD_MAX_LENGTH)));
        }
    }

    public static function getPasswordHash($password)
    {
        if (SettingsPiwik::isUserCredentialsSanityCheckEnabled()) {
            self::checkBasicPasswordStrength($password);
        }
        // if change here, should also edit the installation process
        // to change how the root pwd is saved in the config file
        return md5($password);
    }

    public static function checkBasicPasswordStrength($password)
    {
        $ex = new \Exception('This password is too weak, please supply another value or reset it.');

        $numDistinctCharacters = strlen(count_chars($password, 3));
        if ($numDistinctCharacters < 2) {
            throw $ex;
        }

        if (strlen($password) < 6) {
            throw $ex;
        }
    }

    /**
     * Checks the password hash length. Used as a sanity check.
     *
     * @param string $passwordHash The password hash to check.
     * @param string $exceptionMessage Message of the exception thrown.
     * @throws Exception if the password hash length is incorrect.
     */
    public static function checkPasswordHash($passwordHash, $exceptionMessage)
    {
        if (strlen($passwordHash) != 32 || !ctype_xdigit($passwordHash)) {  // MD5 hash length
            throw new Exception($exceptionMessage);
        }
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "General_OrCancel";
        $translationKeys[] = "General_Save";
        $translationKeys[] = "General_Done";
        $translationKeys[] = "General_Pagination";
        $translationKeys[] = "General_PleaseTryAgain";
        $translationKeys[] = "General_Username";
        $translationKeys[] = "General_Password";
        $translationKeys[] = "UsersManager_DeleteConfirm";
        $translationKeys[] = "UsersManager_ConfirmGrantSuperUserAccess";
        $translationKeys[] = "UsersManager_ConfirmProhibitOtherUsersSuperUserAccess";
        $translationKeys[] = "UsersManager_ConfirmProhibitMySuperUserAccess";
        $translationKeys[] = "UsersManager_ExceptionUserHasViewAccessAlready";
        $translationKeys[] = "UsersManager_ExceptionNoValueForUsernameOrEmail";
        $translationKeys[] = "UsersManager_GiveUserAccess";
        $translationKeys[] = "UsersManager_PrivAdmin";
        $translationKeys[] = "UsersManager_PrivView";
        $translationKeys[] = "UsersManager_RemoveUserAccess";
        $translationKeys[] = "UsersManager_ConfirmWithPassword";
        $translationKeys[] = "UsersManager_YourCurrentPassword";
        $translationKeys[] = "UsersManager_UserHasPermission";
        $translationKeys[] = "UsersManager_UserHasNoPermission";
        $translationKeys[] = "UsersManager_PrivNone";
        $translationKeys[] = "UsersManager_ManageUsers";
        $translationKeys[] = "UsersManager_ManageUsersDesc";
        $translationKeys[] = "UsersManager_ManageUsersAdminDesc";
        $translationKeys[] = 'Mobile_NavigationBack';
        $translationKeys[] = 'UsersManager_AddExistingUser';
        $translationKeys[] = 'UsersManager_EnterUsernameOrEmail';
        $translationKeys[] = 'UsersManager_NoAccessWarning';
        $translationKeys[] = 'UsersManager_BulkActions';
        $translationKeys[] = 'UsersManager_SetPermission';
        $translationKeys[] = 'UsersManager_RolesHelp';
        $translationKeys[] = 'UsersManager_Role';
        $translationKeys[] = 'UsersManager_2FA';
        $translationKeys[] = 'UsersManager_UsesTwoFactorAuthentication';
        $translationKeys[] = 'General_Actions';
        $translationKeys[] = 'UsersManager_TheDisplayedWebsitesAreSelected';
        $translationKeys[] = 'UsersManager_ClickToSelectAll';
        $translationKeys[] = 'UsersManager_AllWebsitesAreSelected';
        $translationKeys[] = 'UsersManager_ClickToSelectDisplayedWebsites';
        $translationKeys[] = 'UsersManager_DeletePermConfirmSingle';
        $translationKeys[] = 'UsersManager_DeletePermConfirmMultiple';
        $translationKeys[] = 'UsersManager_ChangePermToSiteConfirmSingle';
        $translationKeys[] = 'UsersManager_ChangePermToSiteConfirmMultiple';
        $translationKeys[] = 'UsersManager_BasicInformation';
        $translationKeys[] = 'UsersManager_Permissions';
        $translationKeys[] = 'UsersManager_RemovePermissions';
        $translationKeys[] = 'UsersManager_FirstSiteInlineHelp';
        $translationKeys[] = 'UsersManager_SuperUsersPermissionsNotice';
        $translationKeys[] = 'UsersManager_SuperUserIntro1';
        $translationKeys[] = 'UsersManager_SuperUserIntro2';
        $translationKeys[] = 'UsersManager_HasSuperUserAccess';
        $translationKeys[] = 'UsersManager_AreYouSure';
        $translationKeys[] = 'UsersManager_RemoveSuperuserAccessConfirm';
        $translationKeys[] = 'UsersManager_AddSuperuserAccessConfirm';
        $translationKeys[] = 'UsersManager_UserSearch';
        $translationKeys[] = 'UsersManager_DeleteUsers';
        $translationKeys[] = 'UsersManager_FilterByAccess';
        $translationKeys[] = 'UsersManager_Username';
        $translationKeys[] = 'UsersManager_RoleFor';
        $translationKeys[] = 'UsersManager_TheDisplayedUsersAreSelected';
        $translationKeys[] = 'UsersManager_AllUsersAreSelected';
        $translationKeys[] = 'UsersManager_ClickToSelectDisplayedUsers';
        $translationKeys[] = 'UsersManager_DeleteUserConfirmSingle';
        $translationKeys[] = 'UsersManager_DeleteUserConfirmMultiple';
        $translationKeys[] = 'UsersManager_DeleteUserPermConfirmSingle';
        $translationKeys[] = 'UsersManager_DeleteUserPermConfirmMultiple';
        $translationKeys[] = 'UsersManager_ResetTwoFactorAuthentication';
        $translationKeys[] = 'UsersManager_ResetTwoFactorAuthenticationInfo';
        $translationKeys[] = 'UsersManager_TwoFactorAuthentication';
        $translationKeys[] = 'UsersManager_InviteNewUser';
        $translationKeys[] = 'UsersManager_EditUser';
        $translationKeys[] = 'UsersManager_InviteUser';
        $translationKeys[] = 'UsersManager_SaveBasicInfo';
        $translationKeys[] = 'UsersManager_Email';
        $translationKeys[] = 'UsersManager_LastSeen';
        $translationKeys[] = 'UsersManager_SuperUserAccess';
        $translationKeys[] = 'UsersManager_AreYouSureChangeDetails';
        $translationKeys[] = 'UsersManager_AnonymousUserRoleChangeWarning';
        $translationKeys[] = 'General_Warning';
        $translationKeys[] = 'General_Add';
        $translationKeys[] = 'General_Note';
        $translationKeys[] = 'General_Yes';
        $translationKeys[] = 'UsersManager_FilterByWebsite';
        $translationKeys[] = 'UsersManager_GiveAccessToAll';
        $translationKeys[] = 'UsersManager_OrManageIndividually';
        $translationKeys[] = 'UsersManager_ChangePermToAllSitesConfirm';
        $translationKeys[] = 'UsersManager_ChangePermToAllSitesConfirm2';
        $translationKeys[] = 'UsersManager_CapabilitiesHelp';
        $translationKeys[] = 'UsersManager_Capabilities';
        $translationKeys[] = 'UsersManager_AreYouSureAddCapability';
        $translationKeys[] = 'UsersManager_AreYouSureRemoveCapability';
        $translationKeys[] = 'UsersManager_IncludedInUsersRole';
        $translationKeys[] = 'UsersManager_NewsletterSignupFailureMessage';
        $translationKeys[] = 'UsersManager_NewsletterSignupSuccessMessage';
        $translationKeys[] = 'UsersManager_FirstWebsitePermission';
        $translationKeys[] = 'UsersManager_YourUsernameCannotBeChanged';
        $translationKeys[] = 'General_Language';
        $translationKeys[] = 'LanguagesManager_AboutPiwikTranslations';
        $translationKeys[] = 'General_TimeFormat';
        $translationKeys[] = 'UsersManager_ReportToLoadByDefault';
        $translationKeys[] = 'UsersManager_ReportDateToLoadByDefault';
        $translationKeys[] = 'UsersManager_NewsletterSignupTitle';
        $translationKeys[] = 'UsersManager_NewsletterSignupMessage';
        $translationKeys[] = 'UsersManager_WhenUsersAreNotLoggedInAndVisitPiwikTheyShouldAccess';
        $translationKeys[] = 'UsersManager_ForAnonymousUsersReportDateToLoadByDefault';
        $translationKeys[] = 'UsersManager_InviteSuccessNotification';
        $translationKeys[] = 'UsersManager_Status';
        $translationKeys[] = 'UsersManager_Active';
        $translationKeys[] = 'UsersManager_Pending';
        $translationKeys[] = 'UsersManager_Expired';
        $translationKeys[] = 'UsersManager_Decline';
        $translationKeys[] = 'UsersManager_InviteSuccess';
        $translationKeys[] = 'UsersManager_InviteDayLeft';
        $translationKeys[] = 'UsersManager_FilterByStatus';
        $translationKeys[] = 'UsersManager_ExpiredInviteAutomaticallyRemoved';
        $translationKeys[] = 'UsersManager_DeleteSuccess';
        $translationKeys[] = 'UsersManager_DeleteNotSuccessful';
        $translationKeys[] = 'UsersManager_InviteConfirmMessage';
        $translationKeys[] = 'UsersManager_ResendInvite';
        $translationKeys[] = 'UsersManager_InvitationSent';
        $translationKeys[] = 'UsersManager_SendInvite';
        $translationKeys[] = 'UsersManager_CopyLink';
        $translationKeys[] = 'UsersManager_LinkCopied';
        $translationKeys[] = "UsersManager_AddNewUser";
        $translationKeys[] = 'UsersManager_BackToUser';
        $translationKeys[] = 'UsersManager_InviteActionNotes';
        $translationKeys[] = 'UsersManager_CopyDenied';
        $translationKeys[] = 'UsersManager_CopyDeniedHints';

    }
}
