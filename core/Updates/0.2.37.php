<?php

class Piwik_Updates_0_2_37 implements Piwik_iUpdate
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
