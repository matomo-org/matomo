<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_1_1 extends Piwik_Updates
{
	static function update($schema = 'Myisam')
	{
		$config = Zend_Registry::get('config');

		$rootLogin = $config->superuser->login;
		try {
			// throws an exception if invalid
			Piwik::checkValidLoginString($rootLogin);
		} catch(Exception $e) {
			throw new Exception('Superuser login name "' . $rootLogin . '" is no longer a valid format. '
						. $e->getMessage()
						. ' Edit your config/config.ini.php to change it.');
		}

		// Warning!
		// This code below will copy in the config.ini.php ALL the values found in the 
		// General section (including the ones in global.ini.php). This is not desired!
		// We leave this code as is since this migration does impact a fraction of users
		// Do not reuse this code for future migration of config file values.
		$generalInfo = $config->General->toArray();
		if(!isset($generalInfo['proxy_client_headers']) && count($headers = Piwik_ProxyHeaders::getProxyClientHeaders()) > 0)
		{
			$generalInfo['proxy_client_headers'] = $headers;
		}
		if(!isset($generalInfo['proxy_host_headers']) && count($headers = Piwik_ProxyHeaders::getProxyHostHeaders()) > 0)
		{
			$generalInfo['proxy_host_headers'] = $headers;
		}
		if(isset($headers))
		{
			if(is_writable( Piwik_Config::getDefaultUserConfigPath() ))
			{
				$config->General = $generalInfo;
				$config->__destruct();
				Piwik::createConfigObject();
			}
			else
			{
				throw new Exception('You appear to be using a proxy server.  Edit your config/config.ini.php to configure proxy_client_headers[] and/or proxy_host_headers[].');
			}
		}

		Piwik_Updater::updateDatabase(__FILE__, self::getSql());
	}
}
