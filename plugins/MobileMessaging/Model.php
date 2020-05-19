<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MobileMessaging;

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
    public function sendSMS($content, $phoneNumber, $from)
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
    public function getActivatedPhoneNumbers($login)
    {
        $phoneNumbers = $this->retrievePhoneNumbers($login);

        $activatedPhoneNumbers = array();
        foreach ($phoneNumbers as $phoneNumber => $verificationCode) {
            if ($this->isActivated($verificationCode)) {
                $activatedPhoneNumbers[] = $phoneNumber;
            }
        }

        return $activatedPhoneNumbers;
    }

    public function retrievePhoneNumbers($login)
    {
        $settings = $this->getUserSettings($login);

        $phoneNumbers = array();
        if (isset($settings[MobileMessaging::PHONE_NUMBERS_OPTION])) {
            $phoneNumbers = $settings[MobileMessaging::PHONE_NUMBERS_OPTION];
        }

        return $phoneNumbers;
    }

    public function savePhoneNumbers($login, $phoneNumbers)
    {
        $settings = $this->getUserSettings($login);

        $settings[MobileMessaging::PHONE_NUMBERS_OPTION] = $phoneNumbers;

        $this->setUserSettings($login, $settings);
    }

    public function increaseCount($login, $option, $phoneNumber)
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

    public function getSMSAPICredential()
    {
        $settings = $this->getCredentialManagerSettings();

        $credentials = isset($settings[MobileMessaging::API_KEY_OPTION]) ? $settings[MobileMessaging::API_KEY_OPTION] : null;

        // fallback for older values, where api key has been stored as string value
        if (!empty($credentials) && !is_array($credentials)) {
            $credentials = array(
                'apiKey' => $credentials
            );
        }

        return array(
            MobileMessaging::PROVIDER_OPTION =>
                isset($settings[MobileMessaging::PROVIDER_OPTION]) ? $settings[MobileMessaging::PROVIDER_OPTION] : null,
            MobileMessaging::API_KEY_OPTION  =>
                $credentials,
        );
    }

    /**
     * get phone number list
     *
     * @param string $login
     * @return array $phoneNumber => $isValidated
     */
    public function getPhoneNumbers($login)
    {
        $rawPhoneNumbers = $this->retrievePhoneNumbers($login);

        $phoneNumbers = array();
        foreach ($rawPhoneNumbers as $phoneNumber => $verificationCode) {
            $phoneNumbers[$phoneNumber] = $this->isActivated($verificationCode);
        }

        return $phoneNumbers;
    }

    public function setCredentialManagerSettings($settings)
    {
        $this->setUserSettings($this->getCredentialManagerLogin(), $settings);
    }

    public function getCredentialManagerSettings()
    {
        return $this->getUserSettings($this->getCredentialManagerLogin());
    }

    public function getDelegatedManagement()
    {
        $option = Option::get(MobileMessaging::DELEGATED_MANAGEMENT_OPTION);
        return $option === 'true';
    }

    public function setDelegatedManagement($delegatedManagement)
    {
        Option::set(MobileMessaging::DELEGATED_MANAGEMENT_OPTION, $delegatedManagement);
    }

    private function isActivated($verificationCode)
    {
        return $verificationCode === null;
    }

    private function setUserSettings($login, $settings)
    {
        Option::set(
            $login . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION,
            json_encode($settings)
        );
    }

    private function getCredentialManagerLogin()
    {
        return $this->getDelegatedManagement() ? Piwik::getCurrentUserLogin() : '';
    }

    private function getUserSettings($user)
    {
        $optionIndex = $user . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION;
        $userSettings = Option::get($optionIndex);

        if (empty($userSettings)) {
            $userSettings = array();
        } else {
            $userSettings = json_decode($userSettings, true);
        }

        return $userSettings;
    }
}