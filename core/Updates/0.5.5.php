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
class Piwik_Updates_0_5_5 extends Piwik_Updates
{
	static function getSql($adapter = 'PDO_MYSQL')
	{
		$sqlarray = array(
			'DROP INDEX index_idsite_date ON ' . Piwik::prefixTable('log_visit') => '1091',
			'CREATE INDEX index_idsite_date_config ON ' . Piwik::prefixTable('log_visit') . ' (idsite, visit_server_date, config_md5config(8))' => '1061',
		);

		$tables = Piwik::getTablesInstalled();
		foreach($tables as $tableName)
		{
			if(preg_match('/archive_/', $tableName) == 1)
			{
				$sqlarray[ 'DROP INDEX index_all ON '. $tableName ] = '1091';
			}
			if(preg_match('/archive_numeric_/', $tableName) == 1)
			{
				$sqlarray[ 'CREATE INDEX index_idsite_dates_period ON '. $tableName .' (idsite, date1, date2, period)' ] = '1061';
			}
		}

		return $sqlarray;
	}

	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, self::getSql());
		
	}
}
