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
use Piwik\Session;
use Piwik\Session\SessionFingerprint;
use Exception;
use Piwik\SettingsPiwik;

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
            'API.UsersManager.createAppSpecificTokenAuth.end' => 'onCreateAppSpecificTokenAuth',
            'Request.dispatch.end' => array('function' => 'onRequestDispatchEnd', 'after' => true),
            'Template.userSecurity.afterPassword' => 'render2FaUserSettings',
            'Login.authenticate.processSuccessfulSession.end' => 'onSuccessfulSession'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/TwoFactorAuth/stylesheets/twofactorauth.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/TwoFactorAuth/javascripts/twofactorauth.js";
        $jsFiles[] = "plugins/TwoFactorAuth/angularjs/setuptwofactor/setuptwofactor.controller.js";
        $jsFiles[] = "node_modules/qrcodejs2/qrcode.min.js";
    }

    public function deleteRecoveryCodes($returnedValue, $params)
    {
        $model = new Model();
        if (!empty($params['parameters']['userLogin'])
            && !$model->userExists($params['parameters']['userLogin'])) {
            // we delete only if the deletion was really successful
            $dao = StaticContainer::get(RecoveryCodeDao::class);
            $dao->deleteAllRecoveryCodesForLogin($params['parameters']['userLogin']);
        }
    }

    public function render2FaUserSettings(&$out)
    {
        $validator = $this->getValidator();
        if ($validator->canUseTwoFa()) {
            $content = FrontController::getInstance()->dispatch('TwoFactorAuth', 'userSettings');
            if (!empty($content)) {
                $out .= $content;
            }
        }
    }

    public function onSuccessfulSession($login)
    {
        if (Piwik::getModule() === 'Login' && Piwik::getAction() === 'logme' && $login) {
            // we allow user to send an "authCode" along logme to directly log in... if not, user will see the
            // auth code verification screen after logme
            $authCode = Common::getRequestVar('authCode', '', 'string');
            $twoFa = $this->getTwoFa();

            if ($authCode
                && TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($login)
                && $twoFa->validateAuthCode($login, $authCode)) {
                $sessionFingerprint = new SessionFingerprint();
                $sessionFingerprint->setTwoFactorAuthenticationVerified();
            }
        }
    }

    private function getTwoFa()
    {
        return StaticContainer::get(TwoFactorAuthentication::class);
    }

    private function getValidator()
    {
        return StaticContainer::get(Validator::class);
    }

    private function isValidTokenAuth($tokenAuth)
    {
        $model = new Model();
        $user = $model->getUserByTokenAuth($tokenAuth);
        return !empty($user);
    }

    public function onCreateAppSpecificTokenAuth($returnedValue, $params)
    {
        if (!SettingsPiwik::isMatomoInstalled()) {
            return;
        }

        if (!empty($returnedValue) && !empty($params['parameters']['userLogin'])) {
            $login = $params['parameters']['userLogin'];
            $twoFa = $this->getTwoFa();

            if (TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($login) && $this->isValidTokenAuth($returnedValue)) {
                $authCode = Common::getRequestVar('authCode', '', 'string');
                // we only return an error when the login/password combo was correct. otherwise you could brute force
                // auth tokens
                if (!$authCode) {
                    http_response_code(401);
                    throw new Exception(Piwik::translate('TwoFactorAuth_MissingAuthCodeAPI'));
                }
                if (!$twoFa->validateAuthCode($login, $authCode)) {
                    http_response_code(401);
                    throw new Exception(Piwik::translate('TwoFactorAuth_InvalidAuthCode'));
                }
            } else if ($twoFa->isUserRequiredToHaveTwoFactorEnabled()
                        && !TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($login)) {
                throw new Exception(Piwik::translate('TwoFactorAuth_RequiredAuthCodeNotConfiguredAPI'));
            }
        }
    }

    public function onRequestDispatch(&$module, &$action, $parameters)
    {
        $validator = $this->getValidator();
        if (!$validator->canUseTwoFa()) {
            return;
        }

        if ($module === 'Proxy') {
            return false;
        }

        if (!$this->requiresAuth($module, $action, $parameters)) {
            return;
        }

        $twoFa = $this->getTwoFa();

        $isUsing2FA = TwoFactorAuthentication::isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin());
        if ($isUsing2FA && !Request::isRootRequestApiRequest() && Session::isStarted()) {
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

    private function requiresAuth($module, $action, $parameters)
    {
        if ($module === 'TwoFactorAuth' && $action === 'showQrCode') {
            return false;
        }

        if ($module === 'CoreUpdater' && $action !== 'newVersionAvailable' && $action !== 'oneClickUpdate') {
            return false;
        }

        if ($module === Piwik::getLoginPluginName() && $action === 'logout') {
            return false;
        }

        $auth = StaticContainer::get('Piwik\Auth');
        if ($auth && !$auth->getLogin() && method_exists($auth, 'getTokenAuth') && $auth->getTokenAuth()) {
            // when authenticated by token only, we do not require 2fa
            // needed eg for rendering exported widgets authenticated by token
            return false;
        }

        $requiresAuth = true;
        Piwik::postEvent('TwoFactorAuth.requiresTwoFactorAuthentication', array(&$requiresAuth, $module, $action, $parameters));

        return $requiresAuth;
    }

    public function onRequestDispatchEnd(&$result, $module, $action, $parameters)
    {
        $validator = $this->getValidator();
        if (!$validator->canUseTwoFa()) {
            return;
        }

        if (!$this->requiresAuth($module, $action, $parameters)) {
            return;
        }

        $twoFa = $this->getTwoFa();

        $isUsing2FA = TwoFactorAuthentication::isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin());
        if ($isUsing2FA && !Request::isRootRequestApiRequest()) {
            $sessionFingerprint = new SessionFingerprint();
            if (!$sessionFingerprint->hasVerifiedTwoFactor()) {
                $result = $this->removeTokenFromOutput($result);
            }
        } elseif (!$isUsing2FA && $twoFa->isUserRequiredToHaveTwoFactorEnabled()) {
            $result = $this->removeTokenFromOutput($result);
        }
    }

    private function removeTokenFromOutput($output)
    {
        $token = Piwik::getCurrentUserTokenAuth();
        // make sure to not leak the token... otherwise someone could log in using someone's credentials...
        // and then maybe in the auth screen look into the DOM to find the token... and then bypass the
        // auth code using API
        return str_replace($token, md5('') . '2fareplaced', $output);
    }

}
