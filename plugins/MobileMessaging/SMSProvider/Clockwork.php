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
use Piwik\Plugins\MobileMessaging\APIException;
use Piwik\Plugins\MobileMessaging\SMSProvider;

require_once PIWIK_INCLUDE_PATH . "/plugins/MobileMessaging/APIException.php";

/**
 * @ignore
 */
class Clockwork extends SMSProvider
{
    const SOCKET_TIMEOUT = 15;

    const BASE_API_URL = 'https://api.mediaburst.co.uk/http';
    const CHECK_CREDIT_RESOURCE = '/credit.aspx';
    const SEND_SMS_RESOURCE = '/send.aspx';

    const ERROR_STRING = 'Error';

    const MAXIMUM_FROM_LENGTH = 11;
    const MAXIMUM_CONCATENATED_SMS = 3;

    public function getId()
    {
        return 'Clockwork';
    }

    public function getDescription()
    {
        return 'You can use <a target="_blank" href="?module=Proxy&action=redirect&url=http://www.clockworksms.com/platforms/piwik/"><img src="plugins/MobileMessaging/images/Clockwork.png"/></a> to send SMS Reports from Piwik.<br/>
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
			';
    }

    public function verifyCredential($apiKey)
    {
        $this->getCreditLeft($apiKey);

        return true;
    }

    public function sendSMS($apiKey, $smsText, $phoneNumber, $from)
    {
        $from = substr($from, 0, self::MAXIMUM_FROM_LENGTH);

        $smsText = self::truncate($smsText, self::MAXIMUM_CONCATENATED_SMS);

        $additionalParameters = array(
            'To'      => str_replace('+', '', $phoneNumber),
            'Content' => $smsText,
            'From'    => $from,
            'Long'    => 1,
            'MsgType' => self::containsUCS2Characters($smsText) ? 'UCS2' : 'TEXT',
        );

        $this->issueApiCall(
            $apiKey,
            self::SEND_SMS_RESOURCE,
            $additionalParameters
        );
    }

    private function issueApiCall($apiKey, $resource, $additionalParameters = array())
    {
        $accountParameters = array(
            'Key' => $apiKey,
        );

        $parameters = array_merge($accountParameters, $additionalParameters);

        $url = self::BASE_API_URL
            . $resource
            . '?' . http_build_query($parameters, '', '&');

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
                $acceptInvalidSslCertificate = true
            );
        } catch (Exception $e) {
            $result = self::ERROR_STRING . " " . $e->getMessage();
        }

        if (strpos($result, self::ERROR_STRING) !== false) {
            throw new APIException(
                'Clockwork API returned the following error message : ' . $result
            );
        }

        return $result;
    }

    public function getCreditLeft($apiKey)
    {
        return $this->issueApiCall(
            $apiKey,
            self::CHECK_CREDIT_RESOURCE
        );
    }
}
