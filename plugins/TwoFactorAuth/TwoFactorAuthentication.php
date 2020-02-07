<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\Piwik;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretRandomGenerator;
use Piwik\Plugins\UsersManager\Model;
use Exception;

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

    /**
     * @var TwoFaSecretRandomGenerator
     */
    private $secretGenerator;

    public function __construct(SystemSettings $systemSettings, RecoveryCodeDao $recoveryCodeDao, TwoFaSecretRandomGenerator $twoFaSecretRandomGenerator)
    {
        $this->settings = $systemSettings;
        $this->recoveryCodeDao = $recoveryCodeDao;
        $this->secretGenerator = $twoFaSecretRandomGenerator;
    }

    private function getUserModel()
    {
        return new Model();
    }

    public function generateSecret()
    {
        return $this->secretGenerator->generateSecret();
    }

    public function disable2FAforUser($login)
    {
        $this->saveSecret($login, '');
        $this->recoveryCodeDao->deleteAllRecoveryCodesForLogin($login);

        Piwik::postEvent('TwoFactorAuth.disabled', array($login));
    }

    private function isAnonymous($login)
    {
        return strtolower($login) === 'anonymous';
    }

    public function saveSecret($login, $secret)
    {
        if ($this->isAnonymous($login)) {
            throw new Exception('Anonymous cannot use two-factor authentication');
        }

        if (!empty($secret) && !$this->recoveryCodeDao->getAllRecoveryCodesForLogin($login)) {
            // ensures the user has seen and ideally backuped the recovery codes... we don't create them here on demand
            throw new Exception('Cannot enable two-factor authentication, no recovery codes have been created');
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
