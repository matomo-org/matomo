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
use Piwik\Container\StaticContainer;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\Login\FormTwoFactorAuthCode;
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
    const VERIFY_PASSWORD_NONCE = 'TwoFactorAuth.verifyPassword';

    /**
     * @var SystemSettings
     */
    private $settings;

    /**
     * @var BackupCodeDao
     */
    private $backupCodeDao;

    public function __construct(SystemSettings $systemSettings, BackupCodeDao $backupCodeDao)
    {
        $this->settings = $systemSettings;
        $this->backupCodeDao = $backupCodeDao;

        parent::__construct();
    }

    public function twoFactorAuth()
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

        // user needs to have been logged in to confirm 2fa
        $sessionFingerprint = new SessionFingerprint();
        if ($sessionFingerprint->hasVerifiedTwoFactor()) {
              throw new Exception('not available');
        }

        if (!Piwik::isUserUsingTwoFactorAuthentication()) {
             throw new Exception('not available');
        }
        $messageNoAccess = null;

        $view = new View('@TwoFactorAuth/twoFactorAuth');
        $form = new FormTwoFactorAuthCode();
        $form->removeAttribute('action'); // remove action attribute, otherwise hash part will be lost
        if ($form->validate()) {
            $nonce = $form->getSubmitValue('form_nonce');
            if ($nonce && Nonce::verifyNonce(self::LOGIN_2FA_NONCE, $nonce) && $form->validate()) {
                $authCode = $form->getSubmitValue('form_authcode');

                if ($this->validateAuthCode($authCode, $allowBackupCode = true)) {
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
        Piwik::checkUserIsNotAnonymous();

        return $this->renderTemplate('userSettings', array(
            'isEnabled' => Piwik::isUserUsingTwoFactorAuthentication(),
            'isForced' => $this->settings->twoFactorAuthRequired->getValue()
        ));
    }

    private function validateAuthCode($authCode, $allowUseBackupCode)
    {
        $user = $this->getMyUser();
        $twoFactorAuth = $this->makeAuthenticator();
        if (!empty($user['twofactor_secret']) && $twoFactorAuth->verifyCode($user['twofactor_secret'], $authCode, 2)) {
            return true;
        }

        if ($allowUseBackupCode && $this->backupCodeDao->useBackupCode($user['login'], $authCode)) {
            return true;
        }

        return false;
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
        Piwik::checkUserIsNotAnonymous();

        if ($this->settings->twoFactorAuthRequired->getValue()) {
            throw new Exception('Two Factor Authentication cannot be disabled');
        }
        if (!Piwik::isUserUsingTwoFactorAuthentication()) {
            throw new Exception('Two Factor Authentication not enabled');
        }

        if ($this->verifyPasswordCorrect()) {
            $model = new Model();
            $model->updateUserFields(Piwik::getCurrentUserLogin(), array('twofactor_secret' => ''));

            Url::redirectToUrl(Url::getCurrentUrl());
        }
    }

    private function verifyPasswordCorrect()
    {
        /** @var \Piwik\Auth $authAdapter */
        $authAdapter = StaticContainer::get('Piwik\Auth');
        $authAdapter->setLogin(Piwik::getCurrentUserLogin());
        $authAdapter->setPasswordHash(Common::getRequestVar('password', null, 'string'));
        $authAdapter->setTokenAuth(null);// ensure authentication happens on password
        $authAdapter->setPassword(null);// ensure authentication happens on password
        $authResult = $authAdapter->authenticate();
        return $authResult->wasAuthenticationSuccessful();
    }

    public function setupTwoFactorAuthStep1()
    {
        Piwik::checkUserIsNotAnonymous();

        if (!empty($_GET['nonce'])) {
            Nonce::checkNonce(self::VERIFY_PASSWORD_NONCE, $_GET['nonce']);
            $this->verifyPasswordCorrect();
            $session = $this->make2faSession();
            $session->passwordVerified = 1;
            $session->setExpirationSeconds(60 * 30, 'passwordVerified'); // require re-enter password after 30 minutes
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
    public function setupTwoFactorAuthStep2()
    {
        Piwik::checkUserIsNotAnonymous();

        $view = new View('@GoogleAuthenticator/regenerate');
        $this->setGeneralVariablesView($view);

        $authentiator = $this->makeAuthenticator();
        $session = $this->make2faSession();

        if (empty($session->passwordVerified)) {
            throw new Exception('You have to verify your password first.');
        }

        if (empty($session->secret)) {
            $session->secret = $authentiator->createSecret(32);
        }

        $secret = $session->secret;
        $session->setExpirationSeconds(60 * 5, 'secret');

        $user = $this->getMyUser();
        $authCode = Common::getRequestVar('authcode', '', 'string');
        $authCodeNonce = Common::getRequestVar('authCodeNonce', '', 'string');

        if (!empty($secret) && !empty($authCode)
            && Nonce::verifyNonce(self::AUTH_CODE_NONCE, $authCodeNonce)
            && $this->validateAuthCode($authCode, $allowBackupCode = false)
        ) {
            // todo... include twofactor secret in password reset hash? and the regular session to log other
            // sessions out after changing secret
            $model = new Model();
            $model->updateUserFields($user['login'], array('twofactor_secret' => $secret));
            $fingerprint = new SessionFingerprint();
            $fingerprint->setTwoFactorAuthenticationVerified();

            $backupCodes = $this->backupCodeDao->createBackupCodesForLogin($user['login']);

            return $this->renderTemplate('backupCodes', array(
                'codes' => $backupCodes
            ));
        }

        $view->title = $this->settings->twoFactorAuthTitle->getValue();
        $view->description = Piwik::getCurrentUserLogin();
        $view->authCodeNonce = Nonce::getNonce(self::AUTH_CODE_NONCE);
        $view->newSecret = $secret;
        $view->authImage = $this->getQRUrl($view->description, $view->gatitle);

        return $view->render();
    }

    public function showQrCode()
    {
        Piwik::checkUserIsNotAnonymous();

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
        return sprintf('index.php?module=GoogleAuthenticator&action=showQrCode&cb=%s&title=%s&descr=%s', Common::getRandomString(8), urlencode($title), urlencode($description));
    }

    protected function getCurrentQRUrl()
    {
        return sprintf('index.php?module=GoogleAuthenticator&action=showQrCode&cb=%s&current=1', Common::getRandomString(8));
    }

}
