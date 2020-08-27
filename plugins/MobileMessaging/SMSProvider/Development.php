<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MobileMessaging\SMSProvider;

use Piwik\Notification;
use Piwik\Plugins\MobileMessaging\SMSProvider;
use Piwik\Development as PiwikDevelopment;
use Piwik\Session;

/**
 * Used for development only
 *
 * @ignore
 */
class Development extends SMSProvider
{

    public function getId()
    {
        return 'Development';
    }

    public function getDescription()
    {
        return 'Development SMS Provider<br />All sent SMS will be displayed as Notification';
    }

    public function isAvailable()
    {
        return PiwikDevelopment::isEnabled();
    }

    public function verifyCredential($credentials)
    {
        return true;
    }

    public function getCredentialFields()
    {
        return array();
    }

    public function sendSMS($credentials, $smsText, $phoneNumber, $from)
    {
        Session::start(); // ensure session is writable to add a notification
        $message = sprintf('An SMS was sent:<br />From: %s<br />To: %s<br />Message: %s', $from, $phoneNumber, $smsText);

        $notification = new Notification($message);
        $notification->raw = true;
        $notification->context = Notification::CONTEXT_INFO;
        Notification\Manager::notify('StubbedSMSProvider'.preg_replace('/[^a-z0-9]/', '', $phoneNumber), $notification);
    }

    public function getCreditLeft($credentials)
    {
        return 'Balance: 42';
    }
}
