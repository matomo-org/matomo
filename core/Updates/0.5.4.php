<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_0_5_4 extends Piwik_Updates
{
	static function getSql($schema = 'Myisam')
	{
		return array(
			'ALTER TABLE `'. Piwik_Common::prefixTable('log_action') .'`
				 CHANGE `name` `name` TEXT' => false,
		);
	}

	static function update()
	{
		$salt = Piwik_Common::generateUniqId();
		if(!isset(Piwik_Config::getInstance()->superuser['salt']))
		{
			try {
				if(is_writable( Piwik_Config::getLocalConfigPath() ))
				{
					Piwik_Config::getInstance()->setConfigOption('superuser', 'salt', $salt);
					Piwik_Config::getInstance()->forceSave();
				}
				else
				{
					throw new Exception('mandatory update failed');
				}
			} catch(Exception $e) {
				throw new Piwik_Updater_UpdateErrorException("Please edit your config/config.ini.php file and add below <code>[superuser]</code> the following line: <br /><code>salt = $salt</code>");
			}
		}

		$plugins = Piwik_Config::getInstance()->Plugins;
		if(!in_array('MultiSites', $plugins))
		{
			try {
				if(is_writable( Piwik_Config::getLocalConfigPath() ))
				{
					$plugins[] = 'MultiSites';
					Piwik_Config::getInstance()->setConfigSection('Plugins', $plugins);
					Piwik_Config::getInstance()->forceSave();
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
