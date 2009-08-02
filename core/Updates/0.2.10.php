<?php

// no direct access
defined('PIWIK_INCLUDE_PATH') or die('Restricted access');

class Piwik_Updates_0_2_10
{
	static function update()
	{
		$tables = Piwik::getTablesCreateSql();
		Piwik_Updater::updateDatabase(__FILE__, array(
			$tables['option'] => false,
		));
	}
}

Piwik_Updates_0_2_10::update();
