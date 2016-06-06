<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MobileMessaging;

use Piwik\Container\StaticContainer;
use Piwik\Plugin;
use Piwik\Piwik;

/**
 * The SMSProvider abstract class is used as a base class for SMS provider implementations. To create your own custom
 * SMSProvider extend this class and implement the methods to send text messages. The class needs to be placed in a
 * `SMSProvider` directory of your plugin.
 *
 * @api
 */
abstract class SMSProvider
{
    const MAX_GSM_CHARS_IN_ONE_UNIQUE_SMS = 160;
    const MAX_GSM_CHARS_IN_ONE_CONCATENATED_SMS = 153;
    const MAX_UCS2_CHARS_IN_ONE_UNIQUE_SMS = 70;
    const MAX_UCS2_CHARS_IN_ONE_CONCATENATED_SMS = 67;

    /**
     * Get the ID of the SMS Provider. Eg 'Clockwork' or 'FreeMobile'
     * @return string
     */
    abstract public function getId();

    /**
     * Get a description about the SMS Provider. For example who the SMS Provider is, instructions how the API Key
     * needs to be set, and more. You may return HTML here for better formatting.
     *
     * @return string
     */
    abstract public function getDescription();

    /**
     * Verify the SMS API credential.
     *
     * @param string $apiKey API Key
     * @return bool true if SMS API Key is valid, false otherwise
     */
    abstract public function verifyCredential($apiKey);

    /**
     * Get the amount of remaining credits.
     *
     * @param string $apiKey API Key
     * @return string remaining credits
     */
    abstract public function getCreditLeft($apiKey);

    /**
     * Actually send the given text message. This method should only send the text message, it should not trigger
     * any notifications etc.
     *
     * @param string $apiKey
     * @param string $smsText
     * @param string $phoneNumber
     * @param string $from
     * @return bool true
     */
    abstract public function sendSMS($apiKey, $smsText, $phoneNumber, $from);

    /**
     * Defines whether the SMS Provider is available. If a certain provider should be used only be a limited
     * range of users you can restrict the provider here. For example there is a Development SMS Provider that is only
     * available when the development is actually enabled. You could also create a SMS Provider that is only available
     * to Super Users etc. Usually this method does not have to be implemented by a SMS Provider.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @param string $provider The name of the string
     * @return SMSProvider
     * @throws \Exception
     * @ignore
     */
    public static function factory($provider)
    {
        $providers = self::findAvailableSmsProviders();

        if (!array_key_exists($provider, $providers)) {
            throw new \Exception(Piwik::translate('MobileMessaging_Exception_UnknownProvider',
                array($provider, implode(', ', array_keys($providers)))
            ));
        }

        return $providers[$provider];
    }

    /**
     * Returns all available SMS Providers
     *
     * @return SMSProvider[]
     * @ignore
     */
    public static function findAvailableSmsProviders()
    {
        /** @var SMSProvider[] $smsProviders */
        $smsProviders = Plugin\Manager::getInstance()->findMultipleComponents('SMSProvider', 'Piwik\Plugins\MobileMessaging\SMSProvider');

        $providers = array();

        foreach ($smsProviders as $provider) {
            /** @var SMSProvider $provider */
            $provider = StaticContainer::get($provider);
            if ($provider->isAvailable()) {
                $providers[$provider->getId()] = $provider;
            }
        }

        return $providers;
    }

    /**
     * Assert whether a given String contains UCS2 characters
     *
     * @param string $string
     * @return bool true if $string contains UCS2 characters
     * @ignore
     */
    public static function containsUCS2Characters($string)
    {
        $GSMCharsetAsString = implode(array_keys(GSMCharset::$GSMCharset));

        foreach (self::mb_str_split($string) as $char) {
            if (mb_strpos($GSMCharsetAsString, $char) === false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Truncate $string and append $appendedString at the end if $string can not fit the
     * the $maximumNumberOfConcatenatedSMS.
     *
     * @param string $string String to truncate
     * @param int $maximumNumberOfConcatenatedSMS
     * @param string $appendedString
     * @return string original $string or truncated $string appended with $appendedString
     * @ignore
     */
    public static function truncate($string, $maximumNumberOfConcatenatedSMS, $appendedString = 'MobileMessaging_SMS_Content_Too_Long')
    {
        $appendedString = Piwik::translate($appendedString);

        $smsContentContainsUCS2Chars = self::containsUCS2Characters($string);
        $maxCharsAllowed = self::maxCharsAllowed($maximumNumberOfConcatenatedSMS, $smsContentContainsUCS2Chars);
        $sizeOfSMSContent = self::sizeOfSMSContent($string, $smsContentContainsUCS2Chars);

        if ($sizeOfSMSContent <= $maxCharsAllowed) return $string;

        $smsContentContainsUCS2Chars = $smsContentContainsUCS2Chars || self::containsUCS2Characters($appendedString);
        $maxCharsAllowed = self::maxCharsAllowed($maximumNumberOfConcatenatedSMS, $smsContentContainsUCS2Chars);
        $sizeOfSMSContent = self::sizeOfSMSContent($string . $appendedString, $smsContentContainsUCS2Chars);

        $sizeToTruncate = $sizeOfSMSContent - $maxCharsAllowed;

        $subStrToTruncate = '';
        $subStrSize = 0;
        $reversedStringChars = array_reverse(self::mb_str_split($string));
        for ($i = 0; $subStrSize < $sizeToTruncate; $i++) {
            $subStrToTruncate = $reversedStringChars[$i] . $subStrToTruncate;
            $subStrSize = self::sizeOfSMSContent($subStrToTruncate, $smsContentContainsUCS2Chars);
        }

        return preg_replace('/' . preg_quote($subStrToTruncate, '/') . '$/', $appendedString, $string);
    }

    private static function mb_str_split($string)
    {
        return preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    private static function sizeOfSMSContent($smsContent, $containsUCS2Chars)
    {
        if ($containsUCS2Chars) return mb_strlen($smsContent, 'UTF-8');

        $sizeOfSMSContent = 0;
        foreach (self::mb_str_split($smsContent) as $char) {
            $sizeOfSMSContent += GSMCharset::$GSMCharset[$char];
        }
        return $sizeOfSMSContent;
    }

    private static function maxCharsAllowed($maximumNumberOfConcatenatedSMS, $containsUCS2Chars)
    {
        $maxCharsInOneUniqueSMS = $containsUCS2Chars ? self::MAX_UCS2_CHARS_IN_ONE_UNIQUE_SMS : self::MAX_GSM_CHARS_IN_ONE_UNIQUE_SMS;
        $maxCharsInOneConcatenatedSMS = $containsUCS2Chars ? self::MAX_UCS2_CHARS_IN_ONE_CONCATENATED_SMS : self::MAX_GSM_CHARS_IN_ONE_CONCATENATED_SMS;

        $uniqueSMS = $maximumNumberOfConcatenatedSMS == 1;

        return $uniqueSMS ?
            $maxCharsInOneUniqueSMS :
            $maxCharsInOneConcatenatedSMS * $maximumNumberOfConcatenatedSMS;
    }

}
