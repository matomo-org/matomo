<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MobileMessaging;

use Piwik\Common;
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
    const SMS_FROM = 'Matomo';

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
    public function areSMSAPICredentialProvided()
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
     *
     * @return bool true if SMS API credential were validated and saved, false otherwise
     */
    public function setSMSAPICredential($provider, $credentials = array())
    {
        $this->checkCredentialManagementRights();

        $smsProviderInstance = SMSProvider::factory($provider);
        $smsProviderInstance->verifyCredential($credentials);

        $settings = $this->model->getCredentialManagerSettings();

        $settings[MobileMessaging::PROVIDER_OPTION] = $provider;
        $settings[MobileMessaging::API_KEY_OPTION] = $credentials;

        $this->model->setCredentialManagerSettings($settings);

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

        $phoneNumber = $this->sanitizePhoneNumber($phoneNumber);

        $verificationCode = "";
        for ($i = 0; $i < self::VERIFICATION_CODE_LENGTH; $i++) {
            $verificationCode .= Common::getRandomInt(0, 9);
        }

        $smsText = Piwik::translate(
            'MobileMessaging_VerificationText',
            array(
                 $verificationCode,
                 Piwik::translate('General_Settings'),
                 Piwik::translate('MobileMessaging_SettingsMenu')
            )
        );

        $this->model->sendSMS($smsText, $phoneNumber, self::SMS_FROM);

        $phoneNumbers = $this->model->retrievePhoneNumbers(Piwik::getCurrentUserLogin());
        $phoneNumbers[$phoneNumber] = $verificationCode;
        $this->model->savePhoneNumbers(Piwik::getCurrentUserLogin(), $phoneNumbers);

        $this->model->increaseCount(Piwik::getCurrentUserLogin(), MobileMessaging::PHONE_NUMBER_VALIDATION_REQUEST_COUNT_OPTION, $phoneNumber);

        return true;
    }

    /**
     * sanitize phone number
     *
     * @param string $phoneNumber
     * @return string sanitized phone number
     */
    private function sanitizePhoneNumber($phoneNumber)
    {
        return str_replace(' ', '', $phoneNumber);
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
     * remove phone number
     *
     * @param string $phoneNumber
     *
     * @return bool true
     */
    public function removePhoneNumber($phoneNumber)
    {
        Piwik::checkUserIsNotAnonymous();

        $phoneNumbers = $this->model->retrievePhoneNumbers(Piwik::getCurrentUserLogin());
        unset($phoneNumbers[$phoneNumber]);
        $this->model->savePhoneNumbers(Piwik::getCurrentUserLogin(), $phoneNumbers);

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

        $phoneNumbers = $this->model->retrievePhoneNumbers(Piwik::getCurrentUserLogin());

        if (isset($phoneNumbers[$phoneNumber])) {
            if ($verificationCode == $phoneNumbers[$phoneNumber]) {

                $phoneNumbers[$phoneNumber] = null;
                $this->model->savePhoneNumbers(Piwik::getCurrentUserLogin(), $phoneNumbers);
                return true;
            }
        }

        return false;
    }

    /**
     * delete the SMS API credential
     *
     * @return bool true
     */
    public function deleteSMSAPICredential()
    {
        $this->checkCredentialManagementRights();

        $settings = $this->model->getCredentialManagerSettings();

        $settings[MobileMessaging::API_KEY_OPTION] = null;

        $this->model->setCredentialManagerSettings($settings);

        return true;
    }

    /**
     * Specify if normal users can manage their own SMS API credential
     *
     * @param bool $delegatedManagement false if SMS API credential only manageable by super admin, true otherwise
     */
    public function setDelegatedManagement($delegatedManagement)
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->model->setDelegatedManagement($delegatedManagement);
    }

    /**
     * Determine if normal users can manage their own SMS API credential
     *
     * @return bool false if SMS API credential only manageable by super admin, true otherwise
     */
    public function getDelegatedManagement()
    {
        Piwik::checkUserHasSomeViewAccess();
        return $this->model->getDelegatedManagement();
    }

    private function checkCredentialManagementRights()
    {
        $this->getDelegatedManagement() ? Piwik::checkUserIsNotAnonymous() : Piwik::checkUserHasSuperUserAccess();
    }
}
