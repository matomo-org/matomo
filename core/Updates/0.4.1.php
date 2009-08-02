<?php

// no direct access
defined('PIWIK_INCLUDE_PATH') or die('Restricted access');

class Piwik_Updates_0_4_1
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

Piwik_Updates_0_4_1::update();
