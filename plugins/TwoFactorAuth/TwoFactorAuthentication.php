<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretRandomGenerator;
use Piwik\Plugins\UsersManager\Model;
use Exception;
use Piwik\SettingsPiwik;

require_once PIWIK_DOCUMENT_ROOT . '/libs/Authenticator/TwoFactorAuthenticator.php';

class TwoFactorAuthentication
{
    const OPTION_PREFIX_TWO_FA_CODE_USED = 'twofa_codes_used_';

    /**
     * Make sure the same fa code was not used in the last X minutes.
     * Technically, even 2 minutes be fine since every token is only valid for 30 sec and we only allow the 2 most
     * recent tokens.
     */
    const BLOCK_TWOFA_CODE_MINUTES = 10;

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

    private static function getUserModel()
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

    private static function isAnonymous($login)
    {
        return strtolower($login) === 'anonymous';
    }

    public function saveSecret($login, $secret)
    {
        if (self::isAnonymous($login)) {
            throw new Exception('Anonymous cannot use two-factor authentication');
        }

        if (!empty($secret) && !$this->recoveryCodeDao->getAllRecoveryCodesForLogin($login)) {
            // ensures the user has seen and ideally backuped the recovery codes... we don't create them here on demand
            throw new Exception('Cannot enable two-factor authentication, no recovery codes have been created');
        }

        $model = self::getUserModel();
        $model->updateUserFields($login, array('twofactor_secret' => $secret));
    }

    public function isUserRequiredToHaveTwoFactorEnabled()
    {
        return $this->settings->twoFactorAuthRequired->getValue();
    }

    public static function isUserUsingTwoFactorAuthentication($login)
    {
        if (self::isAnonymous($login)) {
            return false; // not possible to use auth code with anonymous
        }

        $user = self::getUser($login);
        return !empty($user['twofactor_secret']);
    }

    private static function getUser($login)
    {
        $model = self::getUserModel();
        return $model->getUser($login);
    }

    private function wasTwoFaCodeUsedRecently($login, $authCode)
    {
        $time = Option::get($this->gettwoFaCodeUsedKey($login, $authCode));
        if (empty($time)) {
            return false;
        }
        $fiveMinutes = 60 * self::BLOCK_TWOFA_CODE_MINUTES;
        if (time() - $fiveMinutes >= (int)$time) {
            return true;
        }
        return false;
    }

    private function gettwoFaCodeUsedKey($login, $authCode)
    {
        return self::OPTION_PREFIX_TWO_FA_CODE_USED . md5($login . $authCode . SettingsPiwik::getSalt());
    }

    private function setTwoFaCodeWasUsed($login, $authCode)
    {
        $table = Common::prefixTable('option');
        $bind = array($this->gettwoFaCodeUsedKey($login, $authCode), time(), 0);
        try {
            Db::query('INSERT INTO `' . $table . '` (option_name, option_value, autoload) VALUES (?, ?, ?) ', $bind);
            return true;
        } catch (Exception $e) {
            // when 2 process try to insert at same time should result in duplicate error
            return false;
        }
    }

    public function cleanupTwoFaCodesUsedRecently()
    {
        $values = Option::getLike(TwoFactorAuthentication::OPTION_PREFIX_TWO_FA_CODE_USED . '%');
        if (!empty($values)) {
            foreach ($values as $optionName => $timeCodeWasUsed) {
                $fiveMinutesAgo = time() - (60 * self::BLOCK_TWOFA_CODE_MINUTES);
                if ($timeCodeWasUsed < $fiveMinutesAgo) {
                    // delete any entry created more than 5 min ago
                    Option::delete($optionName);
                }
            }
        }
    }

    public function validateAuthCode($login, $authCode)
    {
        if (!self::isUserUsingTwoFactorAuthentication($login)) {
            return false;
        }

        $user = self::getUser($login);

        if ($this->wasTwoFaCodeUsedRecently($user['login'], $authCode)) {
            return false;
        }

        if (!$this->setTwoFaCodeWasUsed($user['login'], $authCode)) {
            return false;
        }

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
