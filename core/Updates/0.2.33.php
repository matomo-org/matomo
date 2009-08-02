<?php

class Piwik_Updates_0_2_33 implements Piwik_iUpdate
{
	static function update()
	{
		// alter table to set the utf8 collation
		$tablesToAlter = Piwik::getTablesInstalled(true);
		foreach($tablesToAlter as $table) {
			$sqlarray[ 'ALTER TABLE `'. $table .'`
				CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci ' ] = false;
		}

		Piwik_Updater::updateDatabase(__FILE__, $sqlarray);
	}
}
