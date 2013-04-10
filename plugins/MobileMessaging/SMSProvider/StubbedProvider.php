<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_MobileMessaging_SMSProvider
 */

/**
 * Used for testing
 *
 * @package Piwik_MobileMessaging_SMSProvider
 */
class Piwik_MobileMessaging_SMSProvider_StubbedProvider extends Piwik_MobileMessaging_SMSProvider
{

    public function verifyCredential($apiKey)
    {
        return true;
    }

    public function sendSMS($apiKey, $smsText, $phoneNumber, $from)
    {
        // nothing to do
    }

    public function getCreditLeft($apiKey)
    {
        return 1;
    }
}