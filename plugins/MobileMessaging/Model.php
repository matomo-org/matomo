<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MobileMessaging;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Settings\Storage\Factory;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;

/**
 * @phpstan-type PhoneVerificationData array{
 *     verified: bool,
 *     verificationCode: string|null,
 *     verificationTries: int,
 *     verificationTime: int|null,
 *     requestTime: int
 * }
 * @phpstan-type PhoneNumbers array<string, PhoneVerificationData>
 */
class Model
{
    /**
     * send a SMS
     *
     * @return bool true
     */
    public function sendSMS(string $content, string $phoneNumber, string $from): bool
    {
        $credential = $this->getSMSAPICredential();
        $provider = $credential[MobileMessaging::PROVIDER_OPTION];
        $credentials = $credential[MobileMessaging::API_KEY_OPTION];

        if (!is_string($provider) || $provider === '') {
            throw new \Exception('No SMS provider configured');
        }

        if (!is_array($credentials)) {
            $credentials = [];
        }

        $SMSProvider = SMSProvider::factory($provider);
        $SMSProvider->sendSMS(
            $credentials,
            $content,
            $phoneNumber,
            $from
        );

        $this->increaseCount(Piwik::getCurrentUserLogin(), MobileMessaging::SMS_SENT_COUNT_OPTION, $phoneNumber);

        return true;
    }

    /**
     * get activated phone number list
     *
     * @return array $phoneNumber
     */
    public function getActivatedPhoneNumbers(string $login): array
    {
        return array_keys($this->getPhoneNumbers($login, true));
    }

    /**
     * Returns the list of phone numbers with their verification data
     *
     * @return array
     * @phpstan-return PhoneNumbers
     */
    public function getPhoneNumbers(string $login, bool $onlyVerified = true): array
    {
        $phoneNumbers = $this->getPhoneNumbersFromSettings($login);

        foreach ($phoneNumbers as $phoneNumber => &$verificationData) {
            // always remove verification code, as it should not be accidentally revealed somewere
            unset($verificationData['verificationCode']);
        }

        if ($onlyVerified) {
            $phoneNumbers = array_filter($phoneNumbers, function (array $verificationData) {
                return $verificationData['verified'];
            });
        }

        // Sort numbers. Unverified numbers first, then sorted by verification or request time
        uasort($phoneNumbers, function (array $a, array $b) {
            if ($a['verified'] === $b['verified']) {
                if ($a['verified']) {
                    return $b['verificationTime'] <=> $a['verificationTime'];
                }

                return $b['requestTime'] <=> $a['requestTime'];
            }

            return $a['verified'] <=> $b['verified'];
        });

        return $phoneNumbers;
    }

    /**
     * Tries to verify the given phone number with the given verification code
     *
     */
    public function verifyPhoneNumber(string $login, string $phoneNumber, string $verificationCode): bool
    {
        $phoneNumbers = $this->getPhoneNumbersFromSettings($login);

        if (empty($phoneNumbers[$phoneNumber])) {
            return false; // phone number does not exist
        }

        if ($phoneNumbers[$phoneNumber]['verified']) {
            return true; // already verified
        }

        // unset verification code if it's older than 10 minutes
        if ($phoneNumbers[$phoneNumber]['requestTime'] < Date::getNowTimestamp() - 600) {
            $phoneNumbers[$phoneNumber]['verificationCode'] = null;
        } elseif ($phoneNumbers[$phoneNumber]['verificationCode'] !== $verificationCode) {
            // failed attempt: increase verification tries
            $phoneNumbers[$phoneNumber]['verificationTries']++;

            if ($phoneNumbers[$phoneNumber]['verificationTries'] >= 3) {
                // unset verification code after 3rd try
                $phoneNumbers[$phoneNumber]['verificationCode'] = null;
            }
        } else {
            // verification successfull
            $phoneNumbers[$phoneNumber]['verificationTries'] = 0;
            $phoneNumbers[$phoneNumber]['verificationCode'] = null;
            $phoneNumbers[$phoneNumber]['verified'] = true;
            $phoneNumbers[$phoneNumber]['verificationTime'] = Date::getNowTimestamp();
        }

        $this->savePhoneNumbers($login, $phoneNumbers);

        return $phoneNumbers[$phoneNumber]['verified'];
    }

