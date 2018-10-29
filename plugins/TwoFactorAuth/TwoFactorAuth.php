<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Session\SessionFingerprint;

class TwoFactorAuth extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Request.dispatch' => array('function' => 'onRequestDispatch', 'after' => true),
            'API.UsersManager.deleteUser.end' => 'deleteBackupCodes',
            'Template.userSettings.afterTokenAuth' => 'render2FaUserSettings'
        );
    }

    public function deleteBackupCodes($login)
    {
        $model = new Model();
        if (Piwik::hasUserSuperUserAccess() && !$model->userExists($login)) {
            // we delete only if the deletion was really successful
            $dao = StaticContainer::get('Piwik\Plugins\TwoFactorAuth\Dao\BackupCodeDao');
            $dao->deleteAllBackupCodesForLogin($login);
        }
    }

    public function render2FaUserSettings(&$out)
    {
        if (Piwik::isUserIsAnonymous() || !Piwik::isUserHasSomeViewAccess()) {
            return;
        }

        $content = FrontController::getInstance()->dispatch('TwoFactorAuth', 'userSettings');
        if (!empty($content)) {
            $out .= $content;
        }
    }

    public function onRequestDispatch(&$module, &$action, $parameters)
    {
        if (Piwik::isUserIsAnonymous() || !Piwik::isUserHasSomeViewAccess()) {
            return;
        }

        $isUsing2FA = Piwik::isUserUsingTwoFactorAuthentication();
        if ($isUsing2FA && Request::isRootRequestApiRequest()) {
            $sessionFingerprint = new SessionFingerprint();
            if (!$sessionFingerprint->hasVerifiedTwoFactor()) {
                $module = 'TwoFactorAuth';
                $action = 'twoFactorAuth';
            }
        } elseif (!$isUsing2FA) {
            $settings = StaticContainer::get('\Piwik\Plugins\TwoFactorAuth\SystemSettings');
            if ($settings->twoFactorAuthRequired->getValue()) {
                $module = 'TwoFactorAuth';
                $action = 'setupTwoFactorAuth';
            }
        }
    }

}
