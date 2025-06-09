<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
class ASPSMS extends SMSProvider
{
    public const SOCKET_TIMEOUT = 15;

    public const BASE_API_URL          = 'https://json.aspsms.com/';
    public const CHECK_CREDIT_RESOURCE = 'CheckCredits';
    public const SEND_SMS_RESOURCE     = 'SendTextSMS';

    public const MAXIMUM_FROM_LENGTH      = 11;
    public const MAXIMUM_CONCATENATED_SMS = 9;

    public function getId()
    {
        return 'ASPSMS';
    }

    public function getDescription()
    {
        return 'You can use <a target="_blank" rel="noreferrer noopener" href="http://www.aspsms.com/en/?REF=227830"><img src="plugins/MobileMessaging/images/ASPSMS.png"/></a> to send SMS Reports from Matomo.<br/>
			<ul>
			<li> First, <a target="_blank" rel="noreferrer noopener" href="http://www.aspsms.com/en/registration/?REF=227830">get an Account at ASPSMS</a> (Signup is free!)
			</li><li> Enter your ASPSMS credentials on this page. </li>
			</ul>
			<br/>About ASPSMS.com: <ul>
			<li>ASPSMS provides fast and reliable high quality worldwide SMS delivery, over 900 networks in every corner of the globe.
			</li><li>Cost per SMS message depends on the target country and starts from ~0.06USD (0.04EUR).
			</li><li>Most countries and networks are supported but we suggest you check the latest position on their supported networks list <a href="http://www.aspsms.com/en/networks/?REF=227830" target="_blank" rel="noreferrer noopener">here</a>.
			</li><li>For sending an SMS, you need so-called ASPSMS credits, which are purchased in advance. The ASPSMS credits do not expire. 
			</li><li><a target="_blank" rel="noreferrer noopener" href="https://www.aspsms.com/instruction/payment.asp?REF=227830">Payment</a> by bank transfer, various credit cards such as Eurocard/Mastercard, Visa, American Express or Diners Club, PayPal or Swiss Postcard.
			</li>
			</ul>
			';
    }

    public function getCredentialFields()
    {
        return array(
            array(
                'type'  => 'text',
                'name'  => 'username',
                'title' => 'MobileMessaging_UserKey'
            ),
            array(
                'type'  => 'text',
                'name'  => 'password',
                'title' => 'General_Password'
            ),
        );
    }

    public function verifyCredential($credentials)
    {
        $this->getCreditLeft($credentials);

        return true;
    }

    public function sendSMS($credentials, $smsText, $phoneNumber, $from)
    {
        $from = substr($from, 0, self::MAXIMUM_FROM_LENGTH);

        $smsText = self::truncate($smsText, self::MAXIMUM_CONCATENATED_SMS);

        $additionalParameters = array(
            'Recipients'  => array($phoneNumber),
            'MessageText' => $smsText,
            'Originator'  => $from,
            'AffiliateID' => '227830',
        );

        $this->issueApiCall(
            $credentials,
            self::SEND_SMS_RESOURCE,
            $additionalParameters
        );
    }

    private function issueApiCall($credentials, $resource, $additionalParameters = array())
    {
        $accountParameters = array(
            'UserName' => $credentials['username'],
            'Password' => $credentials['password'],
        );

        $parameters = array_merge($accountParameters, $additionalParameters);

        $url = self::BASE_API_URL
            . $resource;

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
                $acceptInvalidSslCertificate = false,
                $byteRange = false,
                $getExtendedInfo = false,
                $httpMethod = 'POST',
                $httpUserName = null,
                $httpPassword = null,
                $requestBody = json_encode($parameters)
            );
        } catch (Exception $e) {
            throw new APIException($e->getMessage());
        }

        $result = @json_decode($result, true);

        if (!$result || $result['StatusCode'] != 1) {
            throw new APIException(
                'ASPSMS API returned the following error message : ' . $result['StatusInfo']
            );
        }

        return $result;
    }

    public function getCreditLeft($credentials)
    {
        $credits = $this->issueApiCall(
            $credentials,
            self::CHECK_CREDIT_RESOURCE
        );

        return Piwik::translate('MobileMessaging_Available_Credits', array($credits['Credits']));
    }
}
