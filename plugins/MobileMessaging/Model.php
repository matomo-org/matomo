<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MobileMessaging;

use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;

class Model
{
    /**
     * send a SMS
     *
     * @param string $content
     * @param string $phoneNumber
     * @param string $from
     * @return bool true
     */
    public function sendSMS(string $content, string $phoneNumber, string $from): bool
    {
        $credential = $this->getSMSAPICredential();
        $SMSProvider = SMSProvider::factory($credential[MobileMessaging::PROVIDER_OPTION]);
        $SMSProvider->sendSMS(
            $credential[MobileMessaging::API_KEY_OPTION],
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
     * @param string $login
     * @return array $phoneNumber
     */
    public function getActivatedPhoneNumbers(string $login): array
    {
        return array_keys($this->getPhoneNumbers($login, true));
    }

    /**
     * Returns the list of phone numbers with their verification data
     *
     * @param string $login
     * @param bool $onlyVerified
     * @return array
     */
    public function getPhoneNumbers(string $login, bool $onlyVerified = true): array
    {
        $phoneNumbers = $this->getPhoneNumbersFromSettings($login);

        foreach ($phoneNumbers as $phoneNumber => &$verificationData) {
            // always remove verification code, as it should not be accidentally revealed somewere
            unset($verificationData['verificationCode']);
        }

        if ($onlyVerified) {
            $phoneNumbers = array_filter($phoneNumbers, function ($verificationData) {
                return $verificationData['verified'];
            });
        }

        // Sort numbers. Unverified numbers first, then sorted by verification or request time
        uasort($phoneNumbers, function ($a, $b) {
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
     * @param string $login
     * @param string $phoneNumber
     * @param string $verificationCode
     * @return bool
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
     * @param string $login
     * @param string $phoneNumber
     * @param string $verificationCode
     * @return void
     */
    public function addPhoneNumber(string $login, string $phoneNumber, string $verificationCode): void
    {
        $phoneNumbers = $this->getPhoneNumbersFromSettings($login);

        $phoneNumbers[$phoneNumber] = [
            'verified' => false,
            'verificationCode' => $verificationCode,
            'verificationTries' => 0,
            'verificationTime' => null,
            'requestTime' => Date::getNowTimestamp(),
        ];

        $this->savePhoneNumbers($login, $phoneNumbers);
    }

    /**
     * Removes a phone number
     *
     * @param string $login
     * @param string $phoneNumber
     * @return void
     */
    public function removePhoneNumber(string $login, string $phoneNumber): void
    {
        $phoneNumbers = $this->getPhoneNumbersFromSettings($login);
        unset($phoneNumbers[$phoneNumber]);
        $this->savePhoneNumbers($login, $phoneNumbers);
    }

    private function savePhoneNumbers(string $login, array $phoneNumbers): void
    {
        $settings = $this->getUserSettings($login);

        $settings[MobileMessaging::PHONE_NUMBERS_OPTION] = $phoneNumbers;

        $this->setUserSettings($login, $settings);
    }

    public function increaseCount(string $login, string $option, string $phoneNumber): void
    {
        $settings = $this->getUserSettings($login);

        $counts = array();
        if (isset($settings[$option])) {
            $counts = $settings[$option];
        }

        $countToUpdate = 0;
        if (isset($counts[$phoneNumber])) {
            $countToUpdate = $counts[$phoneNumber];
        }

        $counts[$phoneNumber] = $countToUpdate + 1;

        $settings[$option] = $counts;

        $this->setUserSettings($login, $settings);
    }

    public function getSMSAPICredential(): array
    {
        $settings = $this->getCredentialManagerSettings();

        $credentials = isset($settings[MobileMessaging::API_KEY_OPTION]) ? $settings[MobileMessaging::API_KEY_OPTION] : null;

        // fallback for older values, where api key has been stored as string value
        if (!empty($credentials) && !is_array($credentials)) {
            $credentials = [
                'apiKey' => $credentials
            ];
        }

        return [
            MobileMessaging::PROVIDER_OPTION =>
                isset($settings[MobileMessaging::PROVIDER_OPTION]) ? $settings[MobileMessaging::PROVIDER_OPTION] : null,
            MobileMessaging::API_KEY_OPTION  =>
                $credentials,
        ];
    }

    public function setCredentialManagerSettings($settings): void
    {
        $this->setUserSettings($this->getCredentialManagerLogin(), $settings);
    }

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

    private function setUserSettings(string $login, $settings): void
    {
        Option::set(
            $login . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION,
            json_encode($settings)
        );
    }

    private function getCredentialManagerLogin(): string
    {
        return $this->getDelegatedManagement() ? Piwik::getCurrentUserLogin() : '';
    }

    private function getUserSettings(string $login): array
    {
        $optionIndex = $login . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION;
        $userSettings = Option::get($optionIndex);

        if (empty($userSettings)) {
            $userSettings = [];
        } else {
            $userSettings = json_decode($userSettings, true);
        }

        return $userSettings;
    }

    private function getPhoneNumbersFromSettings(string $login): array
    {
        $settings = $this->getUserSettings($login);

        $phoneNumbers = array();
        if (isset($settings[MobileMessaging::PHONE_NUMBERS_OPTION])) {
            $phoneNumbers = $settings[MobileMessaging::PHONE_NUMBERS_OPTION];
        }

        if (!is_array($phoneNumbers) || empty($phoneNumbers)) {
            $phoneNumbers = [];
        }

        // Map old storage data to new format
        foreach ($phoneNumbers as $phoneNumber => &$verificationData) {
            if (is_string($verificationData)) {
                $verificationData = [
                    'verified' => false,
                    'verificationCode' => $verificationData,
                    'verificationTime' => null,
                    'verificationTries' => 0,
                    'requestTime' => Date::getNowTimestamp(),
                ];
            } elseif (null === $verificationData) {
                $verificationData = [
                    'verified' => true,
                    'verificationCode' => null,
                    'verificationTime' => null,
                    'verificationTries' => 0,
                    'requestTime' => Date::getNowTimestamp(),
                ];
            }
        }

        return $phoneNumbers;
    }
}
