<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_MobileMessaging
 */

/**
 * The Piwik_MobileMessaging_SMSProvider abstract class is used as a base class for SMS provider implementations.
 *
 * @package Piwik_MobileMessaging
 * @subpackage Piwik_MobileMessaging_SMSProvider
 */
abstract class Piwik_MobileMessaging_SMSProvider
{
	static public $availableSMSProviders = array(
		'Mediaburst',
	);

	/**
	 * Return the SMSProvider associated to the provider name $providerName
	 *
	 * @throws exception If the provider is unknown
	 * @param string $providerName
	 * @return Piwik_MobileMessaging_SMSProvider
	 */
	static public function factory($providerName)
	{
		$name = ucfirst(strtolower($providerName));
		$className = 'Piwik_MobileMessaging_SMSProvider_' . $name;

		try {
			Piwik_Loader::loadClass($className);
			return new $className;
		} catch(Exception $e) {
			throw new Exception(
				Piwik_TranslateException(
					'MobileMessaging_Exception_UnknownProvider',
					array($name, implode(', ', self::$availableSMSProviders))
				)
			);
		}
	}

	/**
	 * verify the SMS API credential
	 *
	 * @param string $username SMS API username
	 * @param string $password SMS API password
	 * @return bool true if SMS API credential are valid, false otherwise
	 */
	abstract public function verifyCredential($username, $password);

	/**
	 * get remaining credits
	 *
	 * @param string $username SMS API username
	 * @param string $password SMS API password
	 * @return string remaining credits
	 */
	abstract public function getCreditLeft($username, $password);

	/**
	 * send SMS
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $smsText
	 * @param string $phoneNumber
	 * @return bool true
	 */
	abstract public function sendSMS($username, $password, $smsText, $phoneNumber, $from);
}
