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
class Piwik_Updates_0_9_1 extends Piwik_Updates
{
	static function getSql($schema = 'Myisam')
	{
		// @see http://bugs.php.net/46111
		$timezones = timezone_identifiers_list();
		$brokenTZ = array();

		foreach ($timezones as $timezone) {
			$testDate = "2008-08-19 13:00:00 " . $timezone;
	
			if (!strtotime($testDate)) {
				$brokenTZ[] = $timezone;
			}
		}
		$timezoneList = '"'. implode('","', $brokenTZ) . '"';

		return array(
			'UPDATE '. Piwik_Common::prefixTable('site') .'
				SET timezone = "UTC" WHERE timezone IN ('. $timezoneList .')' => false,

			'UPDATE `'. Piwik_Common::prefixTable('option') .'`
				SET option_value = "UTC" WHERE option_name = "SitesManager_DefaultTimezone" AND option_value IN ('. $timezoneList .')' => false,
		);
	}

	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, self::getSql());
	}
}
