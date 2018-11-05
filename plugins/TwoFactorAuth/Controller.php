<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth;

use Endroid\QrCode\QrCode;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\Login\PasswordVerify;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretRandomGenerator;
use Piwik\Session\SessionFingerprint;
use Piwik\Session\SessionNamespace;
use Piwik\Url;
use Piwik\View;
use Exception;

class Controller extends \Piwik\Plugin\Controller
{
    const AUTH_CODE_NONCE = 'TwoFactorAuth.saveAuthCode';
    const LOGIN_2FA_NONCE = 'TwoFactorAuth.loginAuthCode';
    const DISABLE_2FA_NONCE = 'TwoFactorAuth.disableAuthCode';
    const REGENERATE_CODES_2FA_NONCE = 'TwoFactorAuth.regenerateCodes';
    const VERIFY_PASSWORD_NONCE = 'TwoFactorAuth.verifyPassword';

    /**
     * @var SystemSettings
     */
    private $settings;

    /**
     * @var RecoveryCodeDao
     */
    private $recoveryCodeDao;

    /**
     * @var PasswordVerify
     */
    private $passwordVerify;

    /**
     * @var TwoFactorAuthentication
     */
    private $twoFa;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var TwoFaSecretRandomGenerator
     */
    private $secretGenerator;

    public function __construct(SystemSettings $systemSettings, RecoveryCodeDao $recoveryCodeDao, PasswordVerify $passwordVerify, TwoFactorAuthentication $twoFa, Validator $validator, TwoFaSecretRandomGenerator $secretGenerator)
    {
        $this->settings = $systemSettings;
        $this->recoveryCodeDao = $recoveryCodeDao;
        $this->passwordVerify = $passwordVerify;
        $this->twoFa = $twoFa;
        $this->validator = $validator;
        $this->secretGenerator = $secretGenerator;

        parent::__construct();
    }

    public function loginTwoFactorAuth()
    {
        $this->validator->checkCanUseTwoFa();
        $this->validator->check2FaEnabled();
        $this->validator->checkNotVerified2FAYet();

        $messageNoAccess = null;

        $view = new View('@TwoFactorAuth/loginTwoFactorAuth');
        $form = new FormTwoFactorAuthCode();
        $form->removeAttribute('action'); // remove action attribute, otherwise hash part will be lost
        if ($form->validate()) {
            $nonce = $form->getSubmitValue('form_nonce');
            if ($nonce && Nonce::verifyNonce(self::LOGIN_2FA_NONCE, $nonce) && $form->validate()) {
                $authCode = $form->getSubmitValue('form_authcode');
                if ($authCode && is_string($authCode)) {
                    $authCode = str_replace('-', '', $authCode);
                    $authCode = strtoupper($authCode); // recovery codes are stored upper case, app codes are only numbers
                    $authCode = trim($authCode);
                }

                if ($this->twoFa->validateAuthCode(Piwik::getCurrentUserLogin(), $authCode)) {
                    $sessionFingerprint = new SessionFingerprint();
                    $sessionFingerprint->setTwoFactorAuthenticationVerified();
                    Url::redirectToUrl(Url::getCurrentUrl());
                } else {
                    $messageNoAccess = Piwik::translate('TwoFactorAuth_InvalidAuthCode');
                }
            } else {
                $messageNoAccess = Piwik::translate('Login_InvalidNonceOrHeadersOrReferrer', array('<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/faq/how-to-install/#faq_98">', '</a>'));
            }
        }
        $superUsers = Request::processRequest('UsersManager.getUsersHavingSuperUserAccess', [], []);
        $view->superUserEmails = implode(',', array_column($superUsers, 'email'));
        $view->loginModule = Piwik::getLoginPluginName();
        $view->AccessErrorString = $messageNoAccess;
        $view->addForm($form);
        $this->setBasicVariablesView($view);
        $view->nonce = Nonce::getNonce(self::LOGIN_2FA_NONCE);

        return $view->render();
    }

