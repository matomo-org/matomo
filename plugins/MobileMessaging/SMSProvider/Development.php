<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MobileMessaging\SMSProvider;

use Piwik\Notification;
use Piwik\Plugins\MobileMessaging\SMSProvider;

/**
 * Used for development only
 *
 */
class Development extends SMSProvider
{

    public function verifyCredential($apiKey)
    {
        return true;
    }

    public function sendSMS($apiKey, $smsText, $phoneNumber, $from)
    {
        $message = sprintf('An SMS was sent:<br />From: %s<br />To: %s<br />Message: %s', $from, $phoneNumber, $smsText);

        $notification = new Notification($message);
        $notification->raw = true;
        $notification->context = Notification::CONTEXT_INFO;
        Notification\Manager::notify('StubbedSMSProvider'.preg_replace('/[^a-z0-9]/', '', $phoneNumber), $notification);
    }

    public function getCreditLeft($apiKey)
    {
        return 'Balance: 42';
    }
}
