<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\Access;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Session\SessionFingerprint;
use Piwik\Session\SessionNamespace;
use Piwik\Url;
use Piwik\View;
use Exception;
use Piwik\Plugins\CoreAdminHome\Emails\RecoveryCodesShowedEmail;
use Piwik\Plugins\CoreAdminHome\Emails\TwoFactorAuthEnabledEmail;
use Piwik\Plugins\CoreAdminHome\Emails\TwoFactorAuthDisabledEmail;
use Piwik\Plugins\CoreAdminHome\Emails\RecoveryCodesRegeneratedEmail;

class Controller extends \Piwik\Plugin\Controller
{
    public const AUTH_CODE_NONCE = 'TwoFactorAuth.saveAuthCode';
    public const LOGIN_2FA_NONCE = 'TwoFactorAuth.loginAuthCode';
    public const DISABLE_2FA_NONCE = 'TwoFactorAuth.disableAuthCode';
    public const REGENERATE_CODES_2FA_NONCE = 'TwoFactorAuth.regenerateCodes';
    public const VERIFY_PASSWORD_NONCE = 'TwoFactorAuth.verifyPassword';

    private SystemSettings $settings;

    private RecoveryCodeDao $recoveryCodeDao;

    private PasswordVerifier $passwordVerify;

    private TwoFactorAuthentication $twoFa;

    private Validator $validator;

    public function __construct(SystemSettings $systemSettings, RecoveryCodeDao $recoveryCodeDao, PasswordVerifier $passwordVerify, TwoFactorAuthentication $twoFa, Validator $validator)
    {
        $this->settings = $systemSettings;
        $this->recoveryCodeDao = $recoveryCodeDao;
        $this->passwordVerify = $passwordVerify;
        $this->twoFa = $twoFa;
        $this->validator = $validator;

        parent::__construct();
    }

    public function loginTwoFactorAuth()
    {
        $this->validator->checkCanUseTwoFa();

        if (!$this->validator->isCurrentUserMatchingSessionUser()) {
            return $this->renderFreshLoginAfterResettingPendingTwoFactorSession();
        }

        $this->validator->check2FaEnabled();
        $this->validator->checkNotVerified2FAYet();

        $messageNoAccess = null;

        $view = new View('@TwoFactorAuth/loginTwoFactorAuth');
        $form = new FormTwoFactorAuthCode();
        $form->removeAttribute('action'); // remove action attribute, otherwise hash part will be lost
        if ($form->validate()) {
            $nonce = $form->getSubmitValue('form_nonce');
            $messageNoAccess = Nonce::verifyNonceWithErrorMessage(self::LOGIN_2FA_NONCE, $nonce);
            if ($nonce && $messageNoAccess === "" && $form->validate()) {
                $authCode = $form->getSubmitValue('form_authcode');
                if ($authCode && is_string($authCode)) {
                    $authCode = str_replace('-', '', $authCode);
                    $authCode = strtoupper($authCode); // recovery codes are stored upper case, app codes are only numbers
                    $authCode = trim($authCode);
                }

                if ($this->twoFa->validateAuthCode(Piwik::getCurrentUserLogin(), $authCode)) {
                    $sessionFingerprint = new SessionFingerprint();
                    $sessionFingerprint->setTwoFactorAuthenticationVerified(Piwik::getCurrentUserLogin());
                    Url::redirectToUrl(Url::getCurrentUrl());
                } else {
                    $messageNoAccess = Piwik::translate('TwoFactorAuth_InvalidAuthCode');
                    $this->twoFa->recordFailedAuthAttempt(Piwik::getCurrentUserLogin());
                }
            }
        }
        $view->contactEmail = implode(',', Piwik::getContactEmailAddresses());
        $view->loginModule = Piwik::getLoginPluginName();
        $view->AccessErrorString = $messageNoAccess;
        $view->addForm($form);
        $this->setBasicVariablesView($view);
        $view->nonce = Nonce::getNonce(self::LOGIN_2FA_NONCE);

        return $view->render();
    }

    private function renderFreshLoginAfterResettingPendingTwoFactorSession()
    {
        \Piwik\Plugins\Login\Controller::clearSession();
        Access::getInstance()->setSessionExpired(true);

        return StaticContainer::get(\Piwik\Plugins\Login\Controller::class)->login(
            null,
            Piwik::translate('General_YourSessionHasExpired')
        );
    }

