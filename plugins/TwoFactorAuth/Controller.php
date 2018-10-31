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
use Piwik\Plugins\TwoFactorAuth\Dao\BackupCodeDao;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Session\SessionFingerprint;
use Piwik\Session\SessionNamespace;
use Piwik\Url;
use Piwik\View;
use Exception;

require_once PIWIK_DOCUMENT_ROOT . '/libs/Authenticator/TwoFactorAuthenticator.php';

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
     * @var BackupCodeDao
     */
    private $backupCodeDao;

    /**
     * @var PasswordVerify
     */
    private $passwordVerify;

    /**
     * @var Validate2FA
     */
    private $validate2FA;

    public function __construct(SystemSettings $systemSettings, BackupCodeDao $backupCodeDao, PasswordVerify $passwordVerify, Validate2FA $validate2FA)
    {
        $this->settings = $systemSettings;
        $this->backupCodeDao = $backupCodeDao;
        $this->passwordVerify = $passwordVerify;
        $this->validate2FA = $validate2FA;

        parent::__construct();
    }

    private function checkPermissions()
    {
        Piwik::checkUserIsNotAnonymous();
    }

    private function check2FaEnabled()
    {
        if (!$this->validate2FA->isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin())) {
            throw new Exception('not available');
        }
    }
    private function check2FaNotEnabled()
    {
        if ($this->validate2FA->isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin())) {
            throw new Exception('not available');
        }
    }
    private function checkVerified2FA()
    {
        $sessionFingerprint = new SessionFingerprint();
        if (!$sessionFingerprint->hasVerifiedTwoFactor()) {
            throw new Exception('not available');
        }
    }

    private function checkNotVerified2FAYet()
    {
        $sessionFingerprint = new SessionFingerprint();
        if ($sessionFingerprint->hasVerifiedTwoFactor()) {
            throw new Exception('not available');
        }
    }

    public function loginTwoFactorAuth()
    {
        $this->checkPermissions();
        $this->check2FaEnabled();
        $this->checkNotVerified2FAYet();

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
                    $authCode = strtoupper($authCode); // backup codes are stored upper case, app codes are only numbers
                    $authCode = trim($authCode);
                }

                if ($this->validate2FA->validateAuthCode(Piwik::getCurrentUserLogin(), $authCode)) {
                    $sessionFingerprint = new SessionFingerprint();
                    $sessionFingerprint->setTwoFactorAuthenticationVerified();
                    Url::redirectToUrl(Url::getCurrentUrl());
                }
            } else {
                $messageNoAccess = Piwik::translate('Login_InvalidNonceOrHeadersOrReferrer', array('<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/faq/how-to-install/#faq_98">', '</a>'));

            }
        }
        $view->AccessErrorString = $messageNoAccess;
        $view->addForm($form);
        $this->setBasicVariablesView($view);
        $view->nonce = Nonce::getNonce(self::LOGIN_2FA_NONCE);

        return $view->render();
    }

    public function userSettings()
    {
        $this->checkPermissions();

        return $this->renderTemplate('userSettings', array(
            'isEnabled' => $this->validate2FA->isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin()),
            'isForced' => $this->validate2FA->isUserRequiredToHaveTwoFactorEnabled(),
            'disableNonce' => Nonce::getNonce(self::DISABLE_2FA_NONCE)
        ));
    }


    private function makeAuthenticator()
    {
        return new \TwoFactorAuthenticator();
    }

    public function disableTwoFactorAuth()
    {
        $this->checkPermissions();
        $this->check2FaEnabled();
        $this->checkVerified2FA();

        if ($this->validate2FA->isUserRequiredToHaveTwoFactorEnabled()) {
            throw new Exception('Two-factor authentication cannot be disabled as it is enforced');
        }

        $nonce = Common::getRequestVar('disableNonce', null, 'string');

        if ($this->passwordVerify->requirePasswordVerifiedRecently(array('module' => 'TwoFactorAuth', 'action' => 'disableTwoFactorAuth', 'disableNonce' => $nonce))) {

            Nonce::checkNonce(self::DISABLE_2FA_NONCE, $nonce);

            $this->validate2FA->disable2FAforUser(Piwik::getCurrentUserLogin());
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
        $this->checkPermissions();
        $this->check2FaNotEnabled();

        if (!$this->validate2FA->isUserRequiredToHaveTwoFactorEnabled()) {
            throw new Exception('not available');
        }
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
        $this->checkPermissions();

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

        $authentiator = $this->makeAuthenticator();
        $session = $this->make2faSession();

        if (empty($session->secret)) {
            $session->secret = $authentiator->createSecret(16);
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
            if ($this->validate2FA->validateAuthCodeDuringSetup(trim($authCode), $secret)) {
                $this->validate2FA->save2FASecret($login, $secret);
                $fingerprint = new SessionFingerprint();
                $fingerprint->setTwoFactorAuthenticationVerified();
                unset($session->secret);
                $this->passwordVerify->forgetVerifiedPassword();

                if ($standalone) {
                    $this->redirectToIndex('CoreHome', 'index');
                } else {
                    $view = new View('@TwoFactorAuth/setupFinished');
                    $this->setGeneralVariablesView($view);
                }
                return $view->render();
            } else {
                $accessErrorString = 'Wrong authentication code entered. Please try again.';
            }
        }

        if (!$this->backupCodeDao->getAllBackupCodesForLogin($login)
            || (!$hasSubmittedForm && !$this->validate2FA->isUserUsingTwoFactorAuthentication($login))) {
            // we cannot generate new codes after form has been submitted and user is not yet using 2fa cause we would
            // change backup codes in the background without the user noticing... we cannot simply do this:
            // if !getAllBackupCodesForLogin => createBackupCodesForLogin. Because it could be a security issue that
            // user might start the setup but never finishes. Before setting up 2fa the first time we have to change
            // the backup codes
            $this->backupCodeDao->createBackupCodesForLogin($login);
        }

        $view->title = $this->settings->twoFactorAuthTitle->getValue();
        $view->description = Piwik::getCurrentUserLogin();
        $view->authCodeNonce = Nonce::getNonce(self::AUTH_CODE_NONCE);
        $view->AccessErrorString = $accessErrorString;
        $view->newSecret = $secret;
        $view->authImage = $this->getQRUrl($view->description, $view->gatitle);
        $view->codes = $this->backupCodeDao->getAllBackupCodesForLogin($login);
        $view->standalone = $standalone;

        return $view->render();
    }

    public function showBackupCodes()
    {
        $this->checkPermissions();
        $this->checkVerified2FA();
        $this->check2FaEnabled();

        if (!$this->passwordVerify->requirePasswordVerifiedRecently(array('module' => 'TwoFactorAuth', 'action' => 'showBackupCodes'))) {
            // should usually not go in here but redirect instead
            throw new Exception('You have to verify your password first.');
        }

        $regenerateSuccess = false;
        $regenerateError = false;
        if (!empty($_POST['regenerateNonce'])) {
            $nonce = Common::getRequestVar('regenerateNonce', '', 'string', $_POST);
            if (Nonce::verifyNonce(self::REGENERATE_CODES_2FA_NONCE, $nonce)) {
                $this->backupCodeDao->createBackupCodesForLogin(Piwik::getCurrentUserLogin());
                $regenerateSuccess = true;
            } else {
                $regenerateError = true;
            }
        }

        $backupCodes = $this->backupCodeDao->getAllBackupCodesForLogin(Piwik::getCurrentUserLogin());

        return $this->renderTemplate('showBackupCodes', array(
            'codes' => $backupCodes,
            'regenerateNonce' => Nonce::getNonce(self::REGENERATE_CODES_2FA_NONCE),
            'regenerateError' => $regenerateError,
            'regenerateSuccess' => $regenerateSuccess
        ));
    }

    public function showQrCode()
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

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
