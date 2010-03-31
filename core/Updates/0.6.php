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
class Piwik_Updates_0_6 extends Piwik_Updates
{
	static function getSql($adapter = 'PDO_MYSQL')
	{
		$defaultTimezone = 'UTC';
		$defaultCurrency = 'USD';
		return array(
			'ALTER TABLE ' . Piwik::prefixTable('site') . ' ADD `timezone` VARCHAR( 50 ) NOT NULL AFTER `ts_created` ;' => false,
			'UPDATE ' . Piwik::prefixTable('site') . ' SET `timezone` = "'.$defaultTimezone.'";' => false,
			'ALTER TABLE ' . Piwik::prefixTable('site') . ' ADD currency CHAR( 3 ) NOT NULL AFTER `timezone` ;' => false,
			'UPDATE ' . Piwik::prefixTable('site') . ' SET `currency` = "'.$defaultCurrency.'";' => false,
			'ALTER TABLE ' . Piwik::prefixTable('site') . ' ADD excluded_parameters VARCHAR( 255 ) NOT NULL AFTER `excluded_ips` ;' => false,
			'ALTER TABLE ' . Piwik::prefixTable('site') . ' ADD `excluded_ips` TEXT NOT NULL AFTER `currency` ;' => false,
			'ALTER TABLE ' . Piwik::prefixTable('log_visit') . ' DROP INDEX `index_idsite_date_config` ;' => false,
			'ALTER TABLE ' . Piwik::prefixTable('log_visit') . ' DROP visit_server_date;' => false,
			'ALTER TABLE ' . Piwik::prefixTable('log_visit') . ' ADD INDEX `index_idsite_datetime_config`  ( `idsite` , `visit_last_action_time`  , `config_md5config` ( 8 ) ) ;' => false,
			'ALTER TABLE ' . Piwik::prefixTable('log_conversion') . ' DROP INDEX index_idsite_date' => false,
			'ALTER TABLE ' . Piwik::prefixTable('log_conversion') . ' DROP visit_server_date;' => false,
			'ALTER TABLE ' . Piwik::prefixTable('log_conversion') . ' ADD INDEX index_idsite_datetime ( `idsite` , `server_time` )' => false,
			'ALTER TABLE ' . Piwik::prefixTable('user') . ' CHANGE date_registered date_registered TIMESTAMP NULL' => false,
			'ALTER TABLE ' . Piwik::prefixTable('site') . ' CHANGE ts_created ts_created TIMESTAMP NULL' => false,
		);
	}

	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, self::getSql());
	}
}
