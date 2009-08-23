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
class Piwik_Updates_0_2_27 implements Piwik_iUpdate
{
	static function update()
	{
		$sqlarray[ 'ALTER TABLE `'. Piwik::prefixTable('log_visit') .'`
					ADD `visit_goal_converted` VARCHAR( 1 ) NOT NULL AFTER `visit_total_time`' ] = false;

		$tables = Piwik::getTablesCreateSql();
		$sqlarray[ $tables['log_conversion'] ] = false;
		$sqlarray[ $tables['goal'] ] = false;

		$tables = Piwik::getTablesInstalled();
		foreach($tables as $tableName)
		{
			if(preg_match('/archive_/', $tableName) == 1)
			{
				$sqlarray[ 'CREATE INDEX index_all ON '. $tableName .' (`idsite`,`date1`,`date2`,`name`,`ts_archived`)' ] = false;
			}
		}

		Piwik_Updater::updateDatabase(__FILE__, $sqlarray);
	}
}
