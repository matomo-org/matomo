<?php

// no direct access
defined('PIWIK_INCLUDE_PATH') or die('Restricted access');

class Piwik_Updates_0_2_37
{
	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, array(
			'DELETE FROM `'.  Piwik::prefixTable('user_dashboard') ."`
				WHERE layout LIKE '%.getLastVisitsGraph%'
				OR layout LIKE '%.getLastVisitsReturningGraph%'" => false,
		));
	}
}

Piwik_Updates_0_2_37::update();
