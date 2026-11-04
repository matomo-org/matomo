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
use Piwik\Http\BadRequestException;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\Login\SecondFactorVerifier;
use Piwik\Plugins\Login\WhatsNewProvider;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Request;
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

    private WhatsNewProvider $whatsNewProvider;

    private SecondFactorVerifier $secondFactorVerifier;

    public function __construct(
        SystemSettings $systemSettings,
        RecoveryCodeDao $recoveryCodeDao,
        PasswordVerifier $passwordVerify,
        TwoFactorAuthentication $twoFa,
        Validator $validator,
        WhatsNewProvider $whatsNewProvider,
        ?SecondFactorVerifier $secondFactorVerifier = null
    ) {
        $this->settings = $systemSettings;
        $this->recoveryCodeDao = $recoveryCodeDao;
        $this->passwordVerify = $passwordVerify;
        $this->twoFa = $twoFa;
        $this->validator = $validator;
        $this->whatsNewProvider = $whatsNewProvider;
        $this->secondFactorVerifier = $secondFactorVerifier ?? StaticContainer::get(SecondFactorVerifier::class);

        parent::__construct();
    }

    /**
     * The "What's New" entries shown by the shared login layout. Reuses Login's provider, since
     * these screens already extend Login's layout.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getWhatsNewChanges(): array
    {
        return $this->whatsNewProvider->getChanges();
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
                    $authCode = $this->twoFa->normalizeAuthCode($authCode);
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
        $view->whatsNewChanges = $this->getWhatsNewChanges();
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
            // valid as long as the confirmations it covers
            'disableNonce' => Nonce::getNonce(
                self::DISABLE_2FA_NONCE,
                SecondFactorVerifier::REQUEST_VALID_FOR_MINUTES * 60
            ),
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

        // the nonce is only consumed once 2FA is turned off; each step extends it for the next confirmation
        $nonce = Request::fromRequest()->getStringParameter('disableNonce', '');
        if (!Nonce::verifyNonce(self::DISABLE_2FA_NONCE, $nonce)) {
            throw new BadRequestException(Piwik::translate('General_ExceptionSecurityCheckFailed'));
        }

        $params = [
            'module' => 'TwoFactorAuth',
            'action' => 'disableTwoFactorAuth',
            'disableNonce' => Nonce::getNonce(
                self::DISABLE_2FA_NONCE,
                SecondFactorVerifier::REQUEST_VALID_FOR_MINUTES * 60
            ),
        ];

        if (
            !$this->passwordVerify->requirePasswordVerifiedRecently($params)
            || !$this->secondFactorVerifier->requireSecondFactorConfirmed($params)
        ) {
            // redirected to confirm the password or the second factor first
            return;
        }

        Nonce::discardNonce(self::DISABLE_2FA_NONCE);

        $login = Piwik::getCurrentUserLogin();
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
    }

    private function requireSetupConfirmed(): void
    {
        if (!$this->secondFactorVerifier->requireSecondFactorConfirmed(['module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth'])) {
            // should usually not go in here but redirect instead
            throw new Exception('You have to confirm your authentication code first.');
        }
    }

    private function setSetupSecretExpiration(SessionNamespace $session): void
    {
        $session->setExpirationSeconds(60 * 15, 'secret');
        $session->setExpirationSeconds(60 * 15, 'secretLogin');
        $session->setExpirationSeconds(60 * 15, 'secretConfirmed');
    }

    /**
     * Shows the second-factor confirmation if neither the login plugin in use nor Login can, see
     * {@link SecondFactorVerifier}.
     *
     * @return string|void
     */
    public function confirmSecondFactor()
    {
        return StaticContainer::get(\Piwik\Plugins\Login\Controller::class)->confirmSecondFactor();
    }

    private function make2faSession()
    {
        return new SessionNamespace('TwoFactorAuthenticator');
    }

    public function onLoginSetupTwoFactorAuth()
    {
        // login-only setup screen: available only while 2fa is enforced and the user has not enrolled yet
        $this->validator->checkCanUseTwoFa();
        $this->validator->checkCurrentUserMatchesSessionUser();
        $this->validator->check2FaIsRequired();

        if (TwoFactorAuthentication::isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin())) {
            // eg reloaded after the setup was completed, so continue where the completed setup leads to
            Url::redirectToUrl('index.php');
        }

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
            $view->whatsNewChanges = $this->getWhatsNewChanges();
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

        $request = Request::fromRequest();
        $authCode = $request->getStringParameter('authCode', '');
        $authCodeNonce = $request->getStringParameter('authCodeNonce', '');
        $hasSubmittedForm = !empty($authCodeNonce) || !empty($authCode);
        // the nonce is only handed out by a rendered setup page, which needed a confirmation when replacing a device
        $hasSubmittedCode = !empty($authCode) && Nonce::verifyNonce(self::AUTH_CODE_NONCE, $authCodeNonce);

        if (!$standalone && !$hasSubmittedCode) {
            // the user has not posted the form... at least not with a valid nonce... we make sure the password verify
            // is valid for at least another 15 minutes and if not, ask for another password confirmation to avoid
            // the user may be posting a valid auth code after rendering this screen but the password verify is invalid
            // by then.
            $redirectParams = array('module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth');
            if (!$this->passwordVerify->requirePasswordVerifiedRecently($redirectParams)) {
                throw new Exception('You have to verify your password first.');
            }
        }

        $session = $this->make2faSession();
        $currentLogin = Piwik::getCurrentUserLogin();
        // replacing a configured device shows its recovery codes and lets a new device take over, so every view
        // of it needs a confirmation, except the answer to a code submitted from such a view
        $isReplacingDevice = !$standalone && TwoFactorAuthentication::isUserUsingTwoFactorAuthentication($currentLogin);
        $isConfirmed = false;

        if (
            empty($session->secret)
            || $session->secretLogin !== $currentLogin
            || ($isReplacingDevice && empty($session->secretConfirmed))
        ) {
            if ($isReplacingDevice) {
                $this->requireSetupConfirmed();
                $isConfirmed = true;
            }

            $session->secret = $this->twoFa->generateSecret();
            $session->secretLogin = $currentLogin;
            $session->secretConfirmed = $isConfirmed;
            $this->setSetupSecretExpiration($session);
        } elseif (!$isReplacingDevice) {
            // keeps a first setup going while the user works through it; a replacement ends 15 minutes after
            // its confirmation
            $this->setSetupSecretExpiration($session);
        }

        $secret = $session->secret;
        $accessErrorString = '';
        $login = Piwik::getCurrentUserLogin();

        if (!empty($secret) && $hasSubmittedCode) {
            if ($this->twoFa->validateAuthCodeDuringSetup(trim($authCode), $secret)) {
                $this->twoFa->saveSecret($login, $secret);
                $fingerprint = new SessionFingerprint();
                $fingerprint->setTwoFactorAuthenticationVerified($login);
                unset($session->secret, $session->secretLogin, $session->secretConfirmed);
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
        }

        if ($isReplacingDevice && !$isConfirmed && !$hasSubmittedCode) {
            $this->requireSetupConfirmed();
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

        $regenerateNonce = Request::fromPost()->getStringParameter('regenerateNonce', '');
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
        } else {
            // regenerating is covered by the nonce above, which only a confirmed page view hands out
            $redirectParams = array('module' => 'TwoFactorAuth', 'action' => 'showRecoveryCodes');
            if (!$postedValidNonce && !empty($regenerateNonce)) {
                // eg the page was open longer than the nonce is valid; reported once the page is confirmed again
                $session = $this->make2faSession();
                $session->regenerateFailed = Piwik::getCurrentUserLogin();
                $session->setExpirationSeconds(SecondFactorVerifier::REQUEST_VALID_FOR_MINUTES * 60, 'regenerateFailed');
            }

            if (
                !$this->passwordVerify->requirePasswordVerifiedRecently($redirectParams)
                || !$this->secondFactorVerifier->requireSecondFactorConfirmed($redirectParams)
            ) {
                // should usually not go in here but redirect instead
                throw new Exception('You have to verify your password and authentication code first.');
            }
        }

        $session = $this->make2faSession();
        if ($session->regenerateFailed === Piwik::getCurrentUserLogin()) {
            $regenerateError = !$regenerateSuccess;
            unset($session->regenerateFailed);
        }

        $recoveryCodes = $this->recoveryCodeDao->getAllRecoveryCodesForLogin(Piwik::getCurrentUserLogin());

        if (!$regenerateSuccess) {
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
