<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Plugins\TwoFactorAuth\Dao\BackupCodeDao;

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

    public function validateAuthCode($authCode)
    {
        $user = $this->getMyUser();
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

    private function getMyUser()
    {
        $login = Piwik::getCurrentUserLogin();
        $user = Request::processRequest('UsersManager.getUser', array('userLogin' => $login));

        return $user;
    }

}
