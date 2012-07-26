<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$$
 *
 * @category Piwik_Plugins
 * @package Piwik_MobileMessaging_SMSProvider
 */

require_once PIWIK_INCLUDE_PATH . "/plugins/MobileMessaging/APIException.php";
/**
 *
 * @package Piwik_MobileMessaging_SMSProvider
 */
class Piwik_MobileMessaging_SMSProvider_Mediaburst extends Piwik_MobileMessaging_SMSProvider
{
	const SOCKET_TIMEOUT = 15;

	const BASE_API_URL = 'https://api.mediaburst.co.uk/http';
	const CHECK_CREDIT_RESOURCE = '/credit.aspx';
	const SEND_SMS_RESOURCE = '/send.aspx';

	const ERROR_STRING = 'Error';

	const MAXIMUM_FROM_LENGTH = 11;
	const MAXIMUM_CONTENT_LENGTH = 459;

	public function verifyCredential($username, $password)
	{
		$this->getCreditLeft($username, $password);

		return true;
	}

	public function sendSMS($username, $password, $smsText, $phoneNumber, $from)
	{
		$from = substr($from, 0, self::MAXIMUM_FROM_LENGTH);

		//@review the length of the SMS text is limited by Mediaburst. Here, we truncate the SMS content because MultiSites.getAll can return a lot of sites. Is this the proper way to handle this case?
		$smsText = substr($smsText, 0, self::MAXIMUM_CONTENT_LENGTH);

		$additionalParameters = array(
			'To' => str_replace('+','', $phoneNumber),
			'Content' => $smsText,
			'From' => $from,
			'Long' => 1,
		);

		$this->issueApiCall(
			$username,
			$password,
			self::SEND_SMS_RESOURCE,
			$additionalParameters
		);
	}

	private function issueApiCall($username, $password, $resource, $additionalParameters = array())
	{
		$accountParameters = array(
			'Username' => $username,
			'Password' => $password,
		);

		$parameters = array_merge($accountParameters, $additionalParameters);

		$url = self::BASE_API_URL
				. $resource
				. '?' . http_build_query($parameters, '', '&');

		$timeout = self::SOCKET_TIMEOUT;

		$result = Piwik_Http::sendHttpRequestBy(
			Piwik_Http::getTransportMethod(),
			$url,
			$timeout,
			$userAgent = null,
			$destinationPath = null,
			$file = null,
			$followDepth = 0,
			$acceptLanguage = false,
			$acceptInvalidSslCertificate = true
		);

		if(strpos($result, self::ERROR_STRING) !== false)
		{
			throw new Piwik_MobileMessaging_APIException(
				'Mediaburst API returned the following error message : ' . $result
			);
		}

		return $result;
	}

	public function getCreditLeft($username, $password)
	{
		return $this->issueApiCall(
					$username,
					$password,
					self::CHECK_CREDIT_RESOURCE
				);
	}
}
