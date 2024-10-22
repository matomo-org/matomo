<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MobileMessaging;

use Piwik\Common;
use Piwik\Date;
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
    public const VERIFICATION_CODE_LENGTH = 5;
    public const SMS_FROM = 'Matomo';

    /** @var Model $model */
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * determine if SMS API credential are available for the current user
     *
     * @return bool true if SMS API credential are available for the current user
     */
    public function areSMSAPICredentialProvided(): bool
    {
        Piwik::checkUserHasSomeViewAccess();

        $credential = $this->model->getSMSAPICredential();
        return isset($credential[MobileMessaging::API_KEY_OPTION]);
    }

    /**
     * return the SMS API Provider for the current user
     *
     * @return string SMS API Provider
     */
    public function getSMSProvider()
    {
        $this->checkCredentialManagementRights();
        $credential = $this->model->getSMSAPICredential();
        return $credential[MobileMessaging::PROVIDER_OPTION];
    }

    /**
     * set the SMS API credential
     *
     * @param string $provider SMS API provider
     * @param array $credentials array with data like API Key or username
     * @return void
     */
    public function setSMSAPICredential(string $provider, array $credentials = []): void
    {
        $this->checkCredentialManagementRights();

        $smsProviderInstance = SMSProvider::factory($provider);
        $smsProviderInstance->verifyCredential($credentials);

        $settings = $this->model->getCredentialManagerSettings();

        $settings[MobileMessaging::PROVIDER_OPTION] = $provider;
        $settings[MobileMessaging::API_KEY_OPTION] = $credentials;

        $this->model->setCredentialManagerSettings($settings);
    }

    /**
     * Adds a phone number for the current user
     *
     * @param string $phoneNumber
     * @return void
     */
    public function addPhoneNumber(string $phoneNumber): void
    {
        Piwik::checkUserIsNotAnonymous();

        $phoneNumber = $this->sanitizePhoneNumber($phoneNumber);

        // Check format matches the international public telecommunication numbering plan (E.164)
        // See https://en.wikipedia.org/wiki/E.164
        if (!preg_match('/^\+[0-9]{5,30}$/', $phoneNumber)) {
            throw new \Exception(Piwik::translate('MobileMessaging_IncorrectNumberFormat', $phoneNumber));
        }

        $phoneNumbers = $this->model->getPhoneNumbers(Piwik::getCurrentUserLogin(), false);

        if (!empty($phoneNumbers[$phoneNumber])) {
            throw new \Exception(Piwik::translate('MobileMessaging_NumberAlreadyAdded', $phoneNumber));
        }

        $unverifiedPhoneNumbers = array_filter(
            $phoneNumbers,
            function ($phoneNumber) {
                return !$phoneNumber['verified'];
            }
        );

        if (count($unverifiedPhoneNumbers) >= 3) {
            throw new \Exception(Piwik::translate('MobileMessaging_TooManyUnverifiedNumbersError'));
        }

        $this->sendVerificationCodeAndAddPhoneNumber($phoneNumber);
    }

    /**
     * Requests a new verification code for the given phone number
     *
     * @param string $phoneNumber
     * @return void
     */
    public function resendVerificationCode(string $phoneNumber): void
    {
        Piwik::checkUserIsNotAnonymous();

        $phoneNumber = $this->sanitizePhoneNumber($phoneNumber);

        $phoneNumbers = $this->model->getPhoneNumbers(Piwik::getCurrentUserLogin(), false);

        if (empty($phoneNumbers[$phoneNumber])) {
            throw new \Exception("The phone number $phoneNumber has not yet been added.");
        }

        if (true === $phoneNumbers[$phoneNumber]['verified']) {
            throw new \Exception("The phone number $phoneNumber has already been verified.");
        }

        if ($phoneNumbers[$phoneNumber]['requestTime'] > Date::getNowTimestamp() - 60) {
            throw new \Exception(Piwik::translate('MobileMessaging_VerificationCodeRecentlySentError', $phoneNumber));
        }

        $this->sendVerificationCodeAndAddPhoneNumber($phoneNumber);
    }

    private function sendVerificationCodeAndAddPhoneNumber(string $phoneNumber): void
    {
        $verificationCode = Common::getRandomString(6, 'abcdefghijklmnoprstuvwxyz0123456789');

        $smsText = Piwik::translate(
            'MobileMessaging_VerificationText',
            array(
                $verificationCode,
                Piwik::translate('General_Settings'),
                Piwik::translate('MobileMessaging_SettingsMenu')
            )
        );

        $this->model->sendSMS($smsText, $phoneNumber, self::SMS_FROM);

        $this->model->addPhoneNumber(Piwik::getCurrentUserLogin(), $phoneNumber, $verificationCode);
    }

    /**
     * Sanitize phone number
     *
     * @param string $phoneNumber
     * @return string sanitized phone number
     */
    private function sanitizePhoneNumber($phoneNumber)
    {
        // remove common formatting characters: - _ ( )
        $phoneNumber = str_replace(['-', '_', ' ', '(', ')'], '', $phoneNumber);

        // Avoid that any method tries to handle phone numbers that are obviously too long
        if (strlen($phoneNumber) > 100) {
            throw new \Exception(Piwik::translate('MobileMessaging_IncorrectNumberFormat', $phoneNumber));
        }

        return $phoneNumber;
    }

    /**
     * get remaining credit
     *
     * @return string remaining credit
     */
    public function getCreditLeft()
    {
        $this->checkCredentialManagementRights();

        $credential = $this->model->getSMSAPICredential();
        $SMSProvider = SMSProvider::factory($credential[MobileMessaging::PROVIDER_OPTION]);
        return $SMSProvider->getCreditLeft(
            $credential[MobileMessaging::API_KEY_OPTION]
        );
    }

    /**
     * @return array
     * @throws \Piwik\NoAccessException
     */
    public function getPhoneNumbers()
    {
        Piwik::checkUserIsNotAnonymous();

        return $this->model->getPhoneNumbers(Piwik::getCurrentUserLogin(), false);
    }

    /**
     * remove phone number
     *
     * @param string $phoneNumber
     *
     * @return void
     */
    public function removePhoneNumber(string $phoneNumber): void
    {
        Piwik::checkUserIsNotAnonymous();

        $phoneNumber = $this->sanitizePhoneNumber($phoneNumber);

        $phoneNumbers = $this->model->removePhoneNumber(Piwik::getCurrentUserLogin(), $phoneNumber);

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
    }

    /**
     * Verify a phone number
     *
     * @param string $phoneNumber
     * @param string $verificationCode
     *
     * @return bool true if verification was successful, false otherwise
     */
    public function validatePhoneNumber(string $phoneNumber, string $verificationCode)
    {
        Piwik::checkUserIsNotAnonymous();

        $phoneNumber = $this->sanitizePhoneNumber($phoneNumber);

        return $this->model->verifyPhoneNumber(Piwik::getCurrentUserLogin(), $phoneNumber, $verificationCode);
    }

    /**
     * delete the SMS API credential
     *
     * @return void
     */
    public function deleteSMSAPICredential(): void
    {
        $this->checkCredentialManagementRights();

        $settings = $this->model->getCredentialManagerSettings();

        $settings[MobileMessaging::API_KEY_OPTION] = null;

        $this->model->setCredentialManagerSettings($settings);
    }

    /**
     * Specify if normal users can manage their own SMS API credential
     *
     * @param bool $delegatedManagement false if SMS API credential only manageable by super admin, true otherwise
     * @return void
     */
    public function setDelegatedManagement(bool $delegatedManagement): void
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->model->setDelegatedManagement($delegatedManagement);
    }

    /**
     * Determine if normal users can manage their own SMS API credential
     *
     * @return bool false if SMS API credential only manageable by super admin, true otherwise
     */
    public function getDelegatedManagement(): bool
    {
        Piwik::checkUserHasSomeViewAccess();
        return $this->model->getDelegatedManagement();
    }

    private function checkCredentialManagementRights()
    {
        $this->getDelegatedManagement() ? Piwik::checkUserIsNotAnonymous() : Piwik::checkUserHasSuperUserAccess();
    }
}
