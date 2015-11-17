<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MobileMessaging;

use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\MobileMessaging\SMSProvider;

/**
 * The MobileMessaging API lets you manage and access all the MobileMessaging plugin features including :
 *  - manage SMS API credential
 *  - activate phone numbers
 *  - check remaining credits
 *  - send SMS
 * @method static \Piwik\Plugins\MobileMessaging\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    const VERIFICATION_CODE_LENGTH = 5;
    const SMS_FROM = 'Piwik';

    /**
     * determine if SMS API credential are available for the current user
     *
     * @return bool true if SMS API credential are available for the current user
     */
    public function areSMSAPICredentialProvided()
    {
        Piwik::checkUserHasSomeViewAccess();

        $credential = $this->getSMSAPICredential();
        return isset($credential[MobileMessaging::API_KEY_OPTION]);
    }

    private function getSMSAPICredential()
    {
        $settings = $this->getCredentialManagerSettings();
        return array(
            MobileMessaging::PROVIDER_OPTION =>
                isset($settings[MobileMessaging::PROVIDER_OPTION]) ? $settings[MobileMessaging::PROVIDER_OPTION] : null,
            MobileMessaging::API_KEY_OPTION  =>
                isset($settings[MobileMessaging::API_KEY_OPTION]) ? $settings[MobileMessaging::API_KEY_OPTION] : null,
        );
    }

    /**
     * return the SMS API Provider for the current user
     *
     * @return string SMS API Provider
     */
    public function getSMSProvider()
    {
        $this->checkCredentialManagementRights();
        $credential = $this->getSMSAPICredential();
        return $credential[MobileMessaging::PROVIDER_OPTION];
    }

    /**
     * set the SMS API credential
     *
     * @param string $provider SMS API provider
     * @param string $apiKey API Key
     *
     * @return bool true if SMS API credential were validated and saved, false otherwise
     */
    public function setSMSAPICredential($provider, $apiKey)
    {
        $this->checkCredentialManagementRights();

        $smsProviderInstance = SMSProvider::factory($provider);
        $smsProviderInstance->verifyCredential($apiKey);

        $settings = $this->getCredentialManagerSettings();

        $settings[MobileMessaging::PROVIDER_OPTION] = $provider;
        $settings[MobileMessaging::API_KEY_OPTION] = $apiKey;

        $this->setCredentialManagerSettings($settings);

        return true;
    }

    /**
     * add phone number
     *
     * @param string $phoneNumber
     *
     * @return bool true
     */
    public function addPhoneNumber($phoneNumber)
    {
        Piwik::checkUserIsNotAnonymous();

        $phoneNumber = self::sanitizePhoneNumber($phoneNumber);

        $verificationCode = "";
        for ($i = 0; $i < self::VERIFICATION_CODE_LENGTH; $i++) {
            $verificationCode .= mt_rand(0, 9);
        }

        $smsText = Piwik::translate(
            'MobileMessaging_VerificationText',
            array(
                 $verificationCode,
                 Piwik::translate('General_Settings'),
                 Piwik::translate('MobileMessaging_SettingsMenu')
            )
        );

        $this->sendSMS($smsText, $phoneNumber, self::SMS_FROM);

        $phoneNumbers = $this->retrievePhoneNumbers();
        $phoneNumbers[$phoneNumber] = $verificationCode;
        $this->savePhoneNumbers($phoneNumbers);

        $this->increaseCount(MobileMessaging::PHONE_NUMBER_VALIDATION_REQUEST_COUNT_OPTION, $phoneNumber);

        return true;
    }

    /**
     * sanitize phone number
     *
     * @ignore
     * @param string $phoneNumber
     * @return string sanitized phone number
     */
    public static function sanitizePhoneNumber($phoneNumber)
    {
        return str_replace(' ', '', $phoneNumber);
    }

    /**
     * send a SMS
     *
     * @param string $content
     * @param string $phoneNumber
     * @param string $from
     * @return bool true
     * @ignore
     */
    public function sendSMS($content, $phoneNumber, $from)
    {
        Piwik::checkUserIsNotAnonymous();

        $credential = $this->getSMSAPICredential();
        $SMSProvider = SMSProvider::factory($credential[MobileMessaging::PROVIDER_OPTION]);
        $SMSProvider->sendSMS(
            $credential[MobileMessaging::API_KEY_OPTION],
            $content,
            $phoneNumber,
            $from
        );

        $this->increaseCount(MobileMessaging::SMS_SENT_COUNT_OPTION, $phoneNumber);

        return true;
    }

    /**
     * get remaining credit
     *
     * @return string remaining credit
     */
    public function getCreditLeft()
    {
        $this->checkCredentialManagementRights();

        $credential = $this->getSMSAPICredential();
        $SMSProvider = SMSProvider::factory($credential[MobileMessaging::PROVIDER_OPTION]);
        return $SMSProvider->getCreditLeft(
            $credential[MobileMessaging::API_KEY_OPTION]
        );
    }

    /**
     * remove phone number
     *
     * @param string $phoneNumber
     *
     * @return bool true
     */
    public function removePhoneNumber($phoneNumber)
    {
        Piwik::checkUserIsNotAnonymous();

        $phoneNumbers = $this->retrievePhoneNumbers();
        unset($phoneNumbers[$phoneNumber]);
        $this->savePhoneNumbers($phoneNumbers);

        /**
         * Triggered after a phone number has been deleted. This event should be used to clean up any data that is
         * related to the now deleted phone number. The ScheduledReports plugin, for example, uses this event to remove
         * the phone number from all reports to make sure no text message will be sent to this phone number.
         *
         * **Example**
         *
         *     public function deletePhoneNumber($phoneNumber)
         *     {
         *         $this->unsubscribePhoneNumberFromScheduledReport($phoneNumber);
         *     }
         *
         * @param string $phoneNumber The phone number that was just deleted.
         */
        Piwik::postEvent('MobileMessaging.deletePhoneNumber', array($phoneNumber));

        return true;
    }

    private function retrievePhoneNumbers()
    {
        $settings = $this->getCurrentUserSettings();

        $phoneNumbers = array();
        if (isset($settings[MobileMessaging::PHONE_NUMBERS_OPTION])) {
            $phoneNumbers = $settings[MobileMessaging::PHONE_NUMBERS_OPTION];
        }

        return $phoneNumbers;
    }

    private function savePhoneNumbers($phoneNumbers)
    {
        $settings = $this->getCurrentUserSettings();

        $settings[MobileMessaging::PHONE_NUMBERS_OPTION] = $phoneNumbers;

        $this->setCurrentUserSettings($settings);
    }

    private function increaseCount($option, $phoneNumber)
    {
        $settings = $this->getCurrentUserSettings();

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

        $this->setCurrentUserSettings($settings);
    }

    /**
     * validate phone number
     *
     * @param string $phoneNumber
     * @param string $verificationCode
     *
     * @return bool true if validation code is correct, false otherwise
     */
    public function validatePhoneNumber($phoneNumber, $verificationCode)
    {
        Piwik::checkUserIsNotAnonymous();

        $phoneNumbers = $this->retrievePhoneNumbers();

        if (isset($phoneNumbers[$phoneNumber])) {
            if ($verificationCode == $phoneNumbers[$phoneNumber]) {

                $phoneNumbers[$phoneNumber] = null;
                $this->savePhoneNumbers($phoneNumbers);
                return true;
            }
        }

        return false;
    }

    /**
     * get phone number list
     *
     * @return array $phoneNumber => $isValidated
     * @ignore
     */
    public function getPhoneNumbers()
    {
        Piwik::checkUserIsNotAnonymous();

        $rawPhoneNumbers = $this->retrievePhoneNumbers();

        $phoneNumbers = array();
        foreach ($rawPhoneNumbers as $phoneNumber => $verificationCode) {
            $phoneNumbers[$phoneNumber] = self::isActivated($verificationCode);
        }

        return $phoneNumbers;
    }

    /**
     * get activated phone number list
     *
     * @return array $phoneNumber
     * @ignore
     */
    public function getActivatedPhoneNumbers()
    {
        Piwik::checkUserIsNotAnonymous();

        $phoneNumbers = $this->retrievePhoneNumbers();

        $activatedPhoneNumbers = array();
        foreach ($phoneNumbers as $phoneNumber => $verificationCode) {
            if (self::isActivated($verificationCode)) {
                $activatedPhoneNumbers[] = $phoneNumber;
            }
        }

        return $activatedPhoneNumbers;
    }

    private static function isActivated($verificationCode)
    {
        return $verificationCode === null;
    }

    /**
     * delete the SMS API credential
     *
     * @return bool true
     */
    public function deleteSMSAPICredential()
    {
        $this->checkCredentialManagementRights();

        $settings = $this->getCredentialManagerSettings();

        $settings[MobileMessaging::API_KEY_OPTION] = null;

        $this->setCredentialManagerSettings($settings);

        return true;
    }

    private function checkCredentialManagementRights()
    {
        $this->getDelegatedManagement() ? Piwik::checkUserIsNotAnonymous() : Piwik::checkUserHasSuperUserAccess();
    }

    private function setUserSettings($user, $settings)
    {
        Option::set(
            $user . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION,
            json_encode($settings)
        );
    }

    private function setCurrentUserSettings($settings)
    {
        $this->setUserSettings(Piwik::getCurrentUserLogin(), $settings);
    }

    private function setCredentialManagerSettings($settings)
    {
        $this->setUserSettings($this->getCredentialManagerLogin(), $settings);
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

    private function getCredentialManagerSettings()
    {
        return $this->getUserSettings($this->getCredentialManagerLogin());
    }

    private function getCurrentUserSettings()
    {
        return $this->getUserSettings(Piwik::getCurrentUserLogin());
    }

    /**
     * Specify if normal users can manage their own SMS API credential
     *
     * @param bool $delegatedManagement false if SMS API credential only manageable by super admin, true otherwise
     */
    public function setDelegatedManagement($delegatedManagement)
    {
        Piwik::checkUserHasSuperUserAccess();
        Option::set(MobileMessaging::DELEGATED_MANAGEMENT_OPTION, $delegatedManagement);
    }

    /**
     * Determine if normal users can manage their own SMS API credential
     *
     * @return bool false if SMS API credential only manageable by super admin, true otherwise
     */
    public function getDelegatedManagement()
    {
        Piwik::checkUserHasSomeViewAccess();
        return Option::get(MobileMessaging::DELEGATED_MANAGEMENT_OPTION) == 'true';
    }
}
