<?php

Piwik_Query( "ALTER TABLE `".Piwik::prefixTable('log_visit')."` 
			ADD `visit_goal_converted` VARCHAR( 1 ) NOT NULL AFTER `visit_total_time` ;");
$tables = Piwik::getTablesCreateSql();
Piwik_Query( $tables['log_conversion'] );
Piwik_Query( $tables['goal'] );

$allTablesInstalled = Piwik::getTablesInstalled();
foreach($allTablesInstalled as $tableName)
{
	if(preg_match('/archive_/', $tableName) == 1)
	{
		Piwik_Query('CREATE INDEX index_all 
				ON '. $tableName . ' (`idsite`,`date1`,`date2`,`name`,`ts_archived`)');
		
	}
}
