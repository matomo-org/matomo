<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
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
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'API.UsersManager.deleteUser.end' => 'deleteRecoveryCodes',
            'API.UsersManager.getTokenAuth.end' => 'onApiGetTokenAuth',
            // 'UsersManager.API.verifyGetTokenAuthIdentity' => 'onApiGetTokenAuth',
            'Template.userSettings.afterTokenAuth' => 'render2FaUserSettings',
            'Login.authenticate.processSuccessfulSession.end' => 'onSuccessfulSession'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/TwoFactorAuth/stylesheets/twofactorauth.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/TwoFactorAuth/angularjs/setuptwofactor/setuptwofactor.controller.js";
    }

    public function deleteRecoveryCodes($returnedValue, $params)
    {
        $model = new Model();
        if (!empty($params['parameters']['userLogin'])
            && Piwik::hasUserSuperUserAccess()
            && !$model->userExists($params['parameters']['userLogin'])) {
            // we delete only if the deletion was really successful
            $dao = StaticContainer::get(RecoveryCodeDao::class);
            $dao->deleteAllRecoveryCodesForLogin($params['parameters']['userLogin']);
        }
    }

    public function render2FaUserSettings(&$out)
    {
        if (Piwik::isUserIsAnonymous()) {
            return;
        }

        $content = FrontController::getInstance()->dispatch('TwoFactorAuth', 'userSettings');
        if (!empty($content)) {
            $out .= $content;
        }
    }

    public function onSuccessfulSession($login)
    {
        if (Piwik::getModule() === 'Login' && Piwik::getAction() === 'logme' && $login) {
            // we allow user to send an "authCode" along logme to directly log in... if not, user will see the
            // auth code verification screen after logme
            $authCode = Common::getRequestVar('authCode', '', 'string');

            if ($authCode) {
                $twoFa = StaticContainer::get(TwoFactorAuthentication::class);
                if ($twoFa->isUserUsingTwoFactorAuthentication($login)
                    && $twoFa->validateAuthCode($login, $authCode)) {
                    $sessionFingerprint = new SessionFingerprint();
                    $sessionFingerprint->setTwoFactorAuthenticationVerified();
                }
            }
        }
    }

    public function onApiGetTokenAuth($returnedValue, $params)
    {
        if (!empty($returnedValue)) {
            $login = $params['parameters']['userLogin'];
            $authCode = Common::getRequestVar('authCode', '', 'string');
            $twoFa = StaticContainer::get(TwoFactorAuthentication::class);

            $model = new Model();
            if ($twoFa->isUserUsingTwoFactorAuthentication($login)
                && $model->getUserByTokenAuth($returnedValue)) {
                // we only return an error when the login/password combo was correct. otherwise you could brute force
                // auth tokens
                if (!$authCode) {
                    http_response_code(401);
                    throw new \Exception('Please specify two-factor authentication code.');
                }
                if (!$twoFa->validateAuthCode($login, $authCode)) {
                    http_response_code(401);
                    throw new \Exception('Please enter correct two-factor authentication code.');
                }
            } else if ($twoFa->isUserRequiredToHaveTwoFactorEnabled()
                        && !$twoFa->isUserUsingTwoFactorAuthentication($login)) {
                throw new \Exception('You are required to set up two-factor authentication. Please log in to your account.');
            }
        }
    }

    public function onRequestDispatch(&$module, &$action, $parameters)
    {
        if (Piwik::isUserIsAnonymous()) {
            return;
        }

        if ($module === 'Proxy') {
            return;
        }

        if ($module === 'TwoFactorAuth' && $action === 'showQrCode') {
            return;
        }

        $twoFa = StaticContainer::get(TwoFactorAuthentication::class);

        $isUsing2FA = $twoFa->isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin());
        if ($isUsing2FA && !Request::isRootRequestApiRequest()) {
            $sessionFingerprint = new SessionFingerprint();
            if (!$sessionFingerprint->hasVerifiedTwoFactor()) {
                $module = 'TwoFactorAuth';
                $action = 'loginTwoFactorAuth';
            }
        } elseif (!$isUsing2FA && $twoFa->isUserRequiredToHaveTwoFactorEnabled()) {
            $module = 'TwoFactorAuth';
            $action = 'onLoginSetupTwoFactorAuth';
        }
    }

}
