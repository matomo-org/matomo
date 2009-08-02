<?php

class Piwik_Updates_0_2_35 implements Piwik_iUpdate
{
	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, array(
			'ALTER TABLE `'. Piwik::prefixTable('user_dashboard') .'`
				CHANGE `layout` `layout` TEXT NOT NULL' => false,
		));
	}
}
