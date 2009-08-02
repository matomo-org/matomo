<?php

class Piwik_Updates_0_4_3 implements Piwik_iUpdate
{
	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, array(
			// 0.1.7 [463]
			'ALTER IGNORE TABLE `'. Piwik::prefixTable('log_visit') .'`
				 CHANGE `location_provider` `location_provider` VARCHAR( 100 ) DEFAULT NULL' => '/1054/',
			// 0.1.7 [470]
			'ALTER TABLE `'. Piwik::prefixTable('logger_api_call') .'`
				CHANGE `parameter_names_default_values` `parameter_names_default_values` TEXT,
				CHANGE `parameter_values` `parameter_values` TEXT,
				CHANGE `returned_value` `returned_value` TEXT' => false,
			'ALTER TABLE `'. Piwik::prefixTable('logger_error') .'`
				CHANGE `message` `message` TEXT' => false,
			'ALTER TABLE `'. Piwik::prefixTable('logger_exception') .'`
				CHANGE `message` `message` TEXT' => false,
			'ALTER TABLE `'. Piwik::prefixTable('logger_message') .'`
				CHANGE `message` `message` TEXT' => false,
			// 0.2.2 [489]
			'ALTER IGNORE TABLE `'. Piwik::prefixTable('site') .'`
				 CHANGE `feedburnerName` `feedburnerName` VARCHAR( 100 ) DEFAULT NULL' => '/1054/',
			// 0.2.12 [673]
			'DROP INDEX index_idaction ON '. Piwik::prefixTable('log_action') => '/1091/',
			// 0.2.27 [826]
			'ALTER TABLE `'. Piwik::prefixTable('log_visit') .'`
				CHANGE `visit_goal_converted` `visit_goal_converted` TINYINT(1) NOT NULL' => false,
			// 0.2.32 [941]
			'ALTER TABLE `'. Piwik::prefixTable('access') .'`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL' => false,
			'ALTER TABLE `'. Piwik::prefixTable('user') .'`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL' => false,
			'ALTER TABLE `'. Piwik::prefixTable('user_dashboard') .'`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL' => '/1146/',
			'ALTER TABLE `'. Piwik::prefixTable('user_language') .'`
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL' => '/1146/',
			// 0.2.33 [1020]
			'ALTER TABLE `'. Piwik::prefixTable('user_dashboard') .'`
				CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci ' => '/1146/',
			'ALTER TABLE `'. Piwik::prefixTable('user_language') .'`
				CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci ' => '/1146/',
			// 0.4 [1140]
			'ALTER TABLE `'. Piwik::prefixTable('log_visit') .'`
				CHANGE `location_ip` `location_ip` BIGINT UNSIGNED NOT NULL' => false,
			'ALTER TABLE `'. Piwik::prefixTable('logger_api_call') .'`
				CHANGE `caller_ip` `caller_ip` BIGINT UNSIGNED' => false,
		));
	}
}
