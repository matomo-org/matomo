<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_0_5_4 extends Piwik_Updates
{
	static function getSql($adapter = 'PDO_MYSQL')
	{
		return array(
			'ALTER TABLE `'. Piwik::prefixTable('log_action') .'`
				 CHANGE `name` `name` TEXT' => false,
		);
	}

	static function update()
	{
		$config = Zend_Registry::get('config');
		$salt = Piwik_Common::generateUniqId();
		if(!isset($config->superuser->salt))
		{
			try {
				if(is_writable( Piwik_Config::getDefaultUserConfigPath() ))
				{
					$superuser_info = $config->superuser->toArray();
					$superuser_info['salt'] = $salt;
					$config->superuser = $superuser_info;

					$config->__destruct();
					Piwik::createConfigObject();
				}
				else
				{
					throw new Exception('mandatory update failed');
				}
			} catch(Exception $e) {
				throw new Piwik_Updater_UpdateErrorException("Please edit your config/config.ini.php file and add below <code>[superuser]</code> the following line: <br /><code>salt = $salt</code>");
			}
		}

		$config = Zend_Registry::get('config');
		$plugins = $config->Plugins->toArray();
		if(!in_array('MultiSites', $plugins))
		{
			try {
				if(is_writable( Piwik_Config::getDefaultUserConfigPath() ))
				{
					$plugins[] = 'MultiSites';
					$config->Plugins = $plugins;

					$config->__destruct();
					Piwik::createConfigObject();
				}
				else
				{
					throw new Exception('optional update failed');
				}
			} catch(Exception $e) {
				throw new Exception("You can now enable the new MultiSites plugin in the Plugins screen in the Piwik admin!");
			}
		}
		
		Piwik_Updater::updateDatabase(__FILE__, self::getSql());
	}
}
