<?php

class Piwik_Updates_0_2_10 implements Piwik_iUpdate
{
	static function update()
	{
		$tables = Piwik::getTablesCreateSql();
		Piwik_Updater::updateDatabase(__FILE__, array(
			$tables['option'] => false,
		));
	}
}
