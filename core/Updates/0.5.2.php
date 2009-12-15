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
class Piwik_Updates_0_5_2 implements Piwik_iUpdate
{
	static function update()
	{
		$config = Zend_Registry::get('config');
		$salt = Piwik_Common::generateUniqId();
		try {
			if(isset($config->superuser->salt))
			{
				return;
			}

			if(is_writable( Piwik_Config::getDefaultUserConfigPath() ))
			{
				$superuser_info = $config->superuser->toArray();
				$superuser_info['salt'] = $salt;
				$config->superuser = $superuser_info;
				$config->__destruct();

				Piwik::createConfigObject();

				return;
			}
		} catch(Exception $e) { }

		throw new Piwik_Updater_UpdateErrorException("Edit config.ini.php and add below <code>[superuser]</code> the following line <br/><code>salt = $salt</code>");
	}
}
