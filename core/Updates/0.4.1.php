<?php

class Piwik_Updates_0_4_1 implements Piwik_iUpdate
{
	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, array(
			'ALTER TABLE `'. Piwik::prefixTable('log_conversion') .'`
				CHANGE `idlink_va` `idlink_va` INT(11) DEFAULT NULL' => false,
			'ALTER TABLE `'. Piwik::prefixTable('log_conversion') .'`
				CHANGE `idaction` `idaction` INT(11) DEFAULT NULL' => false,
		));
	}
}
