<?php

class Piwik_Updates_0_2_13 implements Piwik_iUpdate
{
	static function update()
	{
		$tables = Piwik::getTablesCreateSql();
		Piwik_Updater::updateDatabase(__FILE__, array(
			'DROP TABLE IF EXISTS `'. Piwik::prefixTable('option') .'`' => false,
			$tables['option'] => false,
		));
	}
}
