<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    public const OPTION_PREFIX_TWO_FA_CODE_USED = 'twofa_codes_used_';

    /**
     * Make sure the same fa code was not used in the last X minutes.
     * Technically, even 2 minutes be fine since every token is only valid for 30 sec and we only allow the 2 most
     * recent tokens.
     *
     * @var int
     */
    public const BLOCK_TWOFA_CODE_MINUTES = 10;

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

    private static function getUserModel(): Model
    {
        return new Model();
    }

    /**
     * @return string
     */
    public function generateSecret()
    {
        return $this->secretGenerator->generateSecret();
    }

    /**
     * @param string $login
     * @return void
     */
    public function disable2FAforUser($login)
    {
        $this->saveSecret($login, '');
        $this->recoveryCodeDao->deleteAllRecoveryCodesForLogin($login);

        Piwik::postEvent('TwoFactorAuth.disabled', array($login));
    }

    private static function isAnonymous(string $login): bool
    {
        return strtolower($login) === 'anonymous';
    }

    /**
     * @param string $login
     * @param string $secret
     * @return void
     */
    public function saveSecret(
        $login,
        #[\SensitiveParameter]
        $secret
    ) {
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

    /**
     * @return bool
     */
    public function isUserRequiredToHaveTwoFactorEnabled()
    {
        return !!$this->settings->twoFactorAuthRequired->getValue();
    }

    /**
     * @param string $login
     * @return bool
     */
    public static function isUserUsingTwoFactorAuthentication($login)
    {
        if (self::isAnonymous($login)) {
            return false; // not possible to use auth code with anonymous
        }

        $user = self::getUser($login);
        return !empty($user['twofactor_secret']);
    }

    private static function getUser(string $login): array
    {
        $model = self::getUserModel();
        return $model->getUser($login);
    }

    private function wasTwoFaCodeUsedRecently(
        string $login,
        #[\SensitiveParameter]
        string $authCode
    ): bool {
        $time = Option::get($this->gettwoFaCodeUsedKey($login, $authCode));
        if (empty($time)) {
            return false;
        }
        $blockWindowSeconds = 60 * self::BLOCK_TWOFA_CODE_MINUTES;
        return (int)$time >= time() - $blockWindowSeconds;
    }

    private function gettwoFaCodeUsedKey(
        string $login,
        #[\SensitiveParameter]
        string $authCode
    ): string {
        return self::OPTION_PREFIX_TWO_FA_CODE_USED . md5($login . $authCode . SettingsPiwik::getSalt());
    }

    private function setTwoFaCodeWasUsed(
        string $login,
        #[\SensitiveParameter]
        string $authCode
    ): bool {
        $table = Common::prefixTable('option');
        $optionName = $this->gettwoFaCodeUsedKey($login, $authCode);
        $currentTime = time();
        $bind = [$optionName, $currentTime, 0];
        try {
            Db::query('INSERT INTO `' . $table . '` (option_name, option_value, autoload) VALUES (?, ?, ?) ', $bind);
            Option::clearCachedOption($optionName);
            return true;
        } catch (Exception $e) {
            // when 2 processes try to insert at same time this can fail with duplicate key.
            // if the record is older than the block window, refresh the timestamp and allow usage again.
            $blockWindowSeconds = 60 * self::BLOCK_TWOFA_CODE_MINUTES;
            $staleThreshold = $currentTime - $blockWindowSeconds;
            $updateBind = [$currentTime, $optionName, $staleThreshold];

            $result = Db::query(
                'UPDATE `' . $table . '` SET option_value = ?, autoload = 0 WHERE option_name = ? AND CAST(option_value AS UNSIGNED) <= ?',
                $updateBind
            );

            $didUpdateRecord = (bool) Db::get()->rowCount($result);
            if ($didUpdateRecord) {
                Option::clearCachedOption($optionName);
            }

            return $didUpdateRecord;
        }
    }

    /**
     * @return void
     */
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

    /**
     * @param string $login
     * @param string $authCode
     * @return bool
     */
    public function validateAuthCode(
        $login,
        #[\SensitiveParameter]
        $authCode
    ) {
        if (!self::isUserUsingTwoFactorAuthentication($login)) {
            return false;
        }

        $user = self::getUser($login);

        if (!is_string($authCode)) {
            return false;
        }

        if ($this->wasTwoFaCodeUsedRecently($user['login'], $authCode)) {
            return false;
        }

        if (!$this->setTwoFaCodeWasUsed($user['login'], $authCode)) {
            return false;
        }

        if (
            !empty($user['twofactor_secret'])
            && $this->validateAuthCodeDuringSetup($authCode, $user['twofactor_secret'])
        ) {
            return true;
        }

        if ($this->recoveryCodeDao->useRecoveryCode($user['login'], $authCode)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $authCode
     * @param string $secret
     * @return bool
     */
    public function validateAuthCodeDuringSetup(
        #[\SensitiveParameter]
        $authCode,
        #[\SensitiveParameter]
        $secret
    ) {
        $twoFactorAuth = $this->makeAuthenticator();

        if (!empty($secret) && $twoFactorAuth->verifyCode($secret, $authCode, 2)) {
            return true;
        }
        return false;
    }

    private function makeAuthenticator(): \TwoFactorAuthenticator
    {
        return new \TwoFactorAuthenticator();
    }
}
