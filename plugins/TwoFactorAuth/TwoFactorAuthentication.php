<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Plugins\UsersManager\Model;

require_once PIWIK_DOCUMENT_ROOT . '/libs/Authenticator/TwoFactorAuthenticator.php';

class TwoFactorAuthentication
{
    /**
     * @var SystemSettings
     */
    private $settings;

    /**
     * @var RecoveryCodeDao
     */
    private $recoveryCodeDao;

    public function __construct(SystemSettings $systemSettings, RecoveryCodeDao $recoveryCodeDao)
    {
        $this->settings = $systemSettings;
        $this->recoveryCodeDao = $recoveryCodeDao;
    }

    private function getUserModel()
    {
        return new Model();
    }

    public function disable2FAforUser($login)
    {
        $this->saveSecret($login, '');
        $this->recoveryCodeDao->deleteAllRecoveryCodesForLogin($login);
    }

    private function isAnonymous($login)
    {
        return strtolower($login) === 'anonymous';
    }

    public function saveSecret($login, $secret)
    {
        if ($this->isAnonymous($login)) {
            throw new \Exception('Anonymous cannot use two-factor authentication');
        }

        $model = $this->getUserModel();
        $model->updateUserFields($login, array('twofactor_secret' => $secret));
    }

    public function isUserRequiredToHaveTwoFactorEnabled()
    {
        return $this->settings->twoFactorAuthRequired->getValue();
    }

    public function isUserUsingTwoFactorAuthentication($login)
    {
        if ($this->isAnonymous($login)) {
            return false; // not possible to use auth code with anonymous
        }

        $user = $this->getUser($login);
        return !empty($user['twofactor_secret']);
    }

    private function getUser($login)
    {
        $model = $this->getUserModel();
        return $model->getUser($login);
    }

    public function validateAuthCode($login, $authCode)
    {
        if (!$this->isUserUsingTwoFactorAuthentication($login)) {
            return false;
        }

        $user = $this->getUser($login);

        if (!empty($user['twofactor_secret'])
            && $this->validateAuthCodeDuringSetup($authCode, $user['twofactor_secret'])) {
            return true;
        }

        if ($this->recoveryCodeDao->useRecoveryCode($user['login'], $authCode)) {
            return true;
        }

        return false;
    }

    public function validateAuthCodeDuringSetup($authCode, $secret)
    {
        $twoFactorAuth = $this->makeAuthenticator();

        if (!empty($secret) && $twoFactorAuth->verifyCode($secret, $authCode, 2)) {
            return true;
        }
        return false;
    }

    private function makeAuthenticator()
    {
        return new \TwoFactorAuthenticator();
    }

}