    public function userSettings()
    {
        $this->validator->checkCanUseTwoFa();

        return $this->renderTemplate('userSettings', array(
            'isEnabled' => $this->twoFa->isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin()),
            'isForced' => $this->twoFa->isUserRequiredToHaveTwoFactorEnabled(),
            'disableNonce' => Nonce::getNonce(self::DISABLE_2FA_NONCE)
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

        $nonce = Common::getRequestVar('disableNonce', null, 'string');

        if ($this->passwordVerify->requirePasswordVerifiedRecently(array('module' => 'TwoFactorAuth', 'action' => 'disableTwoFactorAuth', 'disableNonce' => $nonce))) {

            Nonce::checkNonce(self::DISABLE_2FA_NONCE, $nonce);

            $this->twoFa->disable2FAforUser(Piwik::getCurrentUserLogin());
            $this->passwordVerify->forgetVerifiedPassword();

            $this->redirectToIndex('UsersManager', 'userSettings', null, null, null, array(
                'disableNonce' => false
            ));
        }
    }

    private function make2faSession()
    {
        return new SessionNamespace('TwoFactorAuthenticator');
    }

    public function onLoginSetupTwoFactorAuth()
    {
        // called when 2fa is required, but user has not yet set up 2fa
        $this->validator->checkCanUseTwoFa();
        $this->validator->check2FaNotEnabled();
        $this->validator->check2FaIsRequired();

        return $this->setupTwoFactorAuth($standalone = true);
    }

    /**
     * Action to generate a new Google Authenticator secret for the current user
     *
     * @return string
     * @throws \Exception
     * @throws \Piwik\NoAccessException
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

            if (!$this->passwordVerify->requirePasswordVerifiedRecently(array('module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth'))) {
                // should usually not go in here but redirect instead
                throw new Exception('You have to verify your password first.');
            }
        }

        $session = $this->make2faSession();

        if (empty($session->secret)) {
            $session->secret = $this->secretGenerator->generateSecret();
        }

        $secret = $session->secret;
        $session->setExpirationSeconds(60 * 15, 'secret');

        $authCode = Common::getRequestVar('authcode', '', 'string');
        $authCodeNonce = Common::getRequestVar('authCodeNonce', '', 'string');
        $hasSubmittedForm = !empty($authCodeNonce) || !empty($authCode);
        $accessErrorString = '';
        $login = Piwik::getCurrentUserLogin();

        if (!empty($secret) && !empty($authCode)
            && Nonce::verifyNonce(self::AUTH_CODE_NONCE, $authCodeNonce)) {
            if ($this->twoFa->validateAuthCodeDuringSetup(trim($authCode), $secret)) {
                $this->twoFa->saveSecret($login, $secret);
                $fingerprint = new SessionFingerprint();
                $fingerprint->setTwoFactorAuthenticationVerified();
                unset($session->secret);
                $this->passwordVerify->forgetVerifiedPassword();

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

        if (!$this->recoveryCodeDao->getAllRecoveryCodesForLogin($login)
            || (!$hasSubmittedForm && !$this->twoFa->isUserUsingTwoFactorAuthentication($login))) {
            // we cannot generate new codes after form has been submitted and user is not yet using 2fa cause we would
            // change recovery codes in the background without the user noticing... we cannot simply do this:
            // if !getAllRecoveryCodesForLogin => createRecoveryCodesForLogin. Because it could be a security issue that
            // user might start the setup but never finishes. Before setting up 2fa the first time we have to change
            // the recovery codes
            $this->recoveryCodeDao->createRecoveryCodesForLogin($login);
        }

        $view->title = $this->settings->twoFactorAuthTitle->getValue();
        $view->description = Piwik::getCurrentUserLogin();
        $view->authCodeNonce = Nonce::getNonce(self::AUTH_CODE_NONCE);
        $view->AccessErrorString = $accessErrorString;
        $view->newSecret = $secret;
        $view->authImage = $this->getQRUrl($view->description, $view->gatitle);
        $view->codes = $this->recoveryCodeDao->getAllRecoveryCodesForLogin($login);
        $view->standalone = $standalone;

        return $view->render();
    }

    public function showRecoveryCodes()
    {
        $this->validator->checkCanUseTwoFa();
        $this->validator->checkVerified2FA();
        $this->validator->check2FaEnabled();

        if (!$this->passwordVerify->requirePasswordVerifiedRecently(array('module' => 'TwoFactorAuth', 'action' => 'showRecoveryCodes'))) {
            // should usually not go in here but redirect instead
            throw new Exception('You have to verify your password first.');
        }

        $regenerateSuccess = false;
        $regenerateError = false;
        if (!empty($_POST['regenerateNonce'])) {
            $nonce = Common::getRequestVar('regenerateNonce', '', 'string', $_POST);
            if (Nonce::verifyNonce(self::REGENERATE_CODES_2FA_NONCE, $nonce)) {
                $this->recoveryCodeDao->createRecoveryCodesForLogin(Piwik::getCurrentUserLogin());
                $regenerateSuccess = true;
                $this->passwordVerify->forgetVerifiedPassword();

            } else {
                $regenerateError = true;
            }
        }

        $recoveryCodes = $this->recoveryCodeDao->getAllRecoveryCodesForLogin(Piwik::getCurrentUserLogin());

        return $this->renderTemplate('showRecoveryCodes', array(
            'codes' => $recoveryCodes,
            'regenerateNonce' => Nonce::getNonce(self::REGENERATE_CODES_2FA_NONCE),
            'regenerateError' => $regenerateError,
            'regenerateSuccess' => $regenerateSuccess
        ));
    }

    public function showQrCode()
    {
        $this->validator->checkCanUseTwoFa();

        $session = $this->make2faSession();
        $secret = $session->secret;
        if (empty($secret)) {
            throw new Exception('Not available');
        }
        $title = $this->settings->twoFactorAuthTitle->getValue();
        $descr = Piwik::getCurrentUserLogin();

        $url = 'otpauth://totp/'.urlencode($descr).'?secret='.$secret;
        if(isset($title)) {
            $url .= '&issuer='.urlencode($title);
        }

        $qrCode = new QrCode($url);

        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->get();
    }

    protected function getQRUrl($description, $title)
    {
        return sprintf('index.php?module=TwoFactorAuth&action=showQrCode&cb=%s&title=%s&descr=%s', Common::getRandomString(8), urlencode($title), urlencode($description));
    }

}
