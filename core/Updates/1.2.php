<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_1_2 extends Piwik_Updates
{
	static function getSql($schema = 'Myisam')
	{
		return array(
		    'ALTER TABLE `'. Piwik_Common::prefixTable('log_visit') .'` 
		    	ADD `visit_entry_idaction_name` INT UNSIGNED NOT NULL AFTER `visit_entry_idaction_url`,
			    ADD `visit_exit_idaction_name` INT UNSIGNED NOT NULL AFTER `visit_exit_idaction_url`,
			    CHANGE `visit_exit_idaction_url` `visit_exit_idaction_url` INT UNSIGNED NOT NULL, 
			    CHANGE `visit_entry_idaction_url` `visit_entry_idaction_url` INT UNSIGNED NOT NULL
			   ' => false,
		    'ALTER TABLE `'. Piwik_Common::prefixTable('log_link_visit_action') .'` 
				ADD `idaction_name_ref` INT UNSIGNED NOT NULL AFTER `idaction_name`
			   ' => false,
			'ALTER TABLE `'. Piwik_Common::prefixTable('option') .'` ADD INDEX ( `autoload` ) ' => false,
		);
	}

	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, self::getSql());
	}
}

