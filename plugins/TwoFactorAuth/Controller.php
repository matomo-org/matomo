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

    public function loginTwoFactorAuth()
    {
        $this->checkPermissions();

        // user needs to have been logged in to confirm 2fa
        $sessionFingerprint = new SessionFingerprint();
        if ($sessionFingerprint->hasVerifiedTwoFactor()) {
            throw new Exception('not available');
        }

        if (!Piwik::isUserUsingTwoFactorAuthentication()) {
            throw new Exception('not available');
        }

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
                    $authCode = strtolower($authCode);
                }

                if ($this->validate2FA->validateAuthCode($authCode)) {
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
            'isEnabled' => Piwik::isUserUsingTwoFactorAuthentication(),
            'isForced' => $this->settings->twoFactorAuthRequired->getValue(),
            'disableNonce' => Nonce::getNonce(self::DISABLE_2FA_NONCE)
        ));
    }


    private function makeAuthenticator()
    {
        return new \TwoFactorAuthenticator();
    }

    private function getMyUser()
    {
        $login = Piwik::getCurrentUserLogin();
        $user = Request::processRequest('UsersManager.getUser', array('userLogin' => $login));

        return $user;
    }

    public function disableTwoFactorAuth()
    {
        $this->checkPermissions();

        if ($this->settings->twoFactorAuthRequired->getValue()) {
            throw new Exception('Two Factor Authentication cannot be disabled');
        }
        if (!Piwik::isUserUsingTwoFactorAuthentication()) {
            throw new Exception('Two Factor Authentication not enabled');
        }

        $nonce = Common::getRequestVar('nonce', null, 'string');

        if ($this->passwordVerify->requirePasswordVerifiedRecently(array('module' => 'TwoFactorAuth', 'action' => 'disableTwoFactorAuth', 'nonce' => $nonce))) {

            Nonce::checkNonce(self::DISABLE_2FA_NONCE);

            $model = new Model();
            $model->updateUserFields(Piwik::getCurrentUserLogin(), array('twofactor_secret' => ''));

            Url::redirectToUrl(Url::getCurrentUrl());
            $this->redirectToIndex('UsersManager', 'userSettings', null, null, null, array(
                'nonce' => false
            ));
        }
    }

    private function make2faSession()
    {
        return new SessionNamespace('TwoFactorAuthenticator');
    }

    /**
     * Action to generate a new Google Authenticator secret for the current user
     *
     * @return string
     * @throws \Exception
     * @throws \Piwik\NoAccessException
     */
    public function setupTwoFactorAuth()
    {
        $this->checkPermissions();

        $view = new View('@TwoFactorAuth/setupTwoFactorAuth');
        $this->setGeneralVariablesView($view);

        $authentiator = $this->makeAuthenticator();
        $session = $this->make2faSession();

        if (!$this->passwordVerify->requirePasswordVerifiedRecently(array('module' => 'TwoFactorAuth', 'action' => 'setupTwoFactorAuth'))) {
            // should usually not go in here but redirect instead
            throw new Exception('You have to verify your password first.');
        }

        if (empty($session->secret)) {
            $session->secret = $authentiator->createSecret(16);
        }

        $secret = $session->secret;
        $session->setExpirationSeconds(60 * 10, 'secret');

        $user = $this->getMyUser();
        $authCode = Common::getRequestVar('authcode', '', 'string');
        $authCodeNonce = Common::getRequestVar('authCodeNonce', '', 'string');
        $accessErrorString = '';

        if (!empty($secret) && !empty($authCode)
            && Nonce::verifyNonce(self::AUTH_CODE_NONCE, $authCodeNonce)) {
            if ($this->validate2FA->validateAuthCodeDuringSetup($authCode, $secret)) {
                $model = new Model();
                $model->updateUserFields($user['login'], array('twofactor_secret' => $secret));
                $fingerprint = new SessionFingerprint();
                $fingerprint->setTwoFactorAuthenticationVerified();

                $this->backupCodeDao->createBackupCodesForLogin($user['login']);

                // todo render codes directly here plus show a successful setup message
                $this->redirectToIndex('TwoFactorAuth', 'showBackupCodes');

                $view = new View('@TwoFactorAuth/setupFinished');
                $this->setGeneralVariablesView($view);
                $view->codes = $this->backupCodeDao->getAllBackupCodesForLogin(Piwik::getCurrentUserLogin());
                return $view->render();
            } else {
                $accessErrorString = 'Wrong auth code entered. Please try again.';
            }
        }

        $view->title = $this->settings->twoFactorAuthTitle->getValue();
        $view->description = Piwik::getCurrentUserLogin();
        $view->authCodeNonce = Nonce::getNonce(self::AUTH_CODE_NONCE);
        $view->AccessErrorString = $accessErrorString;
        $view->newSecret = $secret;
        $view->authImage = $this->getQRUrl($view->description, $view->gatitle);

        return $view->render();
    }

    private function checkPermissions()
    {
        Piwik::checkUserIsNotAnonymous();
    }

    public function showBackupCodes()
    {
        $this->checkPermissions();

        if (!$this->passwordVerify->requirePasswordVerifiedRecently(array('module' => 'TwoFactorAuth', 'action' => 'showBackupCodes'))) {
            // should usually not go in here but redirect instead
            throw new Exception('You have to verify your password first.');
        }

        $backupCodes = $this->backupCodeDao->getAllBackupCodesForLogin(Piwik::getCurrentUserLogin());

        return $this->renderTemplate('showBackupCodes', array(
            'codes' => $backupCodes
        ));
    }

    public function showQrCode()
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

        $session = $this->make2faSession();
        $secret = $session->secret;
        if (empty($secret)) {
            throw new Exception('Not possible');
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
