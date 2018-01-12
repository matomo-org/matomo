<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MobileMessaging\SMSProvider;

use Piwik\Plugins\MobileMessaging\SMSProvider;

/**
 * Used for testing
 *
 * @ignore
 */
class StubbedProvider extends SMSProvider
{

    public function getId()
    {
        return 'StubbedProvider';
    }

    public function getDescription()
    {
        return 'Only during testing available';
    }

    public function isAvailable()
    {
        return defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE;
    }

    public function verifyCredential($credentials)
    {
        return true;
    }

    public function sendSMS($credentials, $smsText, $phoneNumber, $from)
    {
        // nothing to do
    }

    public function getCreditLeft($credentials)
    {
        return 1;
    }
}
