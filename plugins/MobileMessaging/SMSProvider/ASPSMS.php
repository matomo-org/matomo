<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\MobileMessaging\SMSProvider;

use Exception;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Config;
use Piwik\Plugins\MobileMessaging\APIException;
use Piwik\Plugins\MobileMessaging\SMSProvider;

require_once PIWIK_INCLUDE_PATH . "/plugins/MobileMessaging/APIException.php";

/**
 * @ignore
 */
class ASPSMS extends SMSProvider
{
    const SOCKET_TIMEOUT = 15;

    const BASE_API_URL          = 'https://json.aspsms.com/';
    const CHECK_CREDIT_RESOURCE = 'CheckCredits';
    const SEND_SMS_RESOURCE     = 'SendTextSMS';

    const MAXIMUM_FROM_LENGTH      = 11;
    const MAXIMUM_CONCATENATED_SMS = 9;

    public function getId()
    {
        return 'ASPSMS';
    }

    public function getAffiliateID()
    {
        $general = Config::getInstance()->General;
        return $general['aspsms_affiliate_id'];
    }

    public function getDescription()
    {

        return Piwik::translate('MobileMessaging_ASPSMS',$this->getAffiliateID());
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
        $general = Config::getInstance()->General;

        $from = substr($from, 0, self::MAXIMUM_FROM_LENGTH);

        $smsText = self::truncate($smsText, self::MAXIMUM_CONCATENATED_SMS);

        $additionalParameters = array(
            'Recipients'  => array($phoneNumber),
            'MessageText' => $smsText,
            'Originator'  => $from,
            'AffiliateID' => $this->getAffiliateID(),
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
                'ASPSMS API error message: ' . $result['StatusInfo']
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