    /**
     * Adds a new phone number to the user, which needs to be verified with the provided code first
     *
     */
    public function addPhoneNumber(string $login, string $phoneNumber, string $verificationCode): void
    {
        $phoneNumbers = $this->getPhoneNumbersFromSettings($login);

        $phoneNumbers[$phoneNumber] = [
            'verified' => false,
            'verificationCode' => $verificationCode,
            'verificationTries' => 0,
            'verificationTime' => null,
            'requestTime' => (int) Date::getNowTimestamp(),
        ];

        $this->savePhoneNumbers($login, $phoneNumbers);
    }

    /**
     * Removes a phone number
     *
     */
    public function removePhoneNumber(string $login, string $phoneNumber): void
    {
        $phoneNumbers = $this->getPhoneNumbersFromSettings($login);
        unset($phoneNumbers[$phoneNumber]);
        $this->savePhoneNumbers($login, $phoneNumbers);
    }

    /**
     * @param array $phoneNumbers
     * @phpstan-param PhoneNumbers $phoneNumbers
     */
    private function savePhoneNumbers(string $login, array $phoneNumbers): void
    {
        $settings = $this->getUserSettings($login);

        $settings[MobileMessaging::PHONE_NUMBERS_OPTION] = $phoneNumbers;

        $this->setUserSettings($login, $settings);
    }

    public function increaseCount(string $login, string $option, string $phoneNumber): void
    {
        $settings = $this->getUserSettings($login);

        $counts = [];
        if (isset($settings[$option]) && is_array($settings[$option])) {
            $counts = $settings[$option];
        }

        $countToUpdate = 0;
        if (isset($counts[$phoneNumber])) {
            $countToUpdate = (int) $counts[$phoneNumber];
        }

        $counts[$phoneNumber] = $countToUpdate + 1;

        $settings[$option] = $counts;

        $this->setUserSettings($login, $settings);
    }

