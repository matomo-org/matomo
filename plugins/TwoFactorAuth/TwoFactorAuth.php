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
            'Request.dispatch.end' => 'onRequestDispatchEnd',
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
                && $twoFa->isUserUsingTwoFactorAuthentication($login)
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

    public function onApiGetTokenAuth($returnedValue, $params)
    {
        if (!empty($returnedValue) && !empty($params['parameters']['userLogin'])) {
            $login = $params['parameters']['userLogin'];
            $twoFa = $this->getTwoFa();

            if ($twoFa->isUserUsingTwoFactorAuthentication($login) && $this->isValidTokenAuth($returnedValue)) {
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
                        && !$twoFa->isUserUsingTwoFactorAuthentication($login)) {
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
            return;
        }

        if ($module === 'TwoFactorAuth' && $action === 'showQrCode') {
            return;
        }

        if ($module === Piwik::getLoginPluginName() && $action === 'logout') {
            return;
        }
        /*
        $auth = StaticContainer::get('Piwik\Auth');
        if ($auth && $auth->getTokenAuthSecret() && !$auth->getLogin()) {
            return;
        }
*/
        $twoFa = $this->getTwoFa();

        $isUsing2FA = $twoFa->isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin());
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

    public function onRequestDispatchEnd(&$result, $module, $action, $parameters)
    {
        $validator = $this->getValidator();
        if (!$validator->canUseTwoFa()) {
            return;
        }

        $twoFa = $this->getTwoFa();

        $isUsing2FA = $twoFa->isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin());
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
        return str_replace($token, md5(''), $output);
    }

}
