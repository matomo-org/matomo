<?php

// no direct access
defined('PIWIK_INCLUDE_PATH') or die('Restricted access');

class Piwik_Updates_0_2_27
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

Piwik_Updates_0_2_27::update();