    /**
     * @return array{Provider: string|null, APIKey: array<string, mixed>|null}
     */
    public function getSMSAPICredential(): array
    {
        $settings = $this->getCredentialManagerSettings();

        $provider = null;
        if (isset($settings[MobileMessaging::PROVIDER_OPTION]) && is_string($settings[MobileMessaging::PROVIDER_OPTION])) {
            $provider = $settings[MobileMessaging::PROVIDER_OPTION];
        }

        $credentials = $settings[MobileMessaging::API_KEY_OPTION] ?? null;

        // fallback for older values, where api key has been stored as string value
        if ($credentials !== null && $credentials !== '' && !is_array($credentials)) {
            if (is_scalar($credentials)) {
                $credentials = [
                    'apiKey' => (string) $credentials,
                ];
            } else {
                $credentials = null;
            }
        } elseif (!is_array($credentials)) {
            $credentials = null;
        }

        return [
            MobileMessaging::PROVIDER_OPTION => $provider,
            MobileMessaging::API_KEY_OPTION => $credentials,
        ];
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function setCredentialManagerSettings(array $settings): void
    {
        $this->setUserSettings($this->getCredentialManagerLogin(), $settings);
    }

    /**
     * @return array<string, mixed>
     */
    public function getCredentialManagerSettings(): array
    {
        return $this->getUserSettings($this->getCredentialManagerLogin());
    }

    public function getDelegatedManagement(): bool
    {
        $option = Option::get(MobileMessaging::DELEGATED_MANAGEMENT_OPTION);
        return true === $option || 'true' === $option || 1 === $option || '1' === $option;
    }

    public function setDelegatedManagement(bool $delegatedManagement): void
    {
        Option::set(MobileMessaging::DELEGATED_MANAGEMENT_OPTION, $delegatedManagement);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function setUserSettings(string $login, array $settings): void
    {
        if ($login === '') {
            $this->getFactory()->getPluginStorage('MobileMessaging', $login)->getBackend()->save($settings);
            return;
        }

        $this->getAccessManager()->setAll('MobileMessaging', $login, $settings);
    }

    private function getCredentialManagerLogin(): string
    {
        return $this->getDelegatedManagement() ? Piwik::getCurrentUserLogin() : '';
    }

    /**
     * @return array<string, mixed>
     * @deprecated Remove legacy Option fallback handling with Matomo 6.
     * @todo Remove fallback reads from legacy user/global option keys with Matomo 6.
     */
    private function getUserSettings(string $login): array
    {
        if ($login === '') {
            $settings = $this->getFactory()->getPluginStorage('MobileMessaging', $login)->getBackend()->load();
            if (is_array($settings) && !empty($settings)) {
                return $settings;
            }

            return $this->getLegacyGlobalSettings();
        }

        $userSettings = $this->getAccessManager()->getAll('MobileMessaging', $login);
        if (empty($userSettings)) {
            // @todo Remove this legacy option fallback with Matomo 6.
            $optionIndex = $login . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION;
            $userSettings = Option::get($optionIndex);
            if (empty($userSettings)) {
                $userSettings = [];
            } else {
                $userSettings = json_decode($userSettings, true);
            }
        }
        return $userSettings;
    }

    /**
     * @return array<string, mixed>
     * @deprecated Remove legacy global Option fallback handling with Matomo 6.
     * @todo Remove this method with Matomo 6.
     */
    private function getLegacyGlobalSettings(): array
    {
        $settings = Option::get(MobileMessaging::USER_SETTINGS_POSTFIX_OPTION);
        if (!is_string($settings) || $settings === '') {
            return [];
        }

        $decoded = json_decode($settings, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array
     * @phpstan-return PhoneNumbers
     */
    private function getPhoneNumbersFromSettings(string $login): array
    {
        $settings = $this->getUserSettings($login);

        $phoneNumbers = [];
        if (isset($settings[MobileMessaging::PHONE_NUMBERS_OPTION])) {
            $phoneNumbers = $settings[MobileMessaging::PHONE_NUMBERS_OPTION];
        }

        if (!is_array($phoneNumbers) || empty($phoneNumbers)) {
            $phoneNumbers = [];
        }

        foreach ($phoneNumbers as $phoneNumber => $verificationData) {
            $phoneNumbers[$phoneNumber] = $this->normalizeVerificationData($verificationData);
        }

        return $phoneNumbers;
    }

    /**
     * @param mixed $verificationData
     * @return array
     * @phpstan-return PhoneVerificationData
     */
    private function normalizeVerificationData($verificationData): array
    {
        if (is_string($verificationData)) {
            return [
                'verified' => false,
                'verificationCode' => $verificationData,
                'verificationTime' => null,
                'verificationTries' => 0,
                'requestTime' => (int) Date::getNowTimestamp(),
            ];
        }

        if ($verificationData === null) {
            return [
                'verified' => true,
                'verificationCode' => null,
                'verificationTime' => null,
                'verificationTries' => 0,
                'requestTime' => (int) Date::getNowTimestamp(),
            ];
        }

        if (!is_array($verificationData)) {
            return [
                'verified' => false,
                'verificationCode' => null,
                'verificationTime' => null,
                'verificationTries' => 0,
                'requestTime' => (int) Date::getNowTimestamp(),
            ];
        }

        return [
            'verified' => !empty($verificationData['verified']),
            'verificationCode' => isset($verificationData['verificationCode']) ? (string) $verificationData['verificationCode'] : null,
            'verificationTime' => isset($verificationData['verificationTime']) ? (int) $verificationData['verificationTime'] : null,
            'verificationTries' => isset($verificationData['verificationTries']) ? (int) $verificationData['verificationTries'] : 0,
            'requestTime' => isset($verificationData['requestTime']) ? (int) $verificationData['requestTime'] : (int) Date::getNowTimestamp(),
        ];
    }

    private function getAccessManager(): UserScopedSettingsAccessManager
    {
        return StaticContainer::get(UserScopedSettingsAccessManager::class);
    }

    private function getFactory(): Factory
    {
        return StaticContainer::get(Factory::class);
    }
}
