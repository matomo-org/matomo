<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\Plugins\TwoFactorAuth\Dao\BackupCodeDao;
use Piwik\Plugins\UsersManager\Model;

require_once PIWIK_DOCUMENT_ROOT . '/libs/Authenticator/TwoFactorAuthenticator.php';

class Validate2FA
{
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
    }

    public function disable2FAforUser($login)
    {
        $this->save2FASecret($login, '');
        $this->backupCodeDao->deleteAllBackupCodesForLogin($login);
    }

    public function save2FASecret($login, $secret)
    {
        $model = new Model();
        $model->updateUserFields($login, array('twofactor_secret' => $secret));
    }

    public function isUserRequiredToHaveTwoFactorEnabled()
    {
        return $this->settings->twoFactorAuthRequired->getValue();
    }

    public function isUserUsingTwoFactorAuthentication($login)
    {
        if ($login === 'anonymous') {
            return false; // not possible to use auth code with anonymous
        }

        $user = $this->getUser($login);
        return !empty($user['twofactor_secret']);
    }

    private function getUser($login)
    {
        $userModel = new Model();
        return $userModel->getUser($login);
    }

    public function validateAuthCode($login, $authCode)
    {
        if (!$this->isUserUsingTwoFactorAuthentication($login)) {
            return true; // two factor not enabled
        }

        $user = $this->getUser($login);

        if ($this->validateAuthCodeDuringSetup($authCode, $user['twofactor_secret'])) {
            return true;
        }

        if ($this->backupCodeDao->useBackupCode($user['login'], $authCode)) {
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
