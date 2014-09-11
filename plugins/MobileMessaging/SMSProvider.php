<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MobileMessaging;

use Exception;
use Piwik\Piwik;
use Piwik\BaseFactory;

/**
 * The SMSProvider abstract class is used as a base class for SMS provider implementations.
 *
 */
abstract class SMSProvider extends BaseFactory
{
    const MAX_GSM_CHARS_IN_ONE_UNIQUE_SMS = 160;
    const MAX_GSM_CHARS_IN_ONE_CONCATENATED_SMS = 153;
    const MAX_UCS2_CHARS_IN_ONE_UNIQUE_SMS = 70;
    const MAX_UCS2_CHARS_IN_ONE_CONCATENATED_SMS = 67;

    public static $availableSMSProviders = array(
        'Clockwork' => 'You can use <a target="_blank" href="?module=Proxy&action=redirect&url=http://www.clockworksms.com/platforms/piwik/"><img src="plugins/MobileMessaging/images/Clockwork.png"/></a> to send SMS Reports from Piwik.<br/>
			<ul>
			<li> First, <a target="_blank" href="?module=Proxy&action=redirect&url=http://www.clockworksms.com/platforms/piwik/">get an API Key from Clockwork</a> (Signup is free!)
			</li><li> Enter your Clockwork API Key on this page. </li>
			</ul>
			<br/><em>About Clockwork: </em><ul>
			<li>Clockwork gives you fast, reliable high quality worldwide SMS delivery, over 450 networks in every corner of the globe.
			</li><li>Cost per SMS message is around ~0.08USD (0.06EUR).
			</li><li>Most countries and networks are supported but we suggest you check the latest position on their coverage map <a target="_blank" href="?module=Proxy&action=redirect&url=http://www.clockworksms.com/sms-coverage/">here</a>.
			</li>
			</ul>
			',
    );

    protected static function getClassNameFromClassId($id)
    {
        return __NAMESPACE__ . '\\SMSProvider\\' . $id;
    }

    protected static function getInvalidClassIdExceptionMessage($id)
    {
        return Piwik::translate('MobileMessaging_Exception_UnknownProvider',
            array($id, implode(', ', array_keys(self::$availableSMSProviders)))
        );
    }

    /**
     * Return the SMSProvider associated to the provider name $providerName
     *
     * @throws Exception If the provider is unknown
     * @param string $providerName
     * @return \Piwik\Plugins\MobileMessaging\SMSProvider
     */
    public static function factory($providerName)
    {
        $className = __NAMESPACE__ . '\\SMSProvider\\' . $providerName;

        if (!class_exists($className)) {
            throw new Exception(
                Piwik::translate(
                    'MobileMessaging_Exception_UnknownProvider',
                    array($providerName, implode(', ', array_keys(self::$availableSMSProviders)))
                )
            );
        }

        return new $className;
    }

    /**
     * Assert whether a given String contains UCS2 characters
     *
     * @param string $string
     * @return bool true if $string contains UCS2 characters
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

    /**
     * verify the SMS API credential
     *
     * @param string $apiKey API Key
     * @return bool true if SMS API credential are valid, false otherwise
     */
    abstract public function verifyCredential($apiKey);

    /**
     * get remaining credits
     *
     * @param string $apiKey API Key
     * @return string remaining credits
     */
    abstract public function getCreditLeft($apiKey);

    /**
     * send SMS
     *
     * @param string $apiKey
     * @param string $smsText
     * @param string $phoneNumber
     * @param string $from
     * @return bool true
     */
    abstract public function sendSMS($apiKey, $smsText, $phoneNumber, $from);
}
