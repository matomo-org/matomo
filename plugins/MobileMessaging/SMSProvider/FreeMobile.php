<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MobileMessaging\SMSProvider;

use Piwik\Http;
use Piwik\Notification;
use Piwik\Plugins\MobileMessaging\APIException;
use Piwik\Plugins\MobileMessaging\SMSProvider;

/**
 * Used for testing
 *
 */
class FreeMobile extends SMSProvider
{

    const API_URL = 'https://smsapi.free-mobile.fr/sendmsg';
    const SOCKET_TIMEOUT = 15;

    public function verifyCredential($apiKey)
    {
        $account = explode(" ", $apiKey);
        if (2 != count($account)) {
            throw new APIException(
                'API key must to contain the user and password separate by space.'
            );
        }

        /* Send SMS with test message */
        $this->sendSMS($apiKey, 'This is a test message from Piwik', null, null);

        return true;
    }

    public function sendSMS($apiKey, $smsText, $phoneNumber, $from)
    {
        $account = explode(" ", $apiKey);

        $parameters = array(
            'user' => $account[0],
            'pass' => $account[1],
            'msg' => $smsText,
        );
        $url = self::API_URL . '?' . http_build_query($parameters, '', '&');

        $timeout = self::SOCKET_TIMEOUT;

        $result = Http::sendHttpRequestBy(
            Http::getTransportMethod(),
            $url,
            $timeout,
            $getExtendedInfo = true
        );

        if (false === $result) {
            $message = 'SMS message can not be send! ';
        } else {
            $message = sprintf('An SMS was sent to your Free Mobile number<br />Message: %s', $result, $smsText);
        }

        $notification = new Notification($message);
        $notification->raw = true;
        $notification->context = Notification::CONTEXT_INFO;
        Notification\Manager::notify('FreeMobile', $notification);
    }

    public function getCreditLeft($apiKey)
    {
        if (2 != count(explode(" ", $apiKey))) return 0;
        return 1;
    }
}
