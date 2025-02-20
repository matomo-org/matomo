<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
            'Login.authenticate.processSuccessfulSession.end' => 'onSuccessfulSession',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        );
    }

    public function getClientSideTranslationKeys(&$translations)
    {
        $translations[] = 'TwoFactorAuth_WarningChangingConfiguredDevice';
        $translations[] = 'TwoFactorAuth_SetupIntroFollowSteps';
        $translations[] = 'TwoFactorAuth_StepX';
        $translations[] = 'TwoFactorAuth_RecoveryCodes';
        $translations[] = 'TwoFactorAuth_RecoveryCodesExplanation';
        $translations[] = 'TwoFactorAuth_RecoveryCodesSecurity';
        $translations[] = 'TwoFactorAuth_RecoveryCodesAllUsed';
        $translations[] = 'General_Download';
        $translations[] = 'General_Print';
        $translations[] = 'General_Copy';
        $translations[] = 'General_Continue';
        $translations[] = 'TwoFactorAuth_SetupBackupRecoveryCodes';
        $translations[] = 'General_Next';
        $translations[] = 'TwoFactorAuth_SetupAuthenticatorOnDeviceStep1';
        $translations[] = 'General_Or';
        $translations[] = 'TwoFactorAuth_ConfirmSetup';
        $translations[] = 'TwoFactorAuth_VerifyAuthCodeIntro';
        $translations[] = 'TwoFactorAuth_AuthenticationCode';
        $translations[] = 'TwoFactorAuth_VerifyAuthCodeHelp';
        $translations[] = 'General_Confirm';
        $translations[] = 'TwoFactorAuth_SetupAuthenticatorOnDeviceStep2ShowCodes';
        $translations[] = 'TwoFactorAuth_ShowCodes';
        $translations[] = 'TwoFactorAuth_DontHaveOTPApp';
        $translations[] = 'TwoFactorAuth_ShowCodeModalInstructions1';
        $translations[] = 'TwoFactorAuth_ShowCodeModalInstructions2';
        $translations[] = 'TwoFactorAuth_ShowCodeModalInstructions3';
        $translations[] = 'TwoFactorAuth_SetupAuthenticatorOnDevice';
        $translations[] = 'TwoFactorAuth_TwoFactorAuthentication';
        $translations[] = 'General_Error';
        $translations[] = 'TwoFactorAuth_VerifyIdentifyExplanation';
        $translations[] = 'TwoFactorAuth_DontHaveYourMobileDevice';
        $translations[] = 'TwoFactorAuth_EnterRecoveryCodeInstead';
        $translations[] = 'TwoFactorAuth_NotPossibleToLogIn';
        $translations[] = 'TwoFactorAuth_LostAuthenticationDevice';
        $translations[] = 'TwoFactorAuth_AskSuperUserResetAuthenticationCode';
        $translations[] = 'General_Logout';
        $translations[] = 'TwoFactorAuth_Your2FaAuthSecret';
        $translations[] = 'TwoFactorAuth_GenerateNewRecoveryCodes';
        $translations[] = 'TwoFactorAuth_GenerateNewRecoveryCodesInfo';
        $translations[] = 'TwoFactorAuth_RecoveryCodesRegenerated';
        $translations[] = 'General_ExceptionSecurityCheckFailed';
        $translations[] = 'TwoFactorAuth_TwoFAShort';
        $translations[] = 'TwoFactorAuth_TwoFactorAuthenticationIntro';
        $translations[] = 'TwoFactorAuth_TwoFactorAuthenticationIsEnabled';
        $translations[] = 'TwoFactorAuth_TwoFactorAuthenticationRequired';
        $translations[] = 'TwoFactorAuth_ConfigureDifferentDevice';
        $translations[] = 'TwoFactorAuth_DisableTwoFA';
        $translations[] = 'TwoFactorAuth_ShowRecoveryCodes';
        $translations[] = 'TwoFactorAuth_TwoFactorAuthenticationIsDisabled';
        $translations[] = 'TwoFactorAuth_EnableTwoFA';
        $translations[] = 'TwoFactorAuth_ConfirmDisableTwoFA';
        $translations[] = 'General_Yes';
        $translations[] = 'General_No';
        $translations[] = 'TwoFactorAuth_RequiredToSetUpTwoFactorAuthentication';
        $translations[] = 'TwoFactorAuth_SetUpTwoFactorAuthentication';
        $translations[] = 'TwoFactorAuth_SetupFinishedTitle';
        $translations[] = 'TwoFactorAuth_SetupFinishedSubtitle';
        $translations[] = 'General_Continue';
        $translations[] = 'TwoFactorAuth_Verify';
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/TwoFactorAuth/stylesheets/twofactorauth.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "node_modules/qrcodejs2/qrcode.min.js";
    }

    public function deleteRecoveryCodes($returnedValue, $params)
    {
        $model = new Model();
        if (
            !empty($params['parameters']['userLogin'])
            && !$model->userExists($params['parameters']['userLogin'])
        ) {
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

            if (
                $authCode
                && TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($login)
                && $twoFa->validateAuthCode($login, $authCode)
            ) {
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
                    if (!headers_sent()) {
                        http_response_code(401);
                    }
                    throw new Exception(Piwik::translate('TwoFactorAuth_MissingAuthCodeAPI'));
                }
                if (!$twoFa->validateAuthCode($login, $authCode)) {
                    if (!headers_sent()) {
                        http_response_code(401);
                    }
                    throw new Exception(Piwik::translate('TwoFactorAuth_InvalidAuthCode'));
                }
            } elseif (
                $twoFa->isUserRequiredToHaveTwoFactorEnabled()
                        && !TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($login)
            ) {
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
        if ($isUsing2FA && Session::isStarted()) {
            $sessionFingerprint = new SessionFingerprint();
            if (!$sessionFingerprint->hasVerifiedTwoFactor()) {
                if (!Request::isRootRequestApiRequest()) {
                    $module = 'TwoFactorAuth';
                    $action = 'loginTwoFactorAuth';
                } elseif (Common::getRequestVar('force_api_session', 0) == 1) {
                    // don't allow API requests with session auth if 2fa code hasn't been verified.
                    throw new Exception(Piwik::translate('General_YourSessionHasExpired'));
                }
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

        if ($module === 'CorePluginsAdmin' && strtolower($action) === 'safemode') {
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
        if (empty($output)) {
            return $output;
        }

        $token = Piwik::getCurrentUserTokenAuth();
        // make sure to not leak the token... otherwise someone could log in using someone's credentials...
        // and then maybe in the auth screen look into the DOM to find the token... and then bypass the
        // auth code using API
        return str_replace($token, md5('') . '2fareplaced', $output);
    }
}