    public function userSettings()
    {
        $this->validator->checkCanUseTwoFa();

        return $this->renderTemplate('userSettings', array(
            'isEnabled' => TwoFactorAuthentication::isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin()),
            'isForced' => $this->twoFa->isUserRequiredToHaveTwoFactorEnabled(),
            'disableNonce' => Nonce::getNonce(self::DISABLE_2FA_NONCE),
        ));
    }

    public function disableTwoFactorAuth()
    {
        $this->validator->checkCanUseTwoFa();
        $this->validator->check2FaEnabled();
        $this->validator->checkVerified2FA();

        if ($this->twoFa->isUserRequiredToHaveTwoFactorEnabled()) {
            throw new Exception('Two-factor authentication cannot be disabled as it is enforced');
        }

        $nonce = Common::getRequestVar('disableNonce', '', 'string');
        $params = array('module' => 'TwoFactorAuth', 'action' => 'disableTwoFactorAuth', 'disableNonce' => $nonce);

        if (!$this->passwordVerify->requirePasswordVerifiedRecently($params)) {
            // redirected to confirm the password first
            return;
        }

        // On top of the password, a valid 2FA code is required to disable 2FA. The nonce is only consumed once
        // 2FA is actually turned off, so it stays valid while the auth code is being entered.
        if (!Nonce::verifyNonce(self::DISABLE_2FA_NONCE, $nonce)) {
            throw new Exception(Piwik::translate('General_ExceptionSecurityCheckFailed'));
        }

        $login = Piwik::getCurrentUserLogin();
        $authCode = Common::getRequestVar('authCode', '', 'string');
        $accessErrorString = null;

        if ($authCode !== '') {
            $authCode = str_replace('-', '', $authCode);
            $authCode = strtoupper($authCode); // recovery codes are stored upper case, app codes are only numbers
            $authCode = trim($authCode);

            if ($this->twoFa->validateAuthCode($login, $authCode)) {
                Nonce::discardNonce(self::DISABLE_2FA_NONCE);

                $this->twoFa->disable2FAforUser($login);
                $this->passwordVerify->forgetVerifiedPassword();

                $container = StaticContainer::getContainer();
                $email = $container->make(TwoFactorAuthDisabledEmail::class, array(
                    'login' => $login,
                    'emailAddress' => Piwik::getCurrentUserEmail(),
                ));
                $email->safeSend();

                $this->redirectToIndex('UsersManager', 'userSecurity', null, null, null, array(
                    'disableNonce' => false,
                ));
                return;
            }

            $accessErrorString = Piwik::translate('TwoFactorAuth_InvalidAuthCode');
            $this->twoFa->recordFailedAuthAttempt($login);
        }

        $view = new View('@TwoFactorAuth/disableTwoFactorAuth');
        $this->setBasicVariablesView($view);
        $view->AccessErrorString = $accessErrorString;
        $view->disableNonce = $nonce;

        return $view->render();
    }

    private function make2faSession()
    {
        return new SessionNamespace('TwoFactorAuthenticator');
    }

    public function onLoginSetupTwoFactorAuth()
    {
        // called when 2fa is required, but user has not yet set up 2fa

        return $this->setupTwoFactorAuth($standalone = true);
    }

    /**
     * Action to setup two factor authentication
     *
     * @return string
     * @throws \Exception
     */
    public function setupTwoFactorAuth($standalone = false)
    {
        $this->validator->checkCanUseTwoFa();

        if ($standalone) {
            $view = new View('@TwoFactorAuth/setupTwoFactorAuthStandalone');
            $this->setBasicVariablesView($view);
            $view->submitAction = 'onLoginSetupTwoFactorAuth';
        } else {
            $view = new View('@TwoFactorAuth/setupTwoFactorAuth');
            $this->setGeneralVariablesView($view);
            $view->submitAction = 'setupTwoFactorAuth';

            $redirectParams = array('module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth');
            if (!$this->passwordVerify->requirePasswordVerified($redirectParams)) {
                // should usually not go in here but redirect instead
                throw new Exception('You have to verify your password first.');
            }
        }

        $session = $this->make2faSession();

        if (empty($session->secret)) {
            $session->secret = $this->twoFa->generateSecret();
        }

        $secret = $session->secret;
        $session->setExpirationSeconds(60 * 15, 'secret');

        $authCode = Common::getRequestVar('authCode', '', 'string');
        $authCodeNonce = Common::getRequestVar('authCodeNonce', '', 'string');
        $hasSubmittedForm = !empty($authCodeNonce) || !empty($authCode);
        $accessErrorString = '';
        $login = Piwik::getCurrentUserLogin();

        if (
            !empty($secret) && !empty($authCode)
            && Nonce::verifyNonce(self::AUTH_CODE_NONCE, $authCodeNonce)
        ) {
            if ($this->twoFa->validateAuthCodeDuringSetup(trim($authCode), $secret)) {
                $this->twoFa->saveSecret($login, $secret);
                $fingerprint = new SessionFingerprint();
                $fingerprint->setTwoFactorAuthenticationVerified($login);
                unset($session->secret);
                $this->passwordVerify->forgetVerifiedPassword();

                Piwik::postEvent('TwoFactorAuth.enabled', array($login));

                $container = StaticContainer::getContainer();
                $email = $container->make(TwoFactorAuthEnabledEmail::class, array(
                    'login' => Piwik::getCurrentUserLogin(),
                    'emailAddress' => Piwik::getCurrentUserEmail(),
                ));
                $email->safeSend();

                if ($standalone) {
                    $this->redirectToIndex('CoreHome', 'index');
                    return;
                }

                $view = new View('@TwoFactorAuth/setupFinished');
                $this->setGeneralVariablesView($view);
                return $view->render();
            } else {
                $accessErrorString = Piwik::translate('TwoFactorAuth_WrongAuthCodeTryAgain');
            }
        } elseif (!$standalone) {
            // the user has not posted the form... at least not with a valid nonce... we make sure the password verify
            // is valid for at least another 15 minutes and if not, ask for another password confirmation to avoid
            // the user may be posting a valid auth code after rendering this screen but the password verify is invalid
            // by then.
            $redirectParams = array('module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth');
            if (!$this->passwordVerify->requirePasswordVerifiedRecently($redirectParams)) {
                throw new Exception('You have to verify your password first.');
            }
        }

        if (
            !$this->recoveryCodeDao->getAllRecoveryCodesForLogin($login)
            || (!$hasSubmittedForm && !TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($login))
        ) {
            // we cannot generate new codes after form has been submitted and user is not yet using 2fa cause we would
            // change recovery codes in the background without the user noticing... we cannot simply do this:
            // if !getAllRecoveryCodesForLogin => createRecoveryCodesForLogin. Because it could be a security issue that
            // user might start the setup but never finishes. Before setting up 2fa the first time we have to change
            // the recovery codes
            $this->recoveryCodeDao->createRecoveryCodesForLogin($login);
        }

        $view->title = $this->settings->twoFactorAuthTitle->getValue();
        $view->description = $login;
        $view->authCodeNonce = Nonce::getNonce(self::AUTH_CODE_NONCE);
        $view->AccessErrorString = $accessErrorString;
        $view->isAlreadyUsing2fa = TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($login);
        $view->newSecret = $secret;
        $view->twoFaBarCodeSetupUrl = $this->getTwoFaBarCodeSetupUrl($secret);
        $view->codes = $this->recoveryCodeDao->getAllRecoveryCodesForLogin($login);
        $view->standalone = $standalone;

        return $view->render();
    }

    public function showRecoveryCodes()
    {
        $this->validator->checkCanUseTwoFa();
        $this->validator->checkVerified2FA();
        $this->validator->check2FaEnabled();

        $regenerateNonce = Common::getRequestVar('regenerateNonce', '', 'string', $_POST);
        $postedValidNonce = !empty($regenerateNonce) && Nonce::verifyNonce(
            self::REGENERATE_CODES_2FA_NONCE,
            $regenerateNonce
        );

        $regenerateSuccess = false;
        $regenerateError = false;
        $container = StaticContainer::getContainer();

        if ($postedValidNonce && $this->passwordVerify->hasBeenVerified()) {
            $this->passwordVerify->forgetVerifiedPassword();
            $this->recoveryCodeDao->createRecoveryCodesForLogin(Piwik::getCurrentUserLogin());
            $regenerateSuccess = true;

            $email = $container->make(RecoveryCodesRegeneratedEmail::class, array(
                'login' => Piwik::getCurrentUserLogin(),
                'emailAddress' => Piwik::getCurrentUserEmail(),
            ));
            $email->safeSend();
            // no need to redirect as password was verified nonce
            // if user has posted a valid nonce, we do not need to require password again as nonce must have been generated recent
            // avoids use case where eg password verify is only valid for one more minute when opening the page but user regenerates 2min later
        } elseif (!$this->passwordVerify->requirePasswordVerifiedRecently(array('module' => 'TwoFactorAuth', 'action' => 'showRecoveryCodes'))) {
            // should usually not go in here but redirect instead
            throw new Exception('You have to verify your password first.');
        }

        if (!$postedValidNonce && !empty($regenerateNonce)) {
            $regenerateError = true;
        }

        $recoveryCodes = $this->recoveryCodeDao->getAllRecoveryCodesForLogin(Piwik::getCurrentUserLogin());

        if (!$regenerateSuccess && !$regenerateError) {
            $email = $container->make(RecoveryCodesShowedEmail::class, array(
                'login' => Piwik::getCurrentUserLogin(),
                'emailAddress' => Piwik::getCurrentUserEmail(),
            ));
            $email->safeSend();
        }

        return $this->renderTemplate('showRecoveryCodes', array(
            'codes' => $recoveryCodes,
            'regenerateNonce' => Nonce::getNonce(self::REGENERATE_CODES_2FA_NONCE),
            'regenerateError' => $regenerateError,
            'regenerateSuccess' => $regenerateSuccess,
        ));
    }

    private function getTwoFaBarCodeSetupUrl(
        #[\SensitiveParameter]
        $secret
    ) {
        $title = $this->settings->twoFactorAuthTitle->getValue();
        $descr = Piwik::getCurrentUserLogin();

        $url = 'otpauth://totp/' . urlencode($descr) . '?secret=' . $secret;
        if (isset($title)) {
            $url .= '&issuer=' . urlencode($title);
        }

        return $url;
    }

    protected function getQRUrl($description, $title)
    {
        return sprintf('index.php?module=TwoFactorAuth&action=showQrCode&cb=%s&title=%s&descr=%s', Common::getRandomString(8), urlencode($title), urlencode($description));
    }
}
