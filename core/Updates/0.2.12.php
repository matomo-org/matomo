<?php

class Piwik_Updates_0_2_12 implements Piwik_iUpdate
{
	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, array(
			'ALTER TABLE `'. Piwik::prefixTable('site') .'`
				CHANGE `ts_created` `ts_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL' => false,
			'ALTER TABLE `'. Piwik::prefixTable('log_visit') .'`
				DROP `config_color_depth`' => false,
		));
	}
}
