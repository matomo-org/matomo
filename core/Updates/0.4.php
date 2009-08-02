<?php

// no direct access
defined('PIWIK_INCLUDE_PATH') or die('Restricted access');

class Piwik_Updates_0_4
{
	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, array(
			'UPDATE `'. Piwik::prefixTable('log_visit') .'`
				SET location_ip=location_ip+CAST(POW(2,32) AS UNSIGNED) WHERE location_ip < 0' => false,
			'ALTER TABLE `'. Piwik::prefixTable('log_visit') .'`
				CHANGE `location_ip` `location_ip` BIGINT UNSIGNED NOT NULL' => false,
			'UPDATE `'. Piwik::prefixTable('logger_api_call') .'`
				SET caller_ip=caller_ip+CAST(POW(2,32) AS UNSIGNED) WHERE caller_ip < 0' => false,
			'ALTER TABLE `'. Piwik::prefixTable('logger_api_call') .'`
				CHANGE `caller_ip` `caller_ip` BIGINT UNSIGNED' => false,
		));
	}
}

Piwik_Updates_0_4::update();
