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
		$rootLogin = Zend_Registry::get('config')->superuser->login;
		try {
			// throws an exception if invalid
			Piwik::checkValidLoginString($rootLogin);
		} catch(Exception $e) {
			throw new Exception('Superuser login name "' . $rootLogin . '" is no longer a valid format. '
						. $e->getMessage()
						. ' Edit your config/config.ini.php to change it.');
		}
	}
}
