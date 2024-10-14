<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MobileMessaging\SMSProvider;

use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\MobileMessaging\SMSProvider;
use Piwik\Development as PiwikDevelopment;

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
        return 'Development SMS Provider<br />All sent SMS will be logged on info level';
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
        StaticContainer::get(LoggerInterface::class)->info(
            'SMS sent from {from}, to {to}: {message}',
            ['from' => $from, 'to' => $phoneNumber, 'message' => $smsText]
        );
    }

    public function getCreditLeft($credentials)
    {
        return 'Balance: 42';
    }
}
