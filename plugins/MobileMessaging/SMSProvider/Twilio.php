<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\MobileMessaging\SMSProvider;

use Exception;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugins\MobileMessaging\APIException;
use Piwik\Plugins\MobileMessaging\SMSProvider;

require_once PIWIK_INCLUDE_PATH . "/plugins/MobileMessaging/APIException.php";

/**
 * @ignore
 */
class Twilio extends SMSProvider
{
    const SOCKET_TIMEOUT = 15;

    const BASE_API_URL          = 'https://api.twilio.com/2010-04-01/Accounts/';
    const SEND_SMS_RESOURCE     = 'Messages.json';

    public function getId()
    {
        return 'Twilio';
    }

    public function getDescription()
    {
        return 'You can use <a target="_blank" href="?module=Proxy&action=redirect&url=http://www.twilio.com/"><img src="plugins/MobileMessaging/images/Twilio.png"/></a> to send SMS Reports from Piwik.<br/>
            <ul>
            <li>First, <a target="_blank" href="?module=Proxy&action=redirect&url=http://www.twilio.com/try-twilio">sign up for a free account</a>.
            </li><li>Then enter your Twilio credentials on this page.</li>
            </ul>
            <br/>About Twilio:<ul>
            <li>Twilio lets you send and receive text messages over the carrier network to any phone, anywhere in the world.
            </li><li>The cost per SMS message is 0.0075 USD (0.01 EUR).
            </li>
            </ul>
            ';
    }

    public function getCredentialFields()
    {
        return array(
            array(
                'type'  => 'text',
                'name'  => 'accountSid',
                'title' => 'Account SID'
            ),
            array(
                'type'  => 'text',
                'name'  => 'authToken',
                'title' => 'Auth Token'
            ),
            array(
                'type'  => 'text',
                'name'  => 'twilioNumber',
                'title' => 'Twilio Phone Number'
            ),
        );
    }

    public function verifyCredential($credentials)
    {
        $result = $this->issueApiCall($credentials, '');
        return $result['sid'] == $credentials['accountSid'];
    }

    public function sendSMS($credentials, $smsText, $phoneNumber, $from)
    {
        $body = $from . "\n\n" . $smsText;

        $additionalParameters = array(
            'From' => $credentials['twilioNumber'],
            'To' => $phoneNumber,
            'Body' => $body
        );

        $this->issueApiCall(
            $credentials,
            self::SEND_SMS_RESOURCE,
            $additionalParameters
        );
    }

    private function issueApiCall($credentials, $resource, $additionalParameters = array())
    {
        $url = self::BASE_API_URL
          . $credentials['accountSid'] . '/' . $resource;

        $timeout = self::SOCKET_TIMEOUT;

        try {
            $result = Http::sendHttpRequestBy(
                Http::getTransportMethod(),
                $url,
                $timeout,
                $userAgent = null,
                $destinationPath = null,
                $file = null,
                $followDepth = 0,
                $acceptLanguage = false,
                $acceptInvalidSslCertificate = true,
                $byteRange = false,
                $getExtendedInfo = false,
                $httpMethod = 'POST',
                $httpUserName = $credentials['accountSid'],
                $httpPassword = $credentials['authToken'],
                $requestBody = $additionalParameters
            );
        } catch (Exception $e) {
            throw new APIException($e->getMessage());
        }

        $result = @json_decode($result, true);


        if (!$result) {
            throw new APIException(
                'The Twilio API did not return a response.'
            );
        }

        if ($result['status'] == '400') {
            throw new APIException(
                'The Twilio API returned the following error message: '
                . $result['message']
            );
        }

        return $result;
    }

    public function getCreditLeft($credentials)
    {
        // Twilio's API does not allow you to check your balance
        return '';
    }
}
