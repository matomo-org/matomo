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

/**
 * The MobileMessaging API lets you manage SMS credentials, phone number verification, and SMS account settings.
 *
 * @phpstan-import-type PhoneNumbers from Model
 *
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
     * Checks whether SMS API credentials are configured for the current user.
     *
     * @return bool `true` if SMS API credentials are available for the current user.
     */
    public function areSMSAPICredentialProvided(): bool
    {
        Piwik::checkUserHasSomeViewAccess();

        $credential = $this->model->getSMSAPICredential();
        return isset($credential[MobileMessaging::API_KEY_OPTION]);
    }

    /**
     * Returns the configured SMS provider for the current user.
     *
     * @return string|null The configured SMS provider identifier, or `null` if none is configured.
     */
    public function getSMSProvider(): ?string
    {
        $this->checkCredentialManagementRights();
        $credential = $this->model->getSMSAPICredential();
        return $credential[MobileMessaging::PROVIDER_OPTION];
    }

    /**
     * Stores the SMS API credentials for the selected provider.
     *
     * @param string $provider SMS provider identifier to configure.
     * @param array<string, string|int|null> $credentials Provider credentials such as an API key or username.
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
     * Adds a phone number for the current user and sends a verification code to it.
     *
     * @param string $phoneNumber Phone number in international format.
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
     * Requests a new verification code for a pending phone number.
     *
     * @param string $phoneNumber Phone number in international format.
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
                Piwik::translate('MobileMessaging_SettingsMenu'),
            )
        );

        $this->model->sendSMS($smsText, $phoneNumber, self::SMS_FROM);

        $this->model->addPhoneNumber(Piwik::getCurrentUserLogin(), $phoneNumber, $verificationCode);
    }

    private function sanitizePhoneNumber(string $phoneNumber): string
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
     * Returns the remaining SMS credit for the configured provider account.
     *
     * @return int|string Remaining SMS credit reported by the configured provider.
     */
    public function getCreditLeft()
    {
        $this->checkCredentialManagementRights();

        $credential = $this->model->getSMSAPICredential();
        $SMSProvider = SMSProvider::factory($credential[MobileMessaging::PROVIDER_OPTION] ?? '');
        return $SMSProvider->getCreditLeft(
            $credential[MobileMessaging::API_KEY_OPTION] ?? []
        );
    }

    /**
     * Returns the phone numbers configured for the current user.
     *
     * @return array Phone numbers keyed by phone number, including verification metadata.
     * @phpstan-return PhoneNumbers
     */
    public function getPhoneNumbers(): array
    {
        Piwik::checkUserIsNotAnonymous();

        return $this->model->getPhoneNumbers(Piwik::getCurrentUserLogin(), false);
    }

    /**
     * Removes a phone number from the current user account.
     *
     * @param string $phoneNumber Phone number in international format.
     */
    public function removePhoneNumber(string $phoneNumber): void
    {
        Piwik::checkUserIsNotAnonymous();

        $phoneNumber = $this->sanitizePhoneNumber($phoneNumber);

        $this->model->removePhoneNumber(Piwik::getCurrentUserLogin(), $phoneNumber);

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
     * Verifies a phone number using the submitted verification code.
     *
     * @param string $phoneNumber Phone number in international format.
     * @param string $verificationCode Verification code received by SMS.
     * @return bool `true` if the phone number was verified successfully, `false` otherwise.
     */
    public function validatePhoneNumber(string $phoneNumber, string $verificationCode): bool
    {
        Piwik::checkUserIsNotAnonymous();

        $phoneNumber = $this->sanitizePhoneNumber($phoneNumber);

        return $this->model->verifyPhoneNumber(Piwik::getCurrentUserLogin(), $phoneNumber, $verificationCode);
    }

    /**
     * Deletes the configured SMS API credentials.
     *
     */
    public function deleteSMSAPICredential(): void
    {
        $this->checkCredentialManagementRights();

        $settings = $this->model->getCredentialManagerSettings();

        $settings[MobileMessaging::API_KEY_OPTION] = null;

        $this->model->setCredentialManagerSettings($settings);
    }

    /**
     * Configures whether regular users can manage their own SMS API credentials.
     *
     * @param bool $delegatedManagement `true` to allow regular users to manage their own credentials, `false` to
     *                                  restrict management to super users.
     */
    public function setDelegatedManagement(bool $delegatedManagement): void
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->model->setDelegatedManagement($delegatedManagement);
    }

    /**
     * Returns whether regular users can manage their own SMS API credentials.
     *
     * @return bool `true` if regular users can manage their own credentials, `false` if only super users can.
     */
    public function getDelegatedManagement(): bool
    {
        Piwik::checkUserHasSomeViewAccess();
        return $this->model->getDelegatedManagement();
    }

    private function checkCredentialManagementRights(): void
    {
        $this->getDelegatedManagement() ? Piwik::checkUserIsNotAnonymous() : Piwik::checkUserHasSuperUserAccess();
    }
}
