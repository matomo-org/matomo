<?php

// no direct access
defined('PIWIK_INCLUDE_PATH') or die('Restricted access');

class Piwik_Updates_0_2_13
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

Piwik_Updates_0_2_13::update();
